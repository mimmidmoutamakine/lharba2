<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full Telc Lesen topic for the exercise screen. MVP returns Teil 3 only,
 * and DELIBERATELY omits the `correctAnswers` key (the app gets it back from
 * /submit). `ad.summary` (Arabic translation) is kept but the app must only
 * display it after submission.
 */
class LesenTopicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug'     => $this->slug,
            'title'    => $this->title,
            'title_ar' => $this->title_ar,
            'level'    => $this->level,
            'category' => $this->category,
            'teil3'    => $this->teil3Public(),
        ];
    }

    /** Teil 3 with the answer key stripped out. */
    private function teil3Public(): ?array
    {
        $teil3 = $this->teil3;
        if (! is_array($teil3)) {
            return null;
        }

        $public = [
            'situations' => array_values($teil3['situations'] ?? []),
            'ads'        => array_values($teil3['ads'] ?? []),
        ];

        // Pass through optional presentation fields if the content has them.
        foreach (['intro', 'instructions', 'title', 'title_ar'] as $optional) {
            if (array_key_exists($optional, $teil3)) {
                $public[$optional] = $teil3[$optional];
            }
        }

        // `correctAnswers` intentionally NOT included.
        return $public;
    }
}
