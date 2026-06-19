<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single graded submission of one Teil of a LesenTopic, made from the mobile app.
 * answers = { situationId: adId } for teil3; score/total are computed server-side.
 */
class LesenAttempt extends Model
{
    protected $fillable = [
        'user_id', 'lesen_topic_id', 'part', 'answers', 'score', 'total',
    ];

    protected $casts = [
        'answers' => 'array',
        'score'   => 'integer',
        'total'   => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesenTopic(): BelongsTo
    {
        return $this->belongsTo(LesenTopic::class);
    }
}
