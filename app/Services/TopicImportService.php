<?php

namespace App\Services;

use App\Models\LesenTopic;
use App\Models\SchreibenTopic;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;

class TopicImportService
{
    /**
     * Import a single Teil's content for many topics in one shot.
     * Each entry must carry an examTitle/title (slug derived from it) and a `content` blob
     * that matches the part's expected shape. Existing topics are matched by slug — only the
     * given column is updated, all other parts and metadata are preserved.
     *
     * @return array{imported: int, skipped: int, errors: array}
     */
    public function importPartFromJson(string $json, string $type, string $part): array
    {
        $allowed = $type === 'lesen'
            ? ['teil1', 'teil2', 'teil3', 'sprachbausteine1', 'sprachbausteine2']
            : ['teil1', 'teil2', 'teil3', 'teil4'];

        if (!in_array($part, $allowed, true)) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ["Invalid part '$part' for $type."]];
        }

        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Invalid JSON: ' . json_last_error_msg()]];
        }

        $entries  = $this->extractEntries($data);
        // Hören has its own dedicated service now (HoerenImportService) — this
        // generic service only handles lesen.
        $model    = LesenTopic::class;
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($entries as $i => $entry) {
            try {
                $title = $entry['examTitle'] ?? $entry['title'] ?? null;
                if (!$title) {
                    throw new \RuntimeException('Missing examTitle/title');
                }

                $slug    = Str::slug($entry['slug'] ?? $title);
                $content = $entry['content'] ?? $entry[$part] ?? null;
                if (is_string($content)) {
                    $decoded = json_decode($content, true);
                    $content = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
                }
                if (!is_array($content)) {
                    throw new \RuntimeException("Missing 'content' (or '$part') field");
                }

                // individualTitle is the per-Teil display title. We stash it inside the
                // part's JSON blob so each Teil of one exam can carry its own heading,
                // while examTitle stays the topic's shared grouping title (shown as a chip).
                if (!empty($entry['individualTitle'])) {
                    $content['individualTitle'] = $entry['individualTitle'];
                }

                $existing = $model::where('slug', $slug)->first();

                if ($existing) {
                    // Preserve all other columns — only swap this part.
                    $existing->update([$part => $content]);
                } else {
                    $titleAr = $entry['arabic_title'] ?? $entry['title_ar'] ?? null;
                    $payload = [
                        'slug'         => $slug,
                        'title'        => $title,
                        'title_ar'     => $titleAr === '' ? null : $titleAr,
                        'level'        => in_array($entry['level'] ?? '', ['B1', 'B2']) ? $entry['level'] : 'B1',
                        'category'     => $entry['category'] ?? null,
                        'is_published' => ($entry['visibility'] ?? 'public') === 'public',
                        $part          => $content,
                    ];
                    if ($type === 'hoeren' && isset($entry['audio_path'])) {
                        $payload['audio_path'] = $entry['audio_path'];
                    }
                    $model::create($payload);
                }

                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Entry $i: " . $e->getMessage();
            }
        }

        return compact('imported', 'skipped', 'errors');
    }

    /**
     * Pull the entries array out of any of the supported wrappers.
     */
    public function extractEntries(array $data): array
    {
        if (isset($data['entries']) && is_array($data['entries'])) {
            return $data['entries'];
        }
        return isset($data[0]) ? $data : [$data];
    }

    /**
     * Import Schreiben prompts from a JSON string. Each entry contains a scenario,
     * a list of required talking points, a type (Brief / Beschwerde / …), level, and
     * a recommended writing time in minutes.
     *
     * @return array{imported: int, skipped: int, errors: array}
     */
    // importHoerenFromJson removed — Hören is now its own subsystem with a
    // dedicated HoerenImportService (modules / codes / exams / exam_statements).

    public function importSchreibenFromJson(string $json): array
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Invalid JSON: ' . json_last_error_msg()]];
        }

        $entries  = $this->extractEntries($data);
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($entries as $i => $entry) {
            try {
                // Schreiben entries from the export wrap most fields under "content"
                $c     = is_array($entry['content'] ?? null) ? $entry['content'] : $entry;
                $title = $entry['title'] ?? $c['title'] ?? null;
                if (!$title) {
                    throw new \RuntimeException('Missing title');
                }

                $scenario = $c['scenario'] ?? $entry['scenario'] ?? null;
                if (!$scenario) {
                    $skipped++;
                    $errors[] = "Entry $i: missing scenario";
                    continue;
                }

                $level = strtoupper($c['level'] ?? $entry['level'] ?? 'B1');
                $level = in_array($level, ['B1', 'B2'], true) ? $level : 'B1';

                $titleAr = $entry['arabic_title'] ?? $c['arabic_title'] ?? null;
                if ($titleAr === '') $titleAr = null;

                $slug    = Str::slug(($entry['id'] ?? $c['id'] ?? '') . '-' . $title);
                $points  = $c['points'] ?? [];
                $minutes = (int) ($c['minutes'] ?? $entry['minutes'] ?? 30);
                $type    = $c['type'] ?? $entry['type'] ?? null;

                SchreibenTopic::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'title'        => $title,
                        'title_ar'     => $titleAr,
                        'slug'         => $slug,
                        'level'        => $level,
                        'type'         => $type ? Str::ucfirst(strtolower($type)) : null,
                        'minutes'      => $minutes ?: 30,
                        'scenario'     => $scenario,
                        'points'       => is_array($points) ? array_values($points) : [],
                        'is_published' => true,
                    ]
                );
                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Entry $i: " . $e->getMessage();
            }
        }

        return compact('imported', 'skipped', 'errors');
    }

    /**
     * Import topics from a JSON string.
     * Accepts either a single topic object or an array of topics.
     *
     * @return array{imported: int, skipped: int, errors: array}
     */
    public function importFromJson(string $json, string $type): array
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Invalid JSON: ' . json_last_error_msg()]];
        }

        // Firebase export format: { entries: [...], part: "Lesen Teil 1 B1" }
        if (isset($data['entries']) && is_array($data['entries'])) {
            return $this->processFirebaseEntries($data['entries'], $type);
        }

        // Allow single-topic object or array of topics
        $topics = isset($data[0]) ? $data : [$data];

        return $this->processTopics($topics, $type);
    }

    /**
     * Import topics from an uploaded Excel / CSV file.
     * Each row = one topic. Columns match fillable fields.
     *
     * @return array{imported: int, skipped: int, errors: array}
     */
    public function importFromExcel(UploadedFile $file, string $type): array
    {
        $rows = Excel::toArray([], $file)[0] ?? [];
        if (empty($rows)) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Empty file.']];
        }

        $headers = array_map('trim', array_shift($rows));
        $topics  = [];
        foreach ($rows as $row) {
            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), null);
            }
            $topics[] = array_combine($headers, $row);
        }

        return $this->processTopics($topics, $type);
    }

    // ──────────────────────────────────────────────────────────────
    // Handle the original Firebase/Zertify export format
    private function processFirebaseEntries(array $entries, string $type): array
    {
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        // partId → column name map
        $partMap = [
            'lesen1'          => 'teil1',
            'lesen2'          => 'teil2',
            'lesen3'          => 'teil3',
            'sprachbausteine' => 'sprachbausteine1',
            'sprachbausteine1'=> 'sprachbausteine1',
            'sprachbausteine2'=> 'sprachbausteine2',
            'hoeren1'         => 'teil1',
            'hoeren2'         => 'teil2',
            'hoeren3'         => 'teil3',
            'hoeren4'         => 'teil4',
        ];

        // Group by examId so multiple parts merge into one topic row
        $grouped = [];
        foreach ($entries as $entry) {
            $id = $entry['examId'] ?? $entry['examTitle'] ?? uniqid();
            $grouped[$id][] = $entry;
        }

        $model = LesenTopic::class; // Hören moved to HoerenImportService.

        foreach ($grouped as $examId => $parts) {
            try {
                $first = $parts[0];
                $slug  = Str::slug($first['examTitle'] ?? $examId);

                $data = [
                    'title'        => $first['examTitle'] ?? $slug,
                    'title_ar'     => $first['arabic_title'] ?? null ?: null,
                    'slug'         => $slug,
                    'level'        => in_array($first['level'] ?? '', ['B1','B2']) ? $first['level'] : 'B1',
                    'category'     => $first['Teilsthema'] ?? null,
                    'is_published' => ($first['visibility'] ?? 'public') === 'public',
                ];

                // Fill part columns
                foreach ($parts as $part) {
                    $rawPartId = $part['partId'] ?? '';
                    // normalise: "part-1766619116654" type ids → guess from partTitle
                    $col = $partMap[$rawPartId] ?? null;
                    if (!$col) {
                        $title = strtolower($part['partTitle'] ?? '');
                        foreach ($partMap as $key => $colName) {
                            if (str_contains($title, str_replace('lesen', '', $key)) || str_contains($rawPartId, $key)) {
                                $col = $colName;
                                break;
                            }
                        }
                        $col = $col ?? 'teil1';
                    }
                    $content = $part['content'] ?? null;
                    // Carry the per-Teil individualTitle inside its blob (see importPartFromJson).
                    if (is_array($content) && !empty($part['individualTitle'])) {
                        $content['individualTitle'] = $part['individualTitle'];
                    }
                    $data[$col] = $content;
                }

                $model::updateOrCreate(['slug' => $slug], $data);
                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "ExamId $examId: " . $e->getMessage();
            }
        }

        return compact('imported', 'skipped', 'errors');
    }

    // ──────────────────────────────────────────────────────────────
    private function processTopics(array $topics, string $type): array
    {
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($topics as $i => $raw) {
            try {
                $slug = Str::slug($raw['slug'] ?? $raw['title'] ?? "topic-$i");
                $data = $this->normalize($raw, $slug, $type);

                $model = LesenTopic::class; // Hören moved to HoerenImportService.

                $model::updateOrCreate(['slug' => $slug], $data);
                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row $i: " . $e->getMessage();
            }
        }

        return compact('imported', 'skipped', 'errors');
    }

    private function normalize(array $raw, string $slug, string $type): array
    {
        $base = [
            'title'        => $raw['title']    ?? $slug,
            'title_ar'     => $raw['title_ar'] ?? null,
            'slug'         => $slug,
            'level'        => in_array($raw['level'] ?? '', ['B1','B2']) ? $raw['level'] : 'B1',
            'category'     => $raw['category'] ?? null,
            'is_published' => filter_var($raw['is_published'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];

        if ($type === 'lesen') {
            foreach (['teil1','teil2','teil3','sprachbausteine1','sprachbausteine2'] as $part) {
                $base[$part] = $this->decodeIfString($raw[$part] ?? null);
            }
        } else {
            $base['audio_path'] = $raw['audio_path'] ?? null;
            foreach (['teil1','teil2','teil3','teil4'] as $part) {
                $base[$part] = $this->decodeIfString($raw[$part] ?? null);
            }
        }

        return $base;
    }

    private function decodeIfString(mixed $value): ?array
    {
        if (is_array($value))  return $value;
        if (is_string($value) && !empty($value)) {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }
        return null;
    }
}
