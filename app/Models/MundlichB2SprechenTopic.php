<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * One exam text for Sprechen Teil 2. Holds the per-topic preparation material:
 * highlight sentences, main ideas, pro/contra argument chips, opinion + experience
 * model answers, and difficult vocabulary.
 */
class MundlichB2SprechenTopic extends Model
{
    protected $table = 'mundlich_b2_sprechen_topics';

    protected $fillable = [
        'slug', 'order', 'title', 'title_ar', 'cluster_ids',
        'highlight_sentences', 'main_ideas', 'arguments', 'opinion_adjectives',
        'opinion_example', 'experience_example', 'difficult_vocabulary', 'is_published',
    ];

    protected $casts = [
        'order'                => 'integer',
        'cluster_ids'          => 'array',
        'highlight_sentences'  => 'array',
        'main_ideas'           => 'array',
        'arguments'            => 'array',
        'opinion_adjectives'   => 'array',
        'difficult_vocabulary' => 'array',
        'is_published'         => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function argumentsDafuer(): array
    {
        return $this->arguments['dafuer'] ?? [];
    }

    public function argumentsDagegen(): array
    {
        return $this->arguments['dagegen'] ?? [];
    }

    public function opinionPositive(): array
    {
        return $this->opinion_adjectives['positive'] ?? [];
    }

    public function opinionNegative(): array
    {
        return $this->opinion_adjectives['negative'] ?? [];
    }

    /** The published clusters this topic belongs to. */
    public function clusters(): Collection
    {
        $keys = $this->cluster_ids ?: [];
        if (! $keys) return new Collection();
        return MundlichB2SprechenCluster::where('is_published', true)
            ->whereIn('cluster_key', $keys)
            ->orderBy('position')
            ->get();
    }
}
