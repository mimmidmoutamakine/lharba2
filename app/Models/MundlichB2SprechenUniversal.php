<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton (id=1) holding the universal "sprechen_teil2_universal.json" payload:
 * universal argument categories, presentation structures, emergency blocks, meta.
 */
class MundlichB2SprechenUniversal extends Model
{
    protected $table = 'mundlich_b2_sprechen_universal';

    protected $fillable = ['payload'];

    protected $casts = ['payload' => 'array'];

    public static function singleton(): self
    {
        return self::firstOrCreate(['id' => 1], ['payload' => []]);
    }

    /** @return array<int,array<string,mixed>> */
    public function argumentCategories(): array
    {
        return $this->payload['universal_argument_categories'] ?? [];
    }

    /** Resolve a single argument category by its id, or null. */
    public function argumentById(string $id): ?array
    {
        foreach ($this->argumentCategories() as $c) {
            if (($c['id'] ?? null) === $id) return $c;
        }
        return null;
    }

    public function presentationStructures(): array
    {
        return $this->payload['presentation_structures'] ?? [];
    }

    public function emergencyBlocks(): array
    {
        return $this->payload['emergency_blocks'] ?? [];
    }

    public function meta(): array
    {
        return $this->payload['meta'] ?? [];
    }
}
