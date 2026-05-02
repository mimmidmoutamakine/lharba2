<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SchreibenTopic extends Model
{
    protected $fillable = [
        'title', 'title_ar', 'slug', 'level', 'type', 'minutes',
        'scenario', 'points', 'is_published',
    ];

    protected $casts = [
        'points'       => 'array',
        'minutes'      => 'integer',
        'is_published' => 'boolean',
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
