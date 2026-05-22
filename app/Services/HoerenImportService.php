<?php

namespace App\Services;

use App\Models\HoerenCode;
use App\Models\HoerenExam;
use App\Models\HoerenExamStatement;
use App\Models\HoerenModule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Imports the Hören source JSON into our 4-table schema.
 *
 * Source shape (per entry in `entries[]`):
 *   id, title ("Teil 1"|"Teil 2"|"Teil 3"), level ("B1"|"B2"),
 *   category ("codes" | "situations" | null), subtitle, desc,
 *   footerNote, footerGuide, questions[]
 *
 * Each (level, teil) may have MULTIPLE entries — typically one with
 * category=codes/null (memorization) and one with category=situations (exams).
 * Both feed into the same `hoeren_modules` row.
 *
 * - codes/null entries → rows in `hoeren_codes`.
 * - situations entries → questions grouped by `groupTitle` → one `hoeren_exams`
 *   row per group + N `hoeren_exam_statements` rows.
 *
 * Re-running the import is idempotent: codes/exams/statements for each module
 * are wiped and rebuilt from the source. Audio paths on existing exams are
 * preserved by matching on (module_id, slug) when the same exam slug appears
 * again across runs.
 */
class HoerenImportService
{
    /**
     * @return array{
     *   modules: int, codes: int, exams: int, statements: int,
     *   skipped: int, errors: list<string>,
     * }
     */
    public function import(string $json): array
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->emptyResult(['Invalid JSON: ' . json_last_error_msg()]);
        }

        $entries = $data['entries'] ?? null;
        if (! is_array($entries)) {
            return $this->emptyResult(['Expected top-level "entries" array, got ' . gettype($entries)]);
        }

        // Group source entries by (level, teil) so we can merge codes + situations
        // into a single module.
        $grouped = [];
        foreach ($entries as $entry) {
            $key = $this->moduleKey($entry);
            if ($key === null) continue;       // skip malformed entries
            $grouped[$key][] = $entry;
        }

        $counts = [
            'modules' => 0, 'codes' => 0, 'exams' => 0,
            'statements' => 0, 'skipped' => 0, 'errors' => [],
        ];

        DB::transaction(function () use ($grouped, &$counts) {
            foreach ($grouped as $key => $entriesForKey) {
                try {
                    [$level, $teil] = explode('|', $key);
                    $teil = (int) $teil;

                    $this->importModule($level, $teil, $entriesForKey, $counts);
                } catch (\Throwable $e) {
                    $counts['skipped']++;
                    $counts['errors'][] = "Module {$key}: " . $e->getMessage();
                }
            }
        });

        return $counts;
    }

    /** Build a stable group key from one entry; returns null if level/teil missing. */
    private function moduleKey(array $entry): ?string
    {
        $level = strtoupper((string) ($entry['level'] ?? ''));
        if (! in_array($level, ['B1', 'B2'], true)) return null;

        // title is like "Teil 1" / "Teil 2" / "Teil 3"
        $title = (string) ($entry['title'] ?? '');
        if (! preg_match('/teil\s*([1-3])/i', $title, $m)) return null;
        $teil = (int) $m[1];

        return "{$level}|{$teil}";
    }

    /**
     * Import (or refresh) one module from its source entries.
     * Mutates $counts in place.
     */
    private function importModule(string $level, int $teil, array $entries, array &$counts): void
    {
        // Pull display metadata from the FIRST entry that has any.
        $meta = $this->mergeMeta($entries);

        $module = HoerenModule::updateOrCreate(
            ['level' => $level, 'teil' => $teil],
            [
                'subtitle'     => $meta['subtitle'],
                'description'  => $meta['description'],
                'footer_note'  => $meta['footer_note'],
                'footer_guide' => $meta['footer_guide'],
                'is_published' => true,
            ]
        );
        $counts['modules']++;

        // Wipe & rebuild codes for this module.
        $module->codes()->delete();

        // For exams, snapshot existing slug → audio_path so we can preserve
        // uploaded audio across re-imports.
        $existingAudio = $module->exams()->pluck('audio_path', 'slug')->all();
        $module->exams()->delete(); // cascades to statements

        $codePos = 0;
        $examPos = 0;
        $seenExamSlugs = [];

        foreach ($entries as $entry) {
            $category = $entry['category'] ?? null;
            $questions = is_array($entry['questions'] ?? null) ? $entry['questions'] : [];

            if ($category === 'situations') {
                $examPos = $this->importSituations($module, $questions, $examPos, $existingAudio, $seenExamSlugs, $counts);
            } else {
                // 'codes' or null → both look like code-questions in the source data.
                $codePos = $this->importCodes($module, $questions, $codePos, $counts);
            }
        }
    }

    /** @return int next codePos */
    private function importCodes(HoerenModule $module, array $questions, int $startPos, array &$counts): int
    {
        $pos = $startPos;
        $rows = [];
        $now = now();
        foreach ($questions as $q) {
            if (! is_array($q)) continue;
            $code = $this->firstAnswer($q);
            $topicTitle = trim((string) ($q['text'] ?? ''));
            if ($code === null || $topicTitle === '') {
                $counts['skipped']++;
                continue;
            }
            $rows[] = [
                'module_id'   => $module->id,
                'code'        => $code,
                'topic_title' => $topicTitle,
                'story_ar'    => trim((string) ($q['story'] ?? '')) ?: null,
                'position'    => $pos++,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        if ($rows) {
            // Chunked insert keeps the transaction snappy.
            foreach (array_chunk($rows, 500) as $chunk) {
                HoerenCode::insert($chunk);
            }
            $counts['codes'] += count($rows);
        }
        return $pos;
    }

    /** @return int next examPos */
    private function importSituations(
        HoerenModule $module,
        array $questions,
        int $startPos,
        array $existingAudio,
        array &$seenExamSlugs,
        array &$counts
    ): int {
        // Group statements by groupTitle.
        $groups = [];
        $order  = []; // preserves first-seen order of group titles
        foreach ($questions as $q) {
            if (! is_array($q)) continue;
            $title = trim((string) ($q['groupTitle'] ?? ''));
            if ($title === '') {
                $counts['skipped']++;
                continue;
            }
            if (! isset($groups[$title])) {
                $groups[$title] = [];
                $order[] = $title;
            }
            $groups[$title][] = $q;
        }

        $pos = $startPos;
        foreach ($order as $title) {
            $statements = $groups[$title];

            $slug = $this->makeUniqueExamSlug($title, $seenExamSlugs);
            $seenExamSlugs[$slug] = true;

            $exam = HoerenExam::create([
                'module_id'    => $module->id,
                'slug'         => $slug,
                'title'        => $title,
                'audio_path'   => $existingAudio[$slug] ?? null, // preserve uploaded audio
                'position'     => $pos++,
                'is_published' => true,
            ]);
            $counts['exams']++;

            $stmtPos = 0;
            $stmtRows = [];
            $now = now();
            foreach ($statements as $s) {
                $text   = trim((string) ($s['text'] ?? ''));
                $answer = $this->normalizeAnswer($s['answers'] ?? null);
                if ($text === '' || $answer === null) {
                    $counts['skipped']++;
                    continue;
                }
                $stmtRows[] = [
                    'exam_id'                => $exam->id,
                    'text'                   => $text,
                    'answer'                 => $answer,
                    'highlights'             => isset($s['highlights']) && is_array($s['highlights'])
                                                ? json_encode($s['highlights'], JSON_UNESCAPED_UNICODE) : null,
                    'explanation_highlights' => isset($s['explanationHighlights']) && is_array($s['explanationHighlights'])
                                                ? json_encode($s['explanationHighlights'], JSON_UNESCAPED_UNICODE) : null,
                    'position'               => $stmtPos++,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ];
            }
            if ($stmtRows) {
                foreach (array_chunk($stmtRows, 500) as $chunk) {
                    HoerenExamStatement::insert($chunk);
                }
                $counts['statements'] += count($stmtRows);
            }
        }

        return $pos;
    }

    /**
     * Merge display metadata across multiple entries for the same (level, teil).
     * Picks the first non-empty value seen.
     */
    private function mergeMeta(array $entries): array
    {
        $meta = [
            'subtitle'     => null,
            'description'  => null,
            'footer_note'  => null,
            'footer_guide' => null,
        ];
        foreach ($entries as $e) {
            $map = [
                'subtitle'     => $e['subtitle']    ?? null,
                'description'  => $e['desc']        ?? null,
                'footer_note'  => $e['footerNote']  ?? null,
                'footer_guide' => $e['footerGuide'] ?? null,
            ];
            foreach ($map as $k => $v) {
                if ($meta[$k] === null && is_string($v) && trim($v) !== '') {
                    $meta[$k] = trim($v);
                }
            }
        }
        return $meta;
    }

    /** Pull the first answer string from a question, trimmed. Null if missing. */
    private function firstAnswer(array $q): ?string
    {
        $answers = $q['answers'] ?? null;
        if (! is_array($answers) || empty($answers)) return null;
        $first = trim((string) $answers[0]);
        return $first === '' ? null : $first;
    }

    /** Normalize +/- answers. Treats common variants as their canonical form. */
    private function normalizeAnswer(mixed $answers): ?string
    {
        if (! is_array($answers) || empty($answers)) return null;
        $a = trim((string) $answers[0]);
        if ($a === '' ) return null;
        return match (strtolower($a)) {
            '+', 'r', 'richtig', 'true', '1'        => HoerenExamStatement::ANSWER_RICHTIG,
            '-', 'f', 'falsch', 'false', '0'        => HoerenExamStatement::ANSWER_FALSCH,
            default                                  => null,
        };
    }

    /** Generate a unique slug for an exam title within the current module. */
    private function makeUniqueExamSlug(string $title, array $seenSlugs): string
    {
        $base = Str::slug($title);
        if ($base === '') $base = 'exam';
        // Limit length so it fits indexes comfortably.
        $base = mb_substr($base, 0, 80);

        if (! isset($seenSlugs[$base])) return $base;

        $i = 2;
        while (isset($seenSlugs["{$base}-{$i}"])) $i++;
        return "{$base}-{$i}";
    }

    private function emptyResult(array $errors): array
    {
        return [
            'modules' => 0, 'codes' => 0, 'exams' => 0,
            'statements' => 0, 'skipped' => 0, 'errors' => $errors,
        ];
    }
}
