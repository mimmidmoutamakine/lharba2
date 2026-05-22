<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoetheB1LesenTopic;
use App\Models\HoerenExam;
use App\Models\LesenTopic;
use App\Models\SchreibenTopic;
use App\Models\TopicTag;
use Illuminate\Http\Request;

/**
 * Admin-only: set / clear a polymorphic topic tag on any taggable model.
 *
 * URL form: /admin/topic-tags/{type}/{id}
 *   type ∈ { lesen, hoeren-exam, goethe-b1-lesen }   ← whitelist below
 *   id   = primary key of the target row
 *
 * Posting accepts:   tag (required, one of TopicTag::ALL) + note (optional)
 * Deleting removes the tag for that row.
 */
class TagController extends Controller
{
    /** Whitelist mapping URL slug → fully-qualified model class. */
    private const TYPE_MAP = [
        'lesen'           => LesenTopic::class,
        'hoeren-exam'     => HoerenExam::class,
        'goethe-b1-lesen' => GoetheB1LesenTopic::class,
        'schreiben'       => SchreibenTopic::class,
    ];

    public function set(Request $request, string $type, int $id)
    {
        $request->validate([
            'tag'  => 'required|in:' . implode(',', TopicTag::ALL),
            'note' => 'nullable|string|max:500',
        ], [
            'tag.required' => 'اختار نوع الإشارة.',
            'tag.in'       => 'نوع الإشارة غير صالح.',
            'note.max'     => 'الملاحظة طويلة بزاف (الحد الأقصى 500 حرف).',
        ]);

        $instance = $this->find($type, $id);

        $instance->setTag(
            tag:       $request->input('tag'),
            note:      $request->input('note') ?: null,
            createdBy: $request->user()->id,
        );

        return back()->with('ok', 'تم وضع الإشارة على « ' . $this->labelFor($instance) . ' ».');
    }

    public function clear(Request $request, string $type, int $id)
    {
        $instance = $this->find($type, $id);
        $instance->clearTag();

        return back()->with('ok', 'تم مسح الإشارة على « ' . $this->labelFor($instance) . ' ».');
    }

    /** Resolve {type} slug → model instance, or 404. */
    private function find(string $type, int $id)
    {
        $class = self::TYPE_MAP[$type] ?? null;
        abort_if(! $class, 404, 'Unknown taggable type.');

        return $class::findOrFail($id);
    }

    /** Best-effort label for the toast message. */
    private function labelFor($instance): string
    {
        return $instance->title ?? ('#' . $instance->getKey());
    }
}
