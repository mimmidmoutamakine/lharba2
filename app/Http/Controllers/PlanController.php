<?php

namespace App\Http\Controllers;

use App\Models\HoerenTopic;
use App\Models\LesenTopic;
use App\Models\SchreibenTopic;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->canSeeGermanContent()) {
            return view('content.coming-soon', ['access' => $user->currentAccess()]);
        }

        $approvedLevel = $user->contentLevel(); // null for admins
        $level = $approvedLevel ?: (in_array($request->level, ['B1', 'B2'], true) ? $request->level : null);

        // Lesen — one card per (topic × teil that has data)
        $teilFullNames = [
            'teil1'            => 'Teil 1',
            'teil2'            => 'Teil 2',
            'teil3'            => 'Teil 3',
            'sprachbausteine1' => 'Sprachbausteine 1',
            'sprachbausteine2' => 'Sprachbausteine 2',
        ];

        $lesenTopics = LesenTopic::where('is_published', true)
            ->when($level, fn ($q) => $q->where('level', $level))
            ->get();

        // One bucket per Teil, so the plan page can show separate cards.
        $lesenBuckets = array_fill_keys(array_keys($teilFullNames), []);
        foreach ($lesenTopics as $t) {
            foreach ($teilFullNames as $tk => $tname) {
                if (empty($t->$tk)) continue;
                $lesenBuckets[$tk][] = [
                    'key'   => $t->id . '-' . $tk,
                    'title' => $t->title,
                    'sub'   => $tname,
                    'level' => $t->level,
                    'href'  => route('lesen.topic', ['slug' => $t->slug, 'teil' => $tk]),
                ];
            }
        }

        $hoerenItems = HoerenTopic::where('is_published', true)
            ->when($level, fn ($q) => $q->where('level', $level))
            ->orderBy('teil')->orderBy('title')
            ->get()
            ->map(fn ($t) => [
                'key'   => (string) $t->id,
                'title' => $t->title,
                'sub'   => 'Teil ' . $t->teil,
                'level' => $t->level,
                'href'  => route('hoeren.topic', $t->slug),
            ])->all();

        $schreibenItems = SchreibenTopic::where('is_published', true)
            ->when($level, fn ($q) => $q->where('level', $level))
            ->orderBy('level')->orderBy('title')
            ->get()
            ->map(fn ($t) => [
                'key'   => (string) $t->id,
                'title' => $t->title,
                'sub'   => $t->type ?? 'Brief',
                'level' => $t->level,
                'href'  => route('schreiben.topic', $t->slug),
            ])->all();

        // Display buckets — each renders as its own card on the plan page.
        // `storage` is the localStorage namespace (lesen / hoeren / schreiben);
        // multiple buckets can share the same storage namespace (Lesen has 5).
        $buckets = [
            'lesen_teil1' => ['label_de' => 'Lesen Teil 1',         'label_ar' => 'القراءة · 1', 'tone' => 'amber',   'icon' => 'book',       'storage' => 'lesen',     'href' => route('lesen.index', ['teil' => 'teil1']),            'items' => $lesenBuckets['teil1']],
            'lesen_teil2' => ['label_de' => 'Lesen Teil 2',         'label_ar' => 'القراءة · 2', 'tone' => 'amber',   'icon' => 'book',       'storage' => 'lesen',     'href' => route('lesen.index', ['teil' => 'teil2']),            'items' => $lesenBuckets['teil2']],
            'lesen_teil3' => ['label_de' => 'Lesen Teil 3',         'label_ar' => 'القراءة · 3', 'tone' => 'amber',   'icon' => 'book',       'storage' => 'lesen',     'href' => route('lesen.index', ['teil' => 'teil3']),            'items' => $lesenBuckets['teil3']],
            'lesen_sb1'   => ['label_de' => 'Sprachbausteine 1',    'label_ar' => 'البنية · 1',  'tone' => 'amber',   'icon' => 'puzzle',     'storage' => 'lesen',     'href' => route('lesen.index', ['teil' => 'sprachbausteine1']), 'items' => $lesenBuckets['sprachbausteine1']],
            'lesen_sb2'   => ['label_de' => 'Sprachbausteine 2',    'label_ar' => 'البنية · 2',  'tone' => 'amber',   'icon' => 'puzzle',     'storage' => 'lesen',     'href' => route('lesen.index', ['teil' => 'sprachbausteine2']), 'items' => $lesenBuckets['sprachbausteine2']],
            'hoeren'      => ['label_de' => 'Hören',                'label_ar' => 'الاستماع',    'tone' => 'orange',  'icon' => 'headphones', 'storage' => 'hoeren',    'href' => route('hoeren.index'),                                'items' => $hoerenItems],
            'schreiben'   => ['label_de' => 'Schreiben',            'label_ar' => 'الكتابة',     'tone' => 'emerald', 'icon' => 'pencil',     'storage' => 'schreiben', 'href' => route('schreiben.index'),                             'items' => $schreibenItems],
        ];

        return view('plan.index', compact('level', 'buckets'));
    }
}
