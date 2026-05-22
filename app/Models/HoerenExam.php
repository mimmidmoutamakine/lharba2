<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class HoerenExam extends Model
{
    protected $fillable = [
        'module_id', 'slug', 'title', 'audio_path', 'position', 'is_published',
    ];

    protected $casts = [
        'position'     => 'integer',
        'is_published' => 'boolean',
    ];

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
