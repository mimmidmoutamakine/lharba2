<?php

namespace App\Models\Concerns;

use App\Models\TopicTag;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Adds polymorphic `topicTag` relation + helpers to any model that should
 * support admin-set flags ("جديد", "نادر فاش كيتحط", etc.).
 *
 * Usage:
 *   class LesenTopic extends Model {
 *       use HasTopicTag;
 *   }
 *
 *   $topic->topicTag                // ?TopicTag
 *   $topic->setTag('new');          // upserts
 *   $topic->setTag('rare', 'سؤال هاد التيل خرج 2x');
 *   $topic->clearTag();
 *
 * Always eager-load to avoid N+1 in list views:
 *   LesenTopic::with('topicTag')->get();
 */
trait HasTopicTag
{
    public function topicTag(): MorphOne
    {
        return $this->morphOne(TopicTag::class, 'taggable');
    }

    /**
     * Upsert the single tag row for this model. Returns the persisted TopicTag.
     */
    public function setTag(string $tag, ?string $note = null, ?int $createdBy = null): TopicTag
    {
        return TopicTag::updateOrCreate(
            [
                'taggable_type' => static::class,
                'taggable_id'   => $this->getKey(),
            ],
            [
                'tag'        => $tag,
                'note'       => $note,
                'created_by' => $createdBy,
            ],
        );
    }

    /** Remove the tag (no-op if none). */
    public function clearTag(): void
    {
        TopicTag::where('taggable_type', static::class)
            ->where('taggable_id', $this->getKey())
            ->delete();
    }
}
