<?php

namespace App\Services;

use App\Models\MundlichB2SprechenCluster;
use App\Models\MundlichB2SprechenTopic;
use App\Models\MundlichB2SprechenUniversal;

/**
 * Imports the three Sprechen-Teil-2 source files:
 *   - universal → singleton payload (replaced wholesale).
 *   - clusters  → one row per cluster, upserted by cluster_key.
 *   - topics    → one row per topic, upserted by slug.
 *
 * Rows not present in an uploaded clusters/topics file are NOT deleted — admin
 * manages removal explicitly (same policy as the Planen module).
 */
class MundlichB2SprechenImportService
{
    public const KINDS = ['universal', 'clusters', 'topics'];

    /** Universal file → singleton payload. */
    public function importUniversal(string $rawJson): array
    {
        $result = ['imported' => 0, 'skipped' => 0, 'errors' => [], 'summary' => []];

        $data = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result['errors'][] = 'Invalid JSON: ' . json_last_error_msg();
            return $result;
        }
        $cats = $data['universal_argument_categories'] ?? null;
        if (! is_array($data) || ! is_array($cats)) {
            $result['errors'][] = 'Universal JSON must have a top-level "universal_argument_categories" array.';
            return $result;
        }

        $row = MundlichB2SprechenUniversal::singleton();
        $row->payload = $data;
        $row->save();

        $result['imported'] = 1;
        $result['summary'] = [
            'argument_categories'    => count($cats),
            'has_structures'         => isset($data['presentation_structures']),
            'has_emergency_blocks'   => isset($data['emergency_blocks']),
        ];
        return $result;
    }

    /** Clusters file → upsert by cluster_key. */
    public function importClusters(string $rawJson): array
    {
        $result = ['imported' => 0, 'skipped' => 0, 'errors' => [], 'summary' => []];

        $data = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result['errors'][] = 'Invalid JSON: ' . json_last_error_msg();
            return $result;
        }
        if (! is_array($data) || ! isset($data['clusters']) || ! is_array($data['clusters'])) {
            $result['errors'][] = 'Clusters JSON must have a top-level "clusters" array.';
            return $result;
        }

        $imported = 0;
        $keys = [];
        foreach ($data['clusters'] as $i => $c) {
            if (! is_array($c)) { $result['skipped']++; $result['errors'][] = "Cluster #{$i}: not an object."; continue; }
            $key = trim((string) ($c['id'] ?? ''));
            if ($key === '') { $result['skipped']++; $result['errors'][] = "Cluster #{$i}: missing 'id'."; continue; }

            MundlichB2SprechenCluster::updateOrCreate(
                ['cluster_key' => $key],
                [
                    'title'                        => (string) ($c['title'] ?? $key),
                    'topic_orders'                 => array_values(array_filter(($c['topic_orders'] ?? []), 'is_numeric')),
                    'universal_argument_ids'       => array_values($c['universal_argument_ids'] ?? []),
                    'selected_universal_arguments' => array_values($c['selected_universal_arguments'] ?? []),
                    'cluster_vocabulary'           => array_values($c['cluster_vocabulary'] ?? []),
                    'position'                     => $i,
                    'is_published'                 => MundlichB2SprechenCluster::where('cluster_key', $key)->value('is_published') ?? true,
                ]
            );
            $imported++;
            $keys[] = $key;
        }

        $result['imported'] = $imported;
        $result['summary'] = ['cluster_count' => $imported, 'keys' => $keys];
        return $result;
    }

    /** Topics file → upsert by slug (= topic id). */
    public function importTopics(string $rawJson): array
    {
        $result = ['imported' => 0, 'skipped' => 0, 'errors' => [], 'summary' => []];

        $data = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $result['errors'][] = 'Invalid JSON: ' . json_last_error_msg();
            return $result;
        }
        if (! is_array($data) || ! isset($data['topics']) || ! is_array($data['topics'])) {
            $result['errors'][] = 'Topics JSON must have a top-level "topics" array.';
            return $result;
        }

        $imported = 0;
        $slugs = [];
        foreach ($data['topics'] as $i => $t) {
            if (! is_array($t)) { $result['skipped']++; $result['errors'][] = "Topic #{$i}: not an object."; continue; }
            $slug = trim((string) ($t['id'] ?? ''));
            if ($slug === '') { $result['skipped']++; $result['errors'][] = "Topic #{$i}: missing 'id'."; continue; }

            $args = is_array($t['arguments'] ?? null) ? $t['arguments'] : [];

            MundlichB2SprechenTopic::updateOrCreate(
                ['slug' => $slug],
                [
                    'order'                => (int) ($t['order'] ?? 0),
                    'title'                => (string) ($t['title'] ?? $slug),
                    'title_ar'             => isset($t['title_ar']) && $t['title_ar'] !== '' ? (string) $t['title_ar'] : null,
                    'cluster_ids'          => array_values($t['cluster_ids'] ?? []),
                    'highlight_sentences'  => array_values($t['highlight_sentences'] ?? []),
                    'main_ideas'           => array_values($t['main_ideas'] ?? []),
                    'arguments'            => [
                        'dafuer'  => array_values($args['dafuer'] ?? []),
                        'dagegen' => array_values($args['dagegen'] ?? []),
                    ],
                    'opinion_adjectives'   => is_array($t['opinion_adjectives'] ?? null) ? $t['opinion_adjectives'] : [],
                    'opinion_example'      => isset($t['opinion_example']) ? (string) $t['opinion_example'] : null,
                    'experience_example'   => isset($t['experience_example']) ? (string) $t['experience_example'] : null,
                    'difficult_vocabulary' => array_values($t['difficult_vocabulary'] ?? []),
                    'is_published'         => MundlichB2SprechenTopic::where('slug', $slug)->value('is_published') ?? true,
                ]
            );
            $imported++;
            $slugs[] = $slug;
        }

        $result['imported'] = $imported;
        $result['summary'] = ['topic_count' => $imported, 'slugs' => $slugs];
        return $result;
    }

    /** Dispatch by kind. */
    public function importByKind(string $kind, string $rawJson): array
    {
        return match ($kind) {
            'universal' => $this->importUniversal($rawJson),
            'clusters'  => $this->importClusters($rawJson),
            'topics'    => $this->importTopics($rawJson),
            default     => ['imported' => 0, 'skipped' => 0, 'errors' => ["Unknown kind: {$kind}"], 'summary' => []],
        };
    }

    /** Non-writing preview for the admin UI. */
    public function preview(string $rawJson, string $kind): array
    {
        $data = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON: ' . json_last_error_msg()];
        }
        if ($kind === 'universal') {
            $cats = $data['universal_argument_categories'] ?? null;
            if (! is_array($cats)) return ['error' => 'Missing "universal_argument_categories" array.'];
            return [
                'kind'                => 'universal',
                'argument_categories' => count($cats),
                'has_structures'      => isset($data['presentation_structures']),
                'has_emergency'       => isset($data['emergency_blocks']),
            ];
        }
        if ($kind === 'clusters') {
            if (! isset($data['clusters']) || ! is_array($data['clusters'])) return ['error' => 'Missing "clusters" array.'];
            $keys = array_values(array_filter(array_map(fn ($c) => $c['id'] ?? null, $data['clusters'])));
            return ['kind' => 'clusters', 'cluster_count' => count($keys), 'keys' => $keys];
        }
        if ($kind === 'topics') {
            if (! isset($data['topics']) || ! is_array($data['topics'])) return ['error' => 'Missing "topics" array.'];
            $slugs = array_values(array_filter(array_map(fn ($t) => $t['id'] ?? null, $data['topics'])));
            return ['kind' => 'topics', 'topic_count' => count($slugs), 'slugs' => $slugs];
        }
        return ['error' => "Unknown kind: {$kind}"];
    }
}
