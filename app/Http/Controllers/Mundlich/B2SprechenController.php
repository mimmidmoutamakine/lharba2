<?php

namespace App\Http\Controllers\Mundlich;

use App\Http\Controllers\Controller;
use App\Models\MundlichB2SprechenCluster;
use App\Models\MundlichB2SprechenTopic;
use App\Models\MundlichB2SprechenUniversal;
use Illuminate\Http\Request;

/**
 * Telc B2 Mündlich Teil 2 (Präsentation) — 3-layer "Baukasten" preparation:
 *   /mundlich/b2-sprechen                 → topic list, filtered by cluster (Lesen pattern)
 *   /mundlich/b2-sprechen/universal       → universal toolkit (العُدّة الشاملة)
 *   /mundlich/b2-sprechen/{slug}          → one topic (الموضوع)
 *
 * Clusters are a FILTER dimension over the topic list (like Lesen's Teil filter),
 * not standalone pages. Gated on Telc B2 access (mirrors the Planen module).
 */
class B2SprechenController extends Controller
{
    private function gateOrComingSoon(Request $request)
    {
        $user = $request->user();
        if ($user->is_admin) return null;
        $access = $user->currentAccess();
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

        $clusters = MundlichB2SprechenCluster::where('is_published', true)->orderBy('position')->get();

        // Cluster acts as a filter over the topic list (Lesen-style). ?cluster=key
        $activeCluster = $request->query('cluster')
            ? $clusters->firstWhere('cluster_key', $request->query('cluster'))
            : null;

        $topics = MundlichB2SprechenTopic::where('is_published', true)
            ->when($activeCluster, fn ($q) => $q->whereIn('order', $activeCluster->topic_orders ?: [-1]))
            ->orderBy('order')
            ->get();

        $universal = MundlichB2SprechenUniversal::find(1);

        return view('mundlich.b2-sprechen.index', [
            'clusters'           => $clusters,
            'activeCluster'      => $activeCluster,
            'topics'             => $topics,
            'hasUniversal'       => $universal !== null && ! empty($universal->argumentCategories()),
            'universalArguments' => ($activeCluster && $universal) ? $activeCluster->universalArguments($universal) : [],
        ]);
    }

    public function universal(Request $request)
    {
        if ($redirect = $this->gateOrComingSoon($request)) return $redirect;

        $row = MundlichB2SprechenUniversal::find(1);
        abort_if($row === null || empty($row->argumentCategories()), 404, 'Der Universal-Baukasten wurde noch nicht hochgeladen.');

        return view('mundlich.b2-sprechen.universal', [
            'meta'         => $row->meta(),
            'categories'   => $row->argumentCategories(),
            'structures'   => $row->presentationStructures(),
            'emergency'    => $row->emergencyBlocks(),
        ]);
    }

    public function topic(Request $request, string $slug)
    {
        if ($redirect = $this->gateOrComingSoon($request)) return $redirect;

        $topic = MundlichB2SprechenTopic::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return view('mundlich.b2-sprechen.topic', [
            'topic'    => $topic,
            'clusters' => $topic->clusters(),
        ]);
    }
}
