<?php

namespace App\Http\Controllers;

use App\Models\SchreibenTopic;
use App\Services\GeminiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class SchreibenController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->canSeeGermanContent()) {
            return view('content.coming-soon', ['access' => $user->currentAccess()]);
        }

        $approvedLevel = $user->contentLevel();
        $level = $approvedLevel ?: (in_array($request->level, ['B1', 'B2'], true) ? $request->level : null);
        $type  = $request->type ?: null;

        $topics = SchreibenTopic::where('is_published', true)
            ->when($level, fn ($q) => $q->where('level', $level))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderBy('level')
            ->orderBy('title')
            ->get();

        $types = SchreibenTopic::where('is_published', true)
            ->when($level, fn ($q) => $q->where('level', $level))
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('schreiben.index', compact('topics', 'level', 'type', 'types'));
    }

    public function topic(Request $request, string $slug)
    {
        $user = $request->user();
        if (! $user->canSeeGermanContent()) {
            return view('content.coming-soon', ['access' => $user->currentAccess()]);
        }

        $topic = SchreibenTopic::where('slug', $slug)->where('is_published', true)->firstOrFail();
        if (! $user->is_admin && $user->contentLevel() && $topic->level !== $user->contentLevel()) {
            abort(403, 'هاد الموضوع خارج نطاق الوصول ديالك.');
        }
        $timerEnabled = $request->boolean('timer');
        return view('schreiben.topic', compact('topic', 'timerEnabled'));
    }

    public function pdf(string $slug)
    {
        $topic = SchreibenTopic::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $pdf   = Pdf::loadView('schreiben.pdf', compact('topic'))->setPaper('a4');
        return $pdf->download(Str::slug($topic->title . '-schreiben-' . $topic->level) . '.pdf');
    }

    public function grade(Request $request, GeminiService $gemini)
    {
        $data = $request->validate([
            'topic_id' => 'required|integer|exists:schreiben_topics,id',
            'text'     => 'required|string|min:30|max:5000',
        ]);

        $topic = SchreibenTopic::where('id', $data['topic_id'])
            ->where('is_published', true)
            ->firstOrFail();

        try {
            $feedback = $gemini->correctSchreiben($topic, $data['text']);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'error' => $e->getMessage() ?: 'AI grading failed.',
            ], 502);
        }

        return response()->json([
            'ok'       => true,
            'feedback' => $feedback,
        ]);
    }

    public function generate(Request $request, GeminiService $gemini)
    {
        $data = $request->validate([
            'topic_id'              => 'required|integer|exists:schreiben_topics,id',
            'email_type'            => 'nullable|string|max:80',
            'selections'            => 'required|array|min:2|max:4',
            'selections.*.label'    => 'required|string|max:300',
            'selections.*.ideas'    => 'nullable|string|max:1000',
        ]);

        $topic = SchreibenTopic::where('id', $data['topic_id'])
            ->where('is_published', true)
            ->firstOrFail();

        if ($topic->level !== 'B2') {
            return response()->json([
                'error' => 'إنشاء النموذج متاح حالياً فقط لمواضيع B2.',
            ], 422);
        }

        try {
            $result = $gemini->generateExample(
                $topic,
                (string) ($data['email_type'] ?? $topic->type ?? 'Beschwerde'),
                $data['selections']
            );
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'error' => $e->getMessage() ?: 'AI generation failed.',
            ], 502);
        }

        return response()->json([
            'ok'     => true,
            'result' => $result,
        ]);
    }
}
