<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LesenAttemptResource;
use App\Http\Resources\LesenTopicListResource;
use App\Http\Resources\LesenTopicResource;
use App\Models\LesenAttempt;
use App\Models\LesenTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Telc Lesen API for the mobile app. MVP scope: Teil 3 (Zuordnungen / matching).
 * Mirrors the web's access gating: the user needs an approved AccessRequest
 * (has.access middleware) and the content must match their language + level.
 */
class LesenController extends Controller
{
    /**
     * Enforce the same access rules the web enforces; return the level filter
     * (null = admin / no restriction). Aborts with 403 when the user may not view.
     */
    private function gate(Request $request): ?string
    {
        $user = $request->user();

        if (! $user->hasAnyAccess()) {
            abort(403, 'access_required');      // no approved AccessRequest yet
        }
        if (! $user->canSeeGermanContent()) {
            abort(403, 'language_mismatch');     // approved for a non-German exam
        }

        return $user->contentLevel();            // null for admins
    }

    /** GET /api/lesen/telc — published Telc Lesen topics that have a Teil 3. */
    public function index(Request $request)
    {
        $level = $this->gate($request);

        $topics = LesenTopic::query()
            ->where('is_published', true)
            ->whereNotNull('teil3')
            ->when($level, fn ($q) => $q->where('level', $level))
            ->orderBy('level')
            ->orderBy('title')
            ->paginate(20);

        return LesenTopicListResource::collection($topics);
    }

    /** GET /api/lesen/telc/{slug} — full Teil 3 WITHOUT the answer key. */
    public function show(Request $request, string $slug)
    {
        $level = $this->gate($request);

        $topic = LesenTopic::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        if ($level && $topic->level !== $level) {
            abort(403, 'level_mismatch');
        }

        return new LesenTopicResource($topic);
    }

    /** POST /api/lesen/telc/{slug}/submit — score server-side, store attempt. */
    public function submit(Request $request, string $slug): JsonResponse
    {
        $level = $this->gate($request);

        $data = $request->validate([
            'part'    => ['required', 'in:teil3'],
            'answers' => ['required', 'array'],
        ]);

        $topic = LesenTopic::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        if ($level && $topic->level !== $level) {
            abort(403, 'level_mismatch');
        }

        $teil3 = is_array($topic->teil3) ? $topic->teil3 : [];

        // Normalise the answer key to string=>string ({situationId: adId}).
        $key = collect($teil3['correctAnswers'] ?? [])
            ->mapWithKeys(fn ($v, $k) => [(string) $k => (string) $v])
            ->all();

        if (empty($key)) {
            abort(422, 'no_answer_key'); // content not gradable (missing correctAnswers)
        }

        $answers = collect($data['answers'])
            ->mapWithKeys(fn ($v, $k) => [(string) $k => (string) $v])
            ->all();

        $perSituation = [];
        $score = 0;

        foreach ($key as $situationId => $correct) {
            $chosen    = $answers[$situationId] ?? null;
            $isCorrect = $chosen !== null && $chosen === $correct;
            if ($isCorrect) {
                $score++;
            }
            $perSituation[$situationId] = [
                'chosen'    => $chosen,
                'correct'   => $correct,
                'isCorrect' => $isCorrect,
            ];
        }

        $total = count($key);

        $attempt = LesenAttempt::create([
            'user_id'        => $request->user()->id,
            'lesen_topic_id' => $topic->id,
            'part'           => 'teil3',
            'answers'        => $answers,
            'score'          => $score,
            'total'          => $total,
        ]);

        return response()->json([
            'attempt_id'     => $attempt->id,
            'score'          => $score,
            'total'          => $total,
            'correctAnswers' => $key,           // revealed only on submit
            'per_situation'  => $perSituation,
        ]);
    }

    /** GET /api/me/attempts — the authenticated user's attempt history. */
    public function attempts(Request $request)
    {
        $attempts = $request->user()
            ->lesenAttempts()
            ->with('lesenTopic:id,slug,title,title_ar,level')
            ->paginate(20);

        return LesenAttemptResource::collection($attempts);
    }
}
