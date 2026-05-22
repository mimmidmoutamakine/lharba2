<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoerenCode extends Model
{
    protected $fillable = [
        'module_id', 'code', 'topic_title', 'story_ar', 'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(HoerenModule::class, 'module_id');
    }
}
