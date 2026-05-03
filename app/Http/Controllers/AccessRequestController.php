<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccessRequestController extends Controller
{
    public function create()
    {
        $user = request()->user();

        // Already approved with no pending change request → show profile-ish page instead
        if ($user->currentAccess() && ! $user->pendingAccess()) {
            // Allow them to come here to submit a new request — show the form normally
        }

        return view('access.create', [
            'languages' => AccessRequest::LANGUAGES,
            'examsByLanguage' => AccessRequest::EXAMS_BY_LANGUAGE,
            'levels'    => AccessRequest::LEVELS,
            'pending'   => $user->pendingAccess(),
            'current'   => $user->currentAccess(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Block stacking: only one pending at a time
        if ($user->pendingAccess()) {
            return redirect()->route('access.pending');
        }

        $data = $request->validate([
            'language' => ['required', Rule::in(array_keys(AccessRequest::LANGUAGES))],
            'exam'     => ['required', 'string', 'max:32'],
            'level'    => ['required', Rule::in(AccessRequest::LEVELS)],
        ]);

        // Validate exam belongs to language
        $allowedExams = AccessRequest::EXAMS_BY_LANGUAGE[$data['language']] ?? [];
        if (! in_array($data['exam'], $allowedExams, true)) {
            return back()->withInput()->withErrors(['exam' => 'الاختبار غير متوفر لهاد اللغة.']);
        }

        AccessRequest::create([
            'user_id'  => $user->id,
            'language' => $data['language'],
            'exam'     => $data['exam'],
            'level'    => $data['level'],
            'status'   => AccessRequest::STATUS_PENDING,
        ]);

        return redirect()->route('access.pending');
    }

    public function pending()
    {
        $user = request()->user();
        return view('access.pending', [
            'pending' => $user->pendingAccess(),
            'current' => $user->currentAccess(),
        ]);
    }

    /**
     * Lightweight polling endpoint — the layout's JS calls this every ~20 seconds.
     * Returns whether the user has a freshly-approved access request that hasn't
     * been "welcomed" yet, so the overlay can pop up automatically.
     */
    public function poll(Request $request)
    {
        // Wrap everything — never let this endpoint 500.
        // If anything goes wrong (missing migration, DB blip, etc.) just return
        // welcome:false so the client keeps polling silently.
        try {
            $user = $request->user();
            $req  = $user?->pendingWelcomeRequest();

            if (! $req) {
                return response()->json(['welcome' => false]);
            }

            return response()->json([
                'welcome' => true,
                'request' => [
                    'id'             => $req->id,
                    'language'       => $req->language,
                    'language_label' => $req->languageLabel(),
                    'exam'           => $req->exam,
                    'level'          => $req->level,
                    'decided_at'     => $req->decided_at?->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('access.poll failed: ' . $e->getMessage());
            return response()->json(['welcome' => false]);
        }
    }

    /**
     * Mark the user's pending welcome as seen so the overlay stops appearing.
     */
    public function markWelcomed(Request $request)
    {
        $user = $request->user();
        $req  = $user->pendingWelcomeRequest();

        if ($req) {
            $req->update(['welcomed_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }
}
