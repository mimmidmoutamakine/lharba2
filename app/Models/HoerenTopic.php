<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HoerenTopic extends Model
{
    protected $fillable = [
        'title', 'title_ar', 'slug', 'level', 'teil',
        'audio_url', 'duration', 'correct_numbers', 'flashcards',
        'statements', 'is_published',
    ];

    protected $casts = [
        'teil'            => 'integer',
        'correct_numbers' => 'array',
        'flashcards'      => 'array',
        'statements'      => 'array',
        'is_published'    => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $topic) {
            if (empty($topic->slug)) {
                $topic->slug = Str::slug($topic->title);
            }
        });
    }
}
