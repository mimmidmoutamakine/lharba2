<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $teil  = in_array($request->teil, self::TEIL_COLUMNS, true) ? $request->teil : null;

        $topics = LesenTopic::where('is_published', true)
            ->when($level, fn ($q) => $q->where('level', $level))
            ->when($teil,  fn ($q) => $q->whereNotNull($teil))
            ->orderBy('level')
            ->orderBy('title')
            ->get();

        return view('lesen.index', compact('topics', 'teil'));
    }

    public function topic(Request $request, string $slug)
    {
        $user = $request->user();
        if (! $user->canSeeGermanContent()) {
            return view('content.coming-soon', ['access' => $user->currentAccess()]);
        }

        $topic = LesenTopic::where('slug', $slug)->where('is_published', true)->firstOrFail();
        if (! $user->is_admin && $user->contentLevel() && $topic->level !== $user->contentLevel()) {
            abort(403, 'هاد الموضوع خارج نطاق الوصول ديالك.');
        }
        $activePart   = in_array($request->teil, self::TEIL_COLUMNS, true) ? $request->teil : null;
        $timerEnabled = $request->boolean('timer');
        return view('lesen.topic', compact('topic', 'activePart', 'timerEnabled'));
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
