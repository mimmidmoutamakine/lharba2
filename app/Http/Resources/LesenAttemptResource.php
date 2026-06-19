<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** One row of a user's attempt history. */
class LesenAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'part'       => $this->part,
            'score'      => $this->score,
            'total'      => $this->total,
            'created_at' => $this->created_at?->toIso8601String(),
            'topic'      => $this->whenLoaded('lesenTopic', fn () => [
                'slug'     => $this->lesenTopic->slug,
                'title'    => $this->lesenTopic->title,
                'title_ar' => $this->lesenTopic->title_ar,
                'level'    => $this->lesenTopic->level,
            ]),
        ];
    }
}
