<?php

namespace App\Models;

use App\Models\Concerns\HasTopicTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LesenTopic extends Model
{
    use HasTopicTag;

    protected $fillable = [
        'title', 'title_ar', 'slug', 'level', 'category', 'is_published',
        'teil1', 'teil2', 'teil3', 'sprachbausteine1', 'sprachbausteine2',
    ];

    protected $casts = [
        'teil1'           => 'array',
        'teil2'           => 'array',
        'teil3'           => 'array',
        'sprachbausteine1'=> 'array',
        'sprachbausteine2'=> 'array',
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

    public function getPartsCountAttribute(): int
    {
        $count = 0;
        foreach (['teil1','teil2','teil3','sprachbausteine1','sprachbausteine2'] as $part) {
            if (!empty($this->$part)) $count++;
        }
        return $count;
    }

    public function getQuestionsCountAttribute(): int
    {
        $count = 0;
        foreach (['teil1','teil2','teil3','sprachbausteine1','sprachbausteine2'] as $part) {
            $data = $this->$part;
            if (is_array($data) && isset($data['questions'])) {
                $count += count($data['questions']);
            }
        }
        return $count;
    }
}
