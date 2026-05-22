<?php

namespace App\Http\Controllers\GoetheB1;

use App\Http\Controllers\Controller;
use App\Models\GoetheB1LesenTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Always require a specific teil. Default to 'teil1' if missing/invalid —
        // prevents the heavy "all teils" view from loading. Each page caps at ~40
        // cards (1 per topic), keeping iOS Safari happy under burst load.
        $teil = in_array($request->teil, self::TEIL_COLUMNS, true) ? $request->teil : 'teil1';

        // Index only renders metadata + per-teil availability flags.
        // Skip the heavy JSON columns to avoid loading + casting 5 blobs per row.
        $perPage = 40;
        $topics = GoetheB1LesenTopic::where('is_published', true)
            ->when($teil, fn ($q) => $q->whereNotNull($teil))
            ->select([
                'id', 'slug', 'title', 'title_ar',
                DB::raw('(teil1 IS NOT NULL) AS has_teil1'),
                DB::raw('(teil2 IS NOT NULL) AS has_teil2'),
                DB::raw('(teil3 IS NOT NULL) AS has_teil3'),
                DB::raw('(teil4 IS NOT NULL) AS has_teil4'),
                DB::raw('(teil5 IS NOT NULL) AS has_teil5'),
            ])
            ->orderBy('title')
            ->paginate($perPage)
            ->withQueryString();

        return view('goethe-b1.lesen.index', compact('topics', 'teil'));
    }

    public function topic(Request $request, string $slug)
    {
        if ($redirect = $this->gateOrComingSoon($request)) return $redirect;

        // Pull metadata + per-teil availability flags only (no JSON yet).
        $hasFlagCols = array_map(
            fn ($c) => DB::raw("({$c} IS NOT NULL) AS has_{$c}"),
            self::TEIL_COLUMNS
        );
        $topic = GoetheB1LesenTopic::where('slug', $slug)
            ->where('is_published', true)
            ->select(array_merge(['id', 'slug', 'title', 'title_ar'], $hasFlagCols))
            ->firstOrFail();

        // Resolve active part: requested (if exists) → first available teil.
        $requested  = in_array($request->teil, self::TEIL_COLUMNS, true) ? $request->teil : null;
        $activePart = ($requested && $topic->{'has_' . $requested})
            ? $requested
            : collect(self::TEIL_COLUMNS)->first(fn ($t) => (bool) $topic->{'has_' . $t});

        // Load JSON only for the active part — second tiny query by primary key.
        $activePartData = null;
        if ($activePart) {
            $raw = GoetheB1LesenTopic::where('id', $topic->id)->value($activePart);
            $activePartData = is_string($raw) ? json_decode($raw, true) : $raw;
        }

        $timerEnabled = $request->boolean('timer');

        return view('goethe-b1.lesen.topic', compact('topic', 'activePart', 'activePartData', 'timerEnabled'));
    }

    public function submit(Request $request, string $slug)
    {
        // No persistence yet — Alpine handles grading client-side.
        return redirect()->route('goethe-b1.lesen.topic', $slug);
    }
}
