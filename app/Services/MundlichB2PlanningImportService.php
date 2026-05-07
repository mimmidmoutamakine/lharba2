<?php

namespace App\Services;

use App\Models\MundlichB2PlanningStructure;
use App\Models\MundlichB2PlanningTopic;

class MundlichB2PlanningImportService
{
    public const KINDS = ['structures', 'topics'];

    /**
     * Import the universal "planning_structures.json" — replaces the singleton row's payload.
     *
     * @return array{imported:int, skipped:int, errors:array<string>, summary:array<string,mixed>}
     */
    public function importStructures(string $rawJson): array
    {
        $result = ['imported' => 0, 'skipped' => 0, 'errors' => [], 'summary' => []];

        $data = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result['errors'][] = 'Invalid JSON: ' . json_last_error_msg();
            return $result;
        }
        if (!is_array($data) || !isset($data['aspekte']) || !is_array($data['aspekte'])) {
            $result['errors'][] = 'Structures JSON must have a top-level "aspekte" array.';
            return $result;
        }

        // Soft-validate each aspekt has minimum required fields.
        $aspectCount = 0;
        foreach ($data['aspekte'] as $i => $a) {
            if (!is_array($a) || !isset($a['id']) || !isset($a['label'])) {
                $result['errors'][] = "Aspekt #{$i}: missing 'id' or 'label'.";
                continue;
            }
            $aspectCount++;
        }
        if ($aspectCount === 0) {
            $result['errors'][] = 'No valid aspekte found.';
            return $result;
        }

        $row = MundlichB2PlanningStructure::singleton();
        $row->payload = $data;
        $row->save();

        $result['imported'] = 1;
        $result['summary'] = [
            'aspekte_count'  => $aspectCount,
            'has_flow'       => isset($data['conversation_flow_template']),
            'has_summary'    => isset($data['summary_formula']),
            'level'          => $data['metadata']['level'] ?? null,
        ];
        return $result;
    }

    /**
     * Import "topic_vocabulary_bank.json" — upsert each topic by slug.
     * Topics not in the file are NOT removed (admin manages deletion).
     *
     * @return array{imported:int, skipped:int, errors:array<string>, summary:array<string,mixed>}
     */
    public function importTopics(string $rawJson): array
    {
        $result = ['imported' => 0, 'skipped' => 0, 'errors' => [], 'summary' => []];

        $data = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result['errors'][] = 'Invalid JSON: ' . json_last_error_msg();
            return $result;
        }
        if (!is_array($data) || !isset($data['topics']) || !is_array($data['topics'])) {
            $result['errors'][] = 'Topics JSON must have a top-level "topics" array.';
            return $result;
        }

        $imported = 0;
        $slugs    = [];
        foreach ($data['topics'] as $i => $t) {
            if (!is_array($t)) {
                $result['skipped']++;
                $result['errors'][] = "Topic #{$i}: not an object.";
                continue;
            }
            $slug = trim((string) ($t['id'] ?? ''));
            if ($slug === '') {
                $result['skipped']++;
                $result['errors'][] = "Topic #{$i}: missing 'id'.";
                continue;
            }
            $aspekte = $t['aspekte'] ?? [];
            if (!is_array($aspekte)) {
                $result['skipped']++;
                $result['errors'][] = "Topic '{$slug}': 'aspekte' must be an object.";
                continue;
            }

            // Prefer the original task text, fall back to short or topic_itself.
            $topicText = $t['exam_task_original']
                ?? $t['topic_itself']
                ?? $t['exam_task_short']
                ?? null;

            MundlichB2PlanningTopic::updateOrCreate(
                ['slug' => $slug],
                [
                    'label'      => (string) ($t['label'] ?? $t['topic_title'] ?? $slug),
                    'topic_type' => isset($t['topic_type']) ? (string) $t['topic_type'] : null,
                    'topic_text' => $topicText,
                    'aspekte'    => $aspekte,
                    // Preserve existing publish state on update; default true on create.
                    'is_published' => MundlichB2PlanningTopic::where('slug', $slug)->value('is_published') ?? true,
                ]
            );
            $imported++;
            $slugs[] = $slug;
        }

        $result['imported'] = $imported;
        $result['summary'] = [
            'topic_count' => $imported,
            'slugs'       => $slugs,
        ];
        return $result;
    }

    /**
     * Quick preview without writing — returns a summary of what an upload would do.
     */
    public function preview(string $rawJson, string $kind): array
    {
        $data = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON: ' . json_last_error_msg()];
        }
        if ($kind === 'structures') {
            if (!isset($data['aspekte']) || !is_array($data['aspekte'])) {
                return ['error' => 'Missing top-level "aspekte" array.'];
            }
            $cats = ['universal' => 0, 'semi_universal' => 0, 'topic_specific' => 0];
            foreach ($data['aspekte'] as $a) {
                $c = $a['category'] ?? 'universal';
                $cats[$c] = ($cats[$c] ?? 0) + 1;
            }
            return [
                'kind'           => 'structures',
                'aspekte_count'  => count($data['aspekte']),
                'categories'     => $cats,
                'has_flow'       => isset($data['conversation_flow_template']),
                'has_summary'    => isset($data['summary_formula']),
            ];
        }
        if ($kind === 'topics') {
            if (!isset($data['topics']) || !is_array($data['topics'])) {
                return ['error' => 'Missing top-level "topics" array.'];
            }
            $slugs = [];
            foreach ($data['topics'] as $t) {
                if (is_array($t) && !empty($t['id'])) $slugs[] = $t['id'];
            }
            return [
                'kind'        => 'topics',
                'topic_count' => count($slugs),
                'slugs'       => $slugs,
            ];
        }
        return ['error' => "Unknown kind: {$kind}"];
    }
}
