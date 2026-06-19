<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** List-row shape for a Telc Lesen topic. */
class LesenTopicListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug'            => $this->slug,
            'title'           => $this->title,
            'title_ar'        => $this->title_ar,
            'level'           => $this->level,
            'category'        => $this->category,
            'parts_count'     => $this->parts_count,
            'questions_count' => $this->questions_count,
            'has_teil3'       => ! empty($this->teil3),
        ];
    }
}
