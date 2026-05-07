<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MundlichB2PlanningTopic extends Model
{
    protected $table = 'mundlich_b2_planning_topics';

    protected $fillable = [
        'slug', 'label', 'label_ar', 'topic_type', 'topic_text',
        'aspekte', 'is_published',
    ];

    protected $casts = [
        'aspekte'      => 'array',
        'is_published' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Filter the structures' aspekte to only those relevant for THIS topic.
     * Rules:
     *  - category=universal with used_in_topics in ["all", "most"] → always include.
     *  - category=semi_universal with used_in_topics array containing this slug → include.
     *  - category=topic_specific with only_for_topics array containing this slug → include.
     */
    public function relevantAspekte(array $allAspekte): array
    {
        $out = [];
        foreach ($allAspekte as $a) {
            $category = $a['category'] ?? 'universal';
            $used     = $a['used_in_topics'] ?? null;
            $only     = $a['only_for_topics'] ?? [];

            $relevant = false;
            if ($category === 'universal') {
                $relevant = true;
            } elseif ($category === 'semi_universal' && is_array($used) && in_array($this->slug, $used, true)) {
                $relevant = true;
            } elseif ($category === 'topic_specific' && is_array($only) && in_array($this->slug, $only, true)) {
                $relevant = true;
            }

            if ($relevant) $out[] = $a;
        }
        return $out;
    }

    /**
     * The topic's vocab list for a given aspekt id, or [] if none.
     */
    public function vocabFor(string $aspektId): array
    {
        $a = $this->aspekte ?? [];
        return $a[$aspektId] ?? [];
    }
}
