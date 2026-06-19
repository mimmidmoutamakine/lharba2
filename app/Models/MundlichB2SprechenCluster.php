<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A topic-family ("cluster") grouping several Teil-2 topics that share the same
 * universal arguments and vocabulary. Master one cluster → handle several exam themes.
 */
class MundlichB2SprechenCluster extends Model
{
    protected $table = 'mundlich_b2_sprechen_clusters';

    protected $fillable = [
        'cluster_key', 'title', 'topic_orders', 'universal_argument_ids',
        'selected_universal_arguments', 'cluster_vocabulary', 'position', 'is_published',
    ];

    protected $casts = [
        'topic_orders'                 => 'array',
        'universal_argument_ids'       => 'array',
        'selected_universal_arguments' => 'array',
        'cluster_vocabulary'           => 'array',
        'position'                     => 'integer',
        'is_published'                 => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'cluster_key';
    }

    /**
     * This cluster's published topics, resolved by `order` ∈ topic_orders.
     * (Not a standard relation — clusters and topics join on the order list, not a FK.)
     */
    public function topicsList()
    {
        return MundlichB2SprechenTopic::where('is_published', true)
            ->whereIn('order', $this->topic_orders ?: [-1])
            ->orderBy('order')
            ->get();
    }

    /**
     * Resolve this cluster's universal argument categories from the universal singleton.
     * @return array<int,array<string,mixed>>
     */
    public function universalArguments(MundlichB2SprechenUniversal $universal): array
    {
        $out = [];
        foreach ($this->universal_argument_ids ?? [] as $id) {
            if ($cat = $universal->argumentById($id)) $out[] = $cat;
        }
        return $out;
    }
}
