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
}
