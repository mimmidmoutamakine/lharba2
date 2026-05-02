<?php

namespace App\Http\Controllers;

use App\Models\HoerenTopic;
use Illuminate\Http\Request;

class HoerenController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->canSeeGermanContent()) {
            return view('content.coming-soon', ['access' => $user->currentAccess()]);
        }

        $approvedLevel = $user->contentLevel(); // null for admins → no filter
        $level = $approvedLevel ?: (in_array($request->level, ['B1', 'B2'], true) ? $request->level : null);
        $teil  = in_array((int) $request->teil, [1, 2, 3], true) ? (int) $request->teil : null;

        $topics = HoerenTopic::where('is_published', true)
            ->when($level, fn ($q) => $q->where('level', $level))
            ->when($teil,  fn ($q) => $q->where('teil', $teil))
            ->orderBy('teil')
            ->orderBy('level')
            ->orderBy('title')
            ->get();

        return view('hoeren.index', compact('topics', 'level', 'teil'));
    }

    public function topic(Request $request, string $slug)
    {
        $user = $request->user();
        if (! $user->canSeeGermanContent()) {
            return view('content.coming-soon', ['access' => $user->currentAccess()]);
        }

        $topic = HoerenTopic::where('slug', $slug)->where('is_published', true)->firstOrFail();

        // Block accessing topics outside the user's approved level (admins bypass).
        if (! $user->is_admin && $user->contentLevel() && $topic->level !== $user->contentLevel()) {
            abort(403, 'هاد الموضوع خارج نطاق الوصول ديالك.');
        }

        return view('hoeren.topic', compact('topic'));
    }

    public function submit(Request $request, string $slug)
    {
        return redirect()->route('hoeren.index');
    }
}
