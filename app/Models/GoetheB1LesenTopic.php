<?php

namespace App\Models;

use App\Models\Concerns\HasTopicTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GoetheB1LesenTopic extends Model
{
    use HasTopicTag;

    protected $fillable = [
        'title', 'title_ar', 'slug', 'level', 'is_published',
        'teil1', 'teil2', 'teil3', 'teil4', 'teil5',
    ];

    protected $casts = [
        'teil1'        => 'array',
        'teil2'        => 'array',
        'teil3'        => 'array',
        'teil4'        => 'array',
        'teil5'        => 'array',
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

    public const PARTS = ['teil1', 'teil2', 'teil3', 'teil4', 'teil5'];

    public function getPartsCountAttribute(): int
    {
        $count = 0;
        foreach (self::PARTS as $part) {
            if (!empty($this->$part)) $count++;
        }
        return $count;
    }

    public function getQuestionsCountAttribute(): int
    {
        $count = 0;
        // Teil 1 (questions), Teil 2 (questions), Teil 3 (situations), Teil 4 (comments), Teil 5 (questions)
        foreach (['teil1' => 'questions', 'teil2' => 'questions', 'teil3' => 'situations', 'teil4' => 'comments', 'teil5' => 'questions'] as $part => $key) {
            $data = $this->$part;
            if (is_array($data) && isset($data[$key]) && is_array($data[$key])) {
                $count += count($data[$key]);
            }
        }
        return $count;
    }
}
