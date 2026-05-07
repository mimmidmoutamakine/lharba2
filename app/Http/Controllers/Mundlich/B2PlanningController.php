<?php

namespace App\Http\Controllers\Mundlich;

use App\Http\Controllers\Controller;
use App\Models\MundlichB2PlanningStructure;
use App\Models\MundlichB2PlanningTopic;
use Illuminate\Http\Request;

class B2PlanningController extends Controller
{
    private function gateOrComingSoon(Request $request)
    {
        $user = $request->user();
        if ($user->is_admin) return null;
        $access = $user->currentAccess();
        // Telc B2 access: language=de, exam=Telc, level=B2 (case-insensitive on exam).
        $hasTelcB2 = $access
            && $access->language === 'de'
            && strcasecmp($access->exam, 'Telc') === 0
            && $access->level === 'B2';
        if (! $hasTelcB2) {
            return view('content.coming-soon', ['access' => $access]);
        }
        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->gateOrComingSoon($request)) return $redirect;

        $topics         = MundlichB2PlanningTopic::where('is_published', true)
                            ->orderBy('label')
                            ->get();
        $structuresRow  = MundlichB2PlanningStructure::find(1);

        return view('mundlich.b2-planning.index', [
            'topics'         => $topics,
            'hasStructures'  => $structuresRow !== null,
        ]);
    }

    public function structures(Request $request)
    {
        if ($redirect = $this->gateOrComingSoon($request)) return $redirect;

        $row = MundlichB2PlanningStructure::find(1);
        abort_if($row === null, 404, 'Strukturen wurden noch nicht hochgeladen.');

        // Universal training page — only universal + semi_universal aspekte; topic_specific belongs on topic pages.
        $aspekte = collect($row->aspekte())
            ->filter(fn ($a) => in_array(($a['category'] ?? 'universal'), ['universal', 'semi_universal'], true))
            ->values()
            ->all();

        return view('mundlich.b2-planning.structures', [
            'aspekte'             => $aspekte,
            'conversationFlow'    => $row->conversationFlow(),
            'summaryFormula'      => $row->summaryFormula(),
            'metadata'            => $row->metadata(),
        ]);
    }

    public function topic(Request $request, string $slug)
    {
        if ($redirect = $this->gateOrComingSoon($request)) return $redirect;

        $topic = MundlichB2PlanningTopic::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $row = MundlichB2PlanningStructure::find(1);
        $allAspekte      = $row?->aspekte() ?? [];
        $relevantAspekte = $topic->relevantAspekte($allAspekte);

        return view('mundlich.b2-planning.topic', [
            'topic'           => $topic,
            'aspekte'         => $relevantAspekte,
            'summaryFormula'  => $row?->summaryFormula() ?? [],
        ]);
    }
}
