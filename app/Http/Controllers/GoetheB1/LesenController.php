<?php

namespace App\Http\Controllers\GoetheB1;

use App\Http\Controllers\Controller;
use App\Models\GoetheB1LesenTopic;
use Illuminate\Http\Request;

class LesenController extends Controller
{
    private const TEIL_COLUMNS = ['teil1', 'teil2', 'teil3', 'teil4', 'teil5'];

    private function gateOrComingSoon(Request $request)
    {
        $user = $request->user();
        if ($user->is_admin) return null;
        $access = $user->currentAccess();
        // Goethe B1 access: language=de, exam=Goethe, level=B1 (case-insensitive on exam)
        $hasGoethe = $access
            && $access->language === 'de'
            && strcasecmp($access->exam, 'Goethe') === 0
            && $access->level === 'B1';
        if (! $hasGoethe) {
            return view('content.coming-soon', ['access' => $access]);
        }
        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->gateOrComingSoon($request)) return $redirect;

        $teil = in_array($request->teil, self::TEIL_COLUMNS, true) ? $request->teil : null;

        $topics = GoetheB1LesenTopic::where('is_published', true)
            ->when($teil, fn ($q) => $q->whereNotNull($teil))
            ->orderBy('title')
            ->get();

        return view('goethe-b1.lesen.index', compact('topics', 'teil'));
    }

    public function topic(Request $request, string $slug)
    {
        if ($redirect = $this->gateOrComingSoon($request)) return $redirect;

        $topic = GoetheB1LesenTopic::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $activePart   = in_array($request->teil, self::TEIL_COLUMNS, true) ? $request->teil : null;
        $timerEnabled = $request->boolean('timer');

        return view('goethe-b1.lesen.topic', compact('topic', 'activePart', 'timerEnabled'));
    }

    public function submit(Request $request, string $slug)
    {
        // No persistence yet — Alpine handles grading client-side.
        return redirect()->route('goethe-b1.lesen.topic', $slug);
    }
}
