<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HoerenModule extends Model
{
    protected $fillable = [
        'level', 'teil', 'subtitle', 'description',
        'footer_note', 'footer_guide', 'is_published',
    ];

    protected $casts = [
        'teil'         => 'integer',
        'is_published' => 'boolean',
    ];

    public function codes(): HasMany
    {
        return $this->hasMany(HoerenCode::class, 'module_id')->orderBy('position');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(HoerenExam::class, 'module_id')->orderBy('position');
    }

    /** Resolve a single (level, teil) module — common lookup. */
    public static function forLevelTeil(string $level, int $teil): ?self
    {
        return static::where('level', $level)
            ->where('teil', $teil)
            ->where('is_published', true)
            ->first();
    }
}
