<?php

namespace App\Models;

use App\Models\Concerns\HasTopicTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class HoerenExam extends Model
{
    use HasTopicTag;

    protected $fillable = [
        'module_id', 'slug', 'title', 'update_category', 'audio_path', 'position', 'is_published',
    ];

    protected $casts = [
        'position'     => 'integer',
        'is_published' => 'boolean',
    ];

    /** Provenance group filter keys (also the ?cat query values). */
    public const CATEGORY_GROUPS = ['standard', 'turkey', 'other'];

    /**
     * Normalize a raw update_category into a coarse provenance group:
     *   'turkey' | 'standard' | 'other' | null (none set)
     * Accepts spelling variants (turkey/türkei/tuerkei, legacy_standard, …).
     */
    public static function groupFor(?string $raw): ?string
    {
        $c = strtolower(trim((string) $raw));
        if ($c === '') return null;
        if (str_contains($c, 'turk') || str_contains($c, 'türk') || str_contains($c, 'tuerk')) {
            return 'turkey';
        }
        if (str_contains($c, 'standard')) {
            return 'standard';
        }
        return 'other';
    }

    public function categoryGroup(): ?string
    {
        return static::groupFor($this->update_category);
    }

    /** Filter exams whose update_category maps to a given provenance group. */
    public function scopeInCategoryGroup($query, string $group)
    {
        return match ($group) {
            'turkey' => $query->where(function ($q) {
                $q->where('update_category', 'like', '%turk%')
                  ->orWhere('update_category', 'like', '%türk%')
                  ->orWhere('update_category', 'like', '%tuerk%');
            }),
            'standard' => $query->where('update_category', 'like', '%standard%')
                ->where('update_category', 'not like', '%turk%'),
            'other' => $query->whereNotNull('update_category')
                ->where('update_category', 'not like', '%turk%')
                ->where('update_category', 'not like', '%türk%')
                ->where('update_category', 'not like', '%tuerk%')
                ->where('update_category', 'not like', '%standard%'),
            default => $query,
        };
    }

    /** Arabic chip label for the provenance group, or null when nothing to show. */
    public function categoryLabel(): ?string
    {
        return match ($this->categoryGroup()) {
            'turkey'   => 'تركيا',
            'standard' => 'ستاندارد',
            'other'    => $this->update_category,
            default    => null,
        };
    }

    /** Tailwind colour tone for the chip. */
    public function categoryTone(): string
    {
        return $this->categoryGroup() === 'turkey' ? 'rose' : 'slate';
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(HoerenModule::class, 'module_id');
    }

    public function statements(): HasMany
    {
        return $this->hasMany(HoerenExamStatement::class, 'exam_id')->orderBy('position');
    }

    /** Public URL for the uploaded audio file, or null if none uploaded. */
    public function audioUrl(): ?string
    {
        if (! $this->audio_path) return null;
        // Stored under storage/app/public/<audio_path> → served via /storage/<audio_path>
        return Storage::disk('public')->url($this->audio_path);
    }

    public function hasAudio(): bool
    {
        return ! empty($this->audio_path) && Storage::disk('public')->exists($this->audio_path);
    }
}
