<?php

namespace App\Services;

use App\Models\GoetheB1LesenTopic;
use Illuminate\Support\Str;

class GoetheB1LesenImportService
{
    public const ALLOWED_TEILE = ['teil1', 'teil2', 'teil3', 'teil4', 'teil5'];

    /**
     * Import a single Teil's JSON into goethe_b1_lesen_topics.
     * Each entry in $data['exams'] becomes (or updates) a row with slug "arena-b1-test-{test}".
     * Only the chosen $teil column is touched — other Teile on existing rows are preserved.
     *
     * @return array{imported:int, skipped:int, errors:array<string>}
     */
    public function importTeilFromJson(string $rawJson, string $teil): array
    {
        $result = ['imported' => 0, 'skipped' => 0, 'errors' => []];

        if (! in_array($teil, self::ALLOWED_TEILE, true)) {
            $result['errors'][] = "Unknown teil: {$teil}";
            return $result;
        }

        $data = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result['errors'][] = 'Invalid JSON: ' . json_last_error_msg();
            return $result;
        }

        if (! is_array($data) || ! isset($data['exams']) || ! is_array($data['exams'])) {
            $result['errors'][] = 'JSON must have an "exams" array (top-level).';
            return $result;
        }

        // Soft check: file's declared teil should match the chosen one
        $declaredTeil = isset($data['teil']) ? (int) $data['teil'] : null;
        $chosenTeilNum = (int) Str::after($teil, 'teil');
        if ($declaredTeil !== null && $declaredTeil !== $chosenTeilNum) {
            $result['errors'][] = "Mismatch: file declares teil={$declaredTeil} but you chose {$teil}.";
            return $result;
        }

        foreach ($data['exams'] as $i => $exam) {
            if (! is_array($exam)) {
                $result['skipped']++;
                $result['errors'][] = "Entry #{$i}: not an object.";
                continue;
            }

            $testNum = (int) ($exam['test'] ?? 0);
            if ($testNum <= 0) {
                $result['skipped']++;
                $result['errors'][] = "Entry #{$i}: missing or invalid 'test' number.";
                continue;
            }

            $slug = "arena-b1-test-{$testNum}";
            $payload = match ($teil) {
                'teil1' => $this->normalizeTeil1($exam),
                'teil2' => $this->normalizeTeil2($exam),
                'teil3' => $this->normalizeTeil3($exam),
                'teil4' => $this->normalizeTeil4($exam),
                'teil5' => $this->normalizeTeil5($exam),
            };

            GoetheB1LesenTopic::updateOrCreate(
                ['slug' => $slug],
                [
                    'title'        => "Arena B1 — Test {$testNum}",
                    'title_ar'     => "أرينا B1 — اختبار {$testNum}",
                    'level'        => 'B1',
                    'is_published' => true,
                    $teil          => $payload,
                ]
            );
            $result['imported']++;
        }

        return $result;
    }

    public function normalizeTeil1(array $exam): array
    {
        // Prefer explicit paragraph segmentation if the source provides it —
        // OCR can't reliably reconstruct paragraph breaks where the last line
        // of a paragraph is column-full, so let the source override the heuristic.
        $paragraphs = null;
        if (!empty($exam['passage_paragraphs']) && is_array($exam['passage_paragraphs'])) {
            $paragraphs = array_values(array_map(
                fn ($p) => $this->mechanicalPassageCleanup(trim((string) $p)),
                $exam['passage_paragraphs']
            ));
            $passage  = implode("\n\n", $paragraphs);
            $blogName = $exam['blog_name'] ?? null;
        } else {
            [$passage, $blogName] = $this->cleanTeil1Passage($exam['passage_text'] ?? '');
            // Reconstruct paragraphs from the cleaned passage so the view always has the array form.
            $paragraphs = preg_split('/\n{2,}/', $passage) ?: [$passage];
        }

        $paragraphsAr = [];
        if (!empty($exam['passage_translation_paragraphs']) && is_array($exam['passage_translation_paragraphs'])) {
            $paragraphsAr = array_values(array_map(
                fn ($p) => trim((string) $p),
                $exam['passage_translation_paragraphs']
            ));
        }

        $beispiel = null;
        if (isset($exam['beispiel']) && is_array($exam['beispiel'])) {
            $beispiel = $this->normalizeTeil1Question($exam['beispiel'], 0);
        }

        return [
            'instructions'           => $exam['instructions'] ?? '',
            'topic_title'            => $exam['topic_title'] ?? null,
            'topic_title_ar'         => $exam['topic_title_ar'] ?? null,
            'blog_name'              => $blogName,
            'blog_url'               => $exam['blog_url'] ?? null,
            'passage'                => $passage,
            'passage_paragraphs'     => $paragraphs,
            'passage_paragraphs_ar'  => $paragraphsAr,
            'beispiel'               => $beispiel,
            'questions'              => array_map(
                fn ($q, $i) => $this->normalizeTeil1Question($q, $i + 1),
                $exam['questions'] ?? [],
                array_keys($exam['questions'] ?? [])
            ),
        ];
    }

    /**
     * Normalize a single Teil 1 question (or the Beispiel) — passes through the
     * rich fields needed for evidence highlighting + Darija explanations + AR translation.
     */
    private function normalizeTeil1Question(array $q, int $defaultId): array
    {
        $evidence = $q['evidence'] ?? [];
        if (is_string($evidence)) $evidence = [$evidence];
        if (!is_array($evidence)) $evidence = [];
        $evidence = array_values(array_filter(array_map(
            fn ($s) => trim((string) $s),
            $evidence
        ), fn ($s) => $s !== ''));

        return [
            'id'                       => $q['number'] ?? $defaultId,
            'prompt'                   => trim($q['prompt'] ?? ''),
            'prompt_ar'                => isset($q['prompt_ar']) ? trim((string) $q['prompt_ar']) : null,
            'answer'                   => $q['answer'] ?? null,
            'evidence'                 => $evidence,
            'evidence_paragraph_index' => isset($q['evidence_paragraph_index']) ? (int) $q['evidence_paragraph_index'] : null,
            'explanation'              => isset($q['explanation'])    ? trim((string) $q['explanation'])    : null,
            'explanation_de'           => isset($q['explanation_de']) ? trim((string) $q['explanation_de']) : null,
        ];
    }

    /**
     * Mechanical cleanup of Teil 1 passage_text from OCR'd Modelltest source,
     * then a heuristic reflow into paragraphs. Returns [cleaned_passage, detected_blog_name].
     */
    public function cleanTeil1Passage(string $raw): array
    {
        if ($raw === '') return ['', null];

        $raw = str_replace(["\r\n", "\r"], "\n", $raw);
        // 1. Strip print-margin line numbers (5, 10, … 50) at the start of any line —
        //    must run before de-hyphenation so a number can't sit between "Da-\n" and "nach".
        $raw = preg_replace('/^(?:5|10|15|20|25|30|35|40|45|50)\s+(?=\p{L})/mu', '', $raw);
        // 2. De-hyphenate end-of-line word splits: "Be-\nsonderes" → "Besonderes".
        //    Restricted to lowercase continuation to avoid merging real compounds.
        $raw = preg_replace('/(\p{L})-\n(\p{Ll})/u', '$1$2', $raw);

        $lines = explode("\n", $raw);

        // Strip leading junk; capture blog name if seen.
        $blogName = null;
        while (!empty($lines)) {
            $first = trim($lines[0]);
            if ($first === '') { array_shift($lines); continue; }
            if (preg_match('/^[|｜]/u', $first)
                || preg_match('/Wähle:\s*Sind die Aussagen/u', $first)
                || preg_match('/^Lies den Text/u', $first)) {
                array_shift($lines); continue;
            }
            // Bare blog/site domain on its own line (e.g. "Monisblog.ch").
            if (preg_match('/^[\p{L}\p{N}_-]+\.(ch|de|at|com|net|org|info|blog)$/iu', $first)) {
                $blogName = $first;
                array_shift($lines); continue;
            }
            break;
        }

        // Per-line fixes.
        foreach ($lines as $i => $line) {
            $lines[$i] = $this->mechanicalPassageCleanup($line);
        }

        // Trim trailing empties.
        while (!empty($lines) && trim(end($lines)) === '') array_pop($lines);

        $passage = $this->reflowPassageParagraphs(implode("\n", $lines));

        return [$passage, $blogName];
    }

    /**
     * Per-line mechanical cleanup (no reflow): apostrophes, common OCR misreads.
     * Safe to call on text that's already segmented into paragraphs.
     */
    private function mechanicalPassageCleanup(string $line): string
    {
        // Strip stray leading apostrophe (straight or curly) + space — common OCR artefact.
        $line = preg_replace('/^[\'’]\s+/u', '', $line);
        // Common OCR fix: gender-neutral "BlogleserInnen".
        $line = str_replace('Blogleserinnen', 'BlogleserInnen', $line);
        // De-hyphenate any straggler word-splits the caller may have left in (e.g. multi-line paragraphs).
        $line = preg_replace('/(\p{L})-\n(\p{Ll})/u', '$1$2', $line);
        return $line;
    }

    /**
     * Collapse OCR's intra-paragraph line wraps into spaces while keeping real
     * paragraph boundaries. Detection heuristics for a paragraph end:
     *   - line is "short" (< 70 chars; column wraps are typically 75–95) AND ends in . ! ?
     *   - line is very short (< 30 chars) AND ends in ,  → greeting/header
     *   - line looks like a sign-off (short, starts with eure/liebe Grüße/mit freundlichen/…)
     * Output uses `\n\n` between paragraphs so `whitespace-pre-line` renders blank-line gaps.
     */
    private function reflowPassageParagraphs(string $passage): string
    {
        if ($passage === '') return '';

        $lines = preg_split("/\n/u", $passage);
        $paragraphs = [];
        $current = '';

        $flush = function () use (&$paragraphs, &$current) {
            if ($current !== '') { $paragraphs[] = $current; $current = ''; }
        };

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') { $flush(); continue; }

            $current = $current === '' ? $line : ($current . ' ' . $line);

            $len            = mb_strlen($line);
            $isShort        = $len < 70;
            $endsTerm       = (bool) preg_match('/[.!?]\s*$/u', $line);
            $endsCommaShort = $len < 30 && (bool) preg_match('/,\s*$/u', $line);
            $isSignoff      = $len < 30 && (bool) preg_match('/^(eure|euer|liebe[rns]?\b|mit\s+freundlichen|gruß|bis\s+bald|viele\s+grüße|herzliche\s+grüße)/iu', $line);

            if (($isShort && $endsTerm) || $endsCommaShort || $isSignoff) {
                $flush();
            }
        }
        $flush();

        return implode("\n\n", $paragraphs);
    }

    public function normalizeTeil2(array $exam): array
    {
        $texts = array_map(function ($t) {
            $range = $t['question_range'] ?? [null, null];
            return [
                'label' => $t['label'] ?? '',
                'title' => $t['title'] ?? '',
                'text'  => $t['text']  ?? '',
                'from'  => $range[0]   ?? null,
                'to'    => $range[1]   ?? null,
            ];
        }, $exam['source_texts'] ?? []);

        $textForQuestion = function (int $qid) use ($texts): ?string {
            foreach ($texts as $t) {
                if ($t['from'] !== null && $t['to'] !== null && $qid >= $t['from'] && $qid <= $t['to']) {
                    return $t['label'];
                }
            }
            return null;
        };

        return [
            'instructions' => $exam['instructions'] ?? '',
            'texts'        => $texts,
            'questions'    => array_map(fn ($q) => [
                'id'         => $q['number'] ?? null,
                'text_label' => $textForQuestion((int) ($q['number'] ?? 0)),
                'prompt'     => trim($q['prompt'] ?? ''),
                'choices'    => $q['choices'] ?? [],
                'answer'     => $q['answer']  ?? null,
            ], $exam['questions'] ?? []),
        ];
    }

    public function normalizeTeil3(array $exam): array
    {
        $beispiel = $exam['beispiel'] ?? null;
        return [
            'instructions'        => $exam['instructions'] ?? '',
            'theme'               => $exam['theme']        ?? '',
            'beispiel'            => $beispiel ? [
                'anzeige' => $beispiel['anzeige'] ?? null,
                'text'    => $beispiel['text']    ?? '',
            ] : null,
            'situations'          => array_map(fn ($s) => [
                'id'     => $s['number'] ?? null,
                'prompt' => trim(preg_replace('/\s*Anzeige:?\s*$/u', '', $s['prompt'] ?? '')),
                'answer' => $s['answer'] ?? null,
            ], $exam['situations'] ?? []),
            'ads_letters'         => ['A','B','C','D','E','F','G','H','I','J'],
            'advertisements_text' => $exam['advertisements_text'] ?? '',
        ];
    }

    public function normalizeTeil4(array $exam): array
    {
        return [
            'instructions' => $exam['instructions'] ?? '',
            'topic'        => $exam['topic'] ?? '',
            'beispiel'     => $exam['beispiel'] ?? null,
            'comments'     => array_map(fn ($c) => [
                'id'     => $c['number'] ?? null,
                'prompt' => trim($c['prompt'] ?? ''),
                'author' => $c['author'] ?? null,
                'answer' => $c['answer'] ?? null,
            ], $exam['comments'] ?? []),
        ];
    }

    public function normalizeTeil5(array $exam): array
    {
        return [
            'instructions' => $exam['instructions'] ?? '',
            'situation'    => $exam['situation'] ?? '',
            'reading_text' => $exam['reading_text'] ?? '',
            'questions'    => array_map(fn ($q) => [
                'id'      => $q['number'] ?? null,
                'prompt'  => trim($q['prompt'] ?? ''),
                'choices' => $q['choices'] ?? [],
                'answer'  => $q['answer']  ?? null,
            ], $exam['questions'] ?? []),
        ];
    }

    /**
     * Quick preview — returns a summary of what a JSON would import without writing.
     */
    public function preview(string $rawJson): array
    {
        $data = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON: ' . json_last_error_msg()];
        }
        if (! is_array($data) || ! isset($data['exams']) || ! is_array($data['exams'])) {
            return ['error' => 'JSON must have an "exams" array.'];
        }

        $tests = [];
        foreach ($data['exams'] as $exam) {
            $testNum = (int) ($exam['test'] ?? 0);
            if ($testNum <= 0) continue;
            $tests[] = $testNum;
        }

        return [
            'declared_teil' => isset($data['teil']) ? (int) $data['teil'] : null,
            'count'         => count($tests),
            'tests'         => $tests,
        ];
    }
}
