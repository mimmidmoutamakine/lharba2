<?php

namespace App\Http\Controllers;

use App\Models\HoerenExam;
use App\Models\LesenTopic;
use App\Models\SchreibenTopic;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->canSeeGermanContent()) {
            return view('content.coming-soon', ['access' => $user->currentAccess()]);
        }

        [$level, $buckets] = $this->resolveLevelAndBuckets($request);

        return view('plan.index', compact('level', 'buckets'));
    }

    /**
     * Downloadable A4 checklist of the user's full study plan, distributed
     * across N weeks. Each bucket's items are chunked sequentially across
     * weeks so related items stay together.
     */
    public function pdf(Request $request)
    {
        $user = $request->user();
        if (! $user->canSeeGermanContent()) {
            abort(403);
        }

        $weeks = (int) $request->query('weeks', 4);
        if (! in_array($weeks, [2, 4, 6, 8], true)) $weeks = 4;

        [$level, $buckets] = $this->resolveLevelAndBuckets($request);

        // Compute weekly schedule: [1 => [bucketKey => items[]], 2 => [...], ...]
        $schedule = $this->buildSchedule($buckets, $weeks);

        // Summary totals for the header.
        $totalItems  = array_sum(array_map(fn ($b) => count($b['items']), $buckets));
        $perWeekAvg  = $weeks > 0 ? (int) ceil($totalItems / $weeks) : 0;
        $perDayAvg   = $weeks > 0 ? (int) ceil($totalItems / ($weeks * 7)) : 0;

        // Use mPDF — DomPDF can't shape Arabic (no letter joining / BiDi),
        // which made the Arabic labels and Darija text unreadable.
        $html = view('plan.pdf', [
            'level'       => $level,
            'weeks'       => $weeks,
            'buckets'     => $buckets,
            'schedule'    => $schedule,
            'totalItems'  => $totalItems,
            'perWeekAvg'  => $perWeekAvg,
            'perDayAvg'   => $perDayAvg,
            'userName'    => $user->name ?? '',
        ])->render();

        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'margin_top'        => 16,
            'margin_bottom'     => 16,
            'margin_left'       => 14,
            'margin_right'      => 14,
            'tempDir'           => storage_path('app/mpdf'),
            'default_font'      => 'dejavusans',
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
        ]);

        // The plan PDF is RTL-first (Arabic UI, with German titles inline). Tell
        // mPDF the document direction up-front; per-element dir attrs override locally.
        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        $filename = Str::slug('khouttati-' . ($level ?: 'all') . '-' . $weeks . 'w-' . now()->format('Y-m-d')) . '.pdf';
        $pdfBytes = $mpdf->Output('', 'S');

        return response($pdfBytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length'      => strlen($pdfBytes),
        ]);
    }

    // ── Shared internals ─────────────────────────────────────────────

    /** Returns [$level, $buckets] — same shape index() used to compute inline. */
    private function resolveLevelAndBuckets(Request $request): array
    {
        $user = $request->user();
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

        // Hören — one bucket per Teil (so the plan page shows separate progress
        // for Teil 1 / 2 / 3, mirroring the Lesen layout). Join through module
        // to filter by user's level. Status keys MUST match the imtihanat cards:
        // "t{teil}-{slug}" — see resources/views/hoeren/imtihanat.blade.php.
        $hoerenRows = HoerenExam::query()
            ->select(
                'hoeren_exams.slug',
                'hoeren_exams.title',
                'hoeren_modules.teil',
                'hoeren_modules.level',
            )
            ->join('hoeren_modules', 'hoeren_modules.id', '=', 'hoeren_exams.module_id')
            ->where('hoeren_exams.is_published', true)
            ->where('hoeren_modules.is_published', true)
            ->when($level, fn ($q) => $q->where('hoeren_modules.level', $level))
            ->orderBy('hoeren_modules.teil')
            ->orderBy('hoeren_exams.position')
            ->get();

        $hoerenBuckets = [1 => [], 2 => [], 3 => []];
        foreach ($hoerenRows as $r) {
            $hoerenBuckets[$r->teil][] = [
                'key'   => 't' . $r->teil . '-' . $r->slug,
                'title' => $r->title,
                'sub'   => 'Teil ' . $r->teil,
                'level' => $r->level,
                'href'  => route('hoeren.exam', ['teil' => 'teil' . $r->teil, 'exam' => $r->slug]),
            ];
        }

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
            'hoeren_teil1'=> ['label_de' => 'Hören Teil 1',         'label_ar' => 'الاستماع · 1','tone' => 'orange',  'icon' => 'headphones', 'storage' => 'hoeren',    'href' => route('hoeren.imtihanat', ['teil' => 'teil1']),       'items' => $hoerenBuckets[1]],
            'hoeren_teil2'=> ['label_de' => 'Hören Teil 2',         'label_ar' => 'الاستماع · 2','tone' => 'orange',  'icon' => 'headphones', 'storage' => 'hoeren',    'href' => route('hoeren.imtihanat', ['teil' => 'teil2']),       'items' => $hoerenBuckets[2]],
            'hoeren_teil3'=> ['label_de' => 'Hören Teil 3',         'label_ar' => 'الاستماع · 3','tone' => 'orange',  'icon' => 'headphones', 'storage' => 'hoeren',    'href' => route('hoeren.imtihanat', ['teil' => 'teil3']),       'items' => $hoerenBuckets[3]],
            'schreiben'   => ['label_de' => 'Schreiben',            'label_ar' => 'الكتابة',     'tone' => 'emerald', 'icon' => 'pencil',     'storage' => 'schreiben', 'href' => route('schreiben.index'),                             'items' => $schreibenItems],
        ];

        return [$level, $buckets];
    }

    /**
     * Distribute each bucket's items across N weeks.
     * Chunks sequentially (week 1 = first N items, week 2 = next N, ...) so
     * related items stay together pedagogically.
     *
     * @return array<int, array<string, array<int, array>>>  [weekNum => [bucketKey => items]]
     */
    private function buildSchedule(array $buckets, int $weeks): array
    {
        $schedule = array_fill(1, $weeks, []);

        foreach ($buckets as $key => $bucket) {
            $items = $bucket['items'];
            if (empty($items)) continue;

            // ceil so any leftover lands in the last week, not an empty week.
            $chunkSize = max(1, (int) ceil(count($items) / $weeks));
            $chunks = array_chunk($items, $chunkSize);

            foreach ($chunks as $weekIdx => $chunk) {
                $weekNum = $weekIdx + 1;
                if ($weekNum > $weeks) {
                    // Overflow chunk — append to the last week.
                    $schedule[$weeks][$key] = array_merge($schedule[$weeks][$key] ?? [], $chunk);
                } else {
                    $schedule[$weekNum][$key] = $chunk;
                }
            }
        }

        return $schedule;
    }
}
