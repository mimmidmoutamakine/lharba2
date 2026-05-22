<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoerenExamStatement extends Model
{
    protected $fillable = [
        'exam_id', 'text', 'answer', 'highlights', 'explanation_highlights', 'position',
    ];

    protected $casts = [
        'highlights'             => 'array',
        'explanation_highlights' => 'array',
        'position'               => 'integer',
    ];

    public const ANSWER_RICHTIG = '+';
    public const ANSWER_FALSCH  = '-';

    public function exam(): BelongsTo
    {
        return $this->belongsTo(HoerenExam::class, 'exam_id');
    }

    public function isRichtig(): bool
    {
        return $this->answer === self::ANSWER_RICHTIG;
    }
}
