<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MundlichB2PlanningStructure extends Model
{
    protected $table = 'mundlich_b2_planning_structures';

    protected $fillable = ['payload'];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Always read/write a single row (id=1). Returns the row, creating it if missing.
     */
    public static function singleton(): self
    {
        return self::firstOrCreate(['id' => 1], ['payload' => []]);
    }

    public function aspekte(): array
    {
        return $this->payload['aspekte'] ?? [];
    }

    public function aspectIndex(): array
    {
        return $this->payload['aspect_index'] ?? [];
    }

    public function conversationFlow(): array
    {
        return $this->payload['conversation_flow_template'] ?? [];
    }

    public function summaryFormula(): array
    {
        return $this->payload['summary_formula'] ?? [];
    }

    public function metadata(): array
    {
        return $this->payload['metadata'] ?? [];
    }
}
