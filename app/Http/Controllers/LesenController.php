<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LesenTopic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class LesenController extends Controller
{
    private const TEIL_COLUMNS = ['teil1', 'teil2', 'teil3', 'sprachbausteine1', 'sprachbausteine2'];

    private const TEIL_LABELS = [
        'teil1'            => 'Teil 1',
        'teil2'            => 'Teil 2',
        'teil3'            => 'Teil 3',
        'sprachbausteine1' => 'Sprachbausteine 1',
        'sprachbausteine2' => 'Sprachbausteine 2',
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->canSeeGermanContent()) {
            return view('content.coming-soon', ['access' => $user->currentAccess()]);
        }

        $approvedLevel = $user->contentLevel(); // null for admins
        $level = $approvedLevel ?: $request->level;
        // Always require a specific teil. If none specified or invalid, default to
        // 'teil1' — this prevents the heavy "all teils" view from ever loading by
        // accident, which was the failure mode on slow networks / overloaded server.
        // Each page is now guaranteed to render at most ~40 cards (1 per topic).
        $teil  = in_array($request->teil, self::TEIL_COLUMNS, true) ? $request->teil : 'teil1';

        // Index only renders metadata + per-teil availability flags.
        // Skip the heavy JSON columns to avoid loading + casting 5 blobs per row.
        $perPage = 40;
        $topics = LesenTopic::where('is_published', true)
            ->when($level, fn ($q) => $q->where('level', $level))
            ->when($teil,  fn ($q) => $q->whereNotNull($teil))
            ->select([
                'id', 'slug', 'title', 'title_ar', 'level',
                DB::raw('(teil1 IS NOT NULL) AS has_teil1'),
                DB::raw('(teil2 IS NOT NULL) AS has_teil2'),
                DB::raw('(teil3 IS NOT NULL) AS has_teil3'),
                DB::raw('(sprachbausteine1 IS NOT NULL) AS has_sprachbausteine1'),
                DB::raw('(sprachbausteine2 IS NOT NULL) AS has_sprachbausteine2'),
            ])
            ->orderBy('level')
            ->orderBy('title')
            ->paginate($perPage)
            ->withQueryString();

        return view('lesen.index', compact('topics', 'teil'));
    }

    public function topic(Request $request, string $slug)
    {
        $user = $request->user();
        if (! $user->canSeeGermanContent()) {
            return view('content.coming-soon', ['access' => $user->currentAccess()]);
        }

        // Pull metadata + per-teil availability flags only (no JSON yet).
        $hasFlagCols = array_map(
            fn ($c) => DB::raw("({$c} IS NOT NULL) AS has_{$c}"),
            self::TEIL_COLUMNS
        );
        $topic = LesenTopic::where('slug', $slug)->where('is_published', true)
            ->select(array_merge(['id', 'slug', 'title', 'title_ar', 'level'], $hasFlagCols))
            ->firstOrFail();

        if (! $user->is_admin && $user->contentLevel() && $topic->level !== $user->contentLevel()) {
            abort(403, 'هاد الموضوع خارج نطاق الوصول ديالك.');
        }

        // Resolve active part: requested (if exists) → first available teil.
        $requested  = in_array($request->teil, self::TEIL_COLUMNS, true) ? $request->teil : null;
        $activePart = ($requested && $topic->{'has_' . $requested})
            ? $requested
            : collect(self::TEIL_COLUMNS)->first(fn ($t) => (bool) $topic->{'has_' . $t});

        // Load JSON only for the active part — second tiny query by primary key.
        $activePartData = null;
        if ($activePart) {
            $raw = LesenTopic::where('id', $topic->id)->value($activePart);
            $activePartData = is_string($raw) ? json_decode($raw, true) : $raw;
        }

        $timerEnabled = $request->boolean('timer');
        return view('lesen.topic', compact('topic', 'activePart', 'activePartData', 'timerEnabled'));
    }

    public function submit(Request $request, string $slug)
    {
        // TODO: process answers, store results
        return redirect()->route('lesen.result', $slug);
    }

    public function result(string $slug)
    {
        return view('lesen.result', ['slug' => $slug]);
    }

    public function pdf(string $slug, string $teil)
    {
        abort_unless(in_array($teil, self::TEIL_COLUMNS, true), 404);

        $topic = LesenTopic::where('slug', $slug)->where('is_published', true)->firstOrFail();
        abort_unless(!empty($topic->$teil), 404);

        $teilLabel = self::TEIL_LABELS[$teil];
        $content   = $topic->$teil;

        $pdf = Pdf::loadView('lesen.pdf', [
            'topic'     => $topic,
            'teil'      => $teil,
            'teilLabel' => $teilLabel,
            'content'   => $content,
        ])->setPaper('a4');

        $filename = Str::slug($topic->title . '-' . $teilLabel) . '.pdf';
        return $pdf->download($filename);
    }
}
