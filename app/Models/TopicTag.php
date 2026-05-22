<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Admin flag/note attached to any topic-like model (LesenTopic, HoerenExam,
 * GoetheB1LesenTopic, etc.) via the HasTopicTag trait.
 */
class TopicTag extends Model
{
    public const TAG_NEW          = 'new';            // جديد — green badge
    public const TAG_RARE         = 'rare';           // نادر فاش كيتحط
    public const TAG_DISCONTINUED = 'discontinued';   // ما بقاش كيتحط
    public const TAG_NOTE         = 'note';           // ملاحظة خاصة (note text only)

    public const ALL = [
        self::TAG_NEW,
        self::TAG_RARE,
        self::TAG_DISCONTINUED,
        self::TAG_NOTE,
    ];

    protected $fillable = ['tag', 'note', 'created_by', 'taggable_type', 'taggable_id'];

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Human-readable Darija label for the tag. */
    public function label(): string
    {
        return match ($this->tag) {
            self::TAG_NEW          => 'جديد',
            self::TAG_RARE         => 'نادر فاش كيتحط',
            self::TAG_DISCONTINUED => 'ما بقاش كيتحط',
            self::TAG_NOTE         => 'ملاحظة',
            default                => $this->tag,
        };
    }

    /**
     * Colour tone for the card badge. Used by views to pick Tailwind classes.
     *   emerald (new) · amber (rare) · red (discontinued) · sky (plain note)
     */
    public function tone(): string
    {
        return match ($this->tag) {
            self::TAG_NEW          => 'emerald',
            self::TAG_RARE         => 'amber',
            self::TAG_DISCONTINUED => 'red',
            self::TAG_NOTE         => 'sky',
            default                => 'slate',
        };
    }
}
