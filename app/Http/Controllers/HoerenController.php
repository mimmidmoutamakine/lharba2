<?php

namespace App\Http\Controllers;

use App\Models\HoerenExam;
use App\Models\HoerenExamStatement;
use App\Models\HoerenModule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

/**
 * Hören (Listening) — user-facing.
 *
 *   /hoeren                       → redirect to teil1 imtihanat
 *   /hoeren/{teil}/learn          → memorization page (codes + mnemonics)
 *   /hoeren/{teil}/imtihanat      → exam cards
 *   /hoeren/{teil}/exam/{slug}    → single exam, R/F submission, audio if available
 *   /hoeren/{teil}/pdf            → PDF (Richtig-only) for that teil + user's level
 *   /hoeren/pdf-all               → PDF (Richtig-only) for all 3 teils + user's level
 *
 * Level is locked to the user's `currentAccess()` level. Admins see B2 by default
 * (can switch via ?level=B1).
 */
class HoerenController extends Controller
{
    private const TEIL_MAP = ['teil1' => 1, 'teil2' => 2, 'teil3' => 3];

    public function index(Request $request)
    {
        if ($gate = $this->gate($request)) return $gate;
        return redirect()->route('hoeren.imtihanat', ['teil' => 'teil1']);
    }

    public function learn(Request $request, string $teil)
    {
        if ($gate = $this->gate($request)) return $gate;
        $teilNum = self::TEIL_MAP[$teil];
        $level   = $this->resolveLevel($request);

        $module = HoerenModule::forLevelTeil($level, $teilNum);
        $codes  = $module ? $module->codes : collect();

        return view('hoeren.learn', [
            'teilKey'   => $teil,
            'teilNum'   => $teilNum,
            'level'     => $level,
            'module'    => $module,
            'codes'     => $codes,
            'section'   => 'learn',
        ]);
    }

    public function imtihanat(Request $request, string $teil)
    {
        if ($gate = $this->gate($request)) return $gate;
        $teilNum = self::TEIL_MAP[$teil];
        $level   = $this->resolveLevel($request);

        $module = HoerenModule::forLevelTeil($level, $teilNum);

        // Provenance filter (standard / Türkei). Which groups actually exist in this
        // module decides whether the filter UI shows at all. Order: standard, turkey, other.
        $availableCats = [];
        if ($module) {
            $present = $module->exams()->where('is_published', true)
                ->pluck('update_category')
                ->map(fn ($c) => HoerenExam::groupFor($c))
                ->filter()
                ->unique()
                ->all();
            $availableCats = array_values(array_filter(
                HoerenExam::CATEGORY_GROUPS,
                fn ($g) => in_array($g, $present, true)
            ));
        }
        $activeCat = in_array($request->query('cat'), $availableCats, true)
            ? $request->query('cat') : null;

        // Per-exam: count of statements + whether audio exists + optional admin tag.
        $exams = $module
            ? $module->exams()
                ->where('is_published', true)
                ->when($activeCat, fn ($q) => $q->inCategoryGroup($activeCat))
                ->with('topicTag')
                ->withCount('statements')
                ->orderBy('position')
                ->paginate(40)
                ->withQueryString()
            : collect();

        return view('hoeren.imtihanat', [
            'teilKey'       => $teil,
            'teilNum'       => $teilNum,
            'level'         => $level,
            'module'        => $module,
            'exams'         => $exams,
            'availableCats' => $availableCats,
            'activeCat'     => $activeCat,
            'section'       => 'imtihanat',
        ]);
    }

    public function exam(Request $request, string $teil, HoerenExam $exam)
    {
        if ($gate = $this->gate($request)) return $gate;
        $teilNum = self::TEIL_MAP[$teil];

        // Defensive: the {exam:slug} binding doesn't enforce the teil — make sure
        // the exam actually belongs to the requested (level, teil).
        $level  = $this->resolveLevel($request);
        $module = $exam->module;
        if (! $module || $module->level !== $level || $module->teil !== $teilNum || ! $exam->is_published) {
            abort(404);
        }

        // Statements: load ordered, then we'll let JS shuffle unless the user
        // has toggled random off.
        $statements = $exam->statements()->get(['id', 'text', 'answer', 'highlights', 'explanation_highlights', 'position']);

        // Sibling exams for the prev/next/dropdown navigation. Only metadata.
        $siblings = $module->exams()
            ->where('is_published', true)
            ->orderBy('position')
            ->get(['id', 'slug', 'title', 'position']);

        // Resolve prev / next within siblings.
        $idx = $siblings->search(fn ($e) => $e->id === $exam->id);
        $prev = $idx !== false && $idx > 0                       ? $siblings[$idx - 1] : null;
        $next = $idx !== false && $idx < $siblings->count() - 1  ? $siblings[$idx + 1] : null;

        return view('hoeren.exam', [
            'teilKey'    => $teil,
            'teilNum'    => $teilNum,
            'level'      => $level,
            'module'     => $module,
            'exam'       => $exam,
            'statements' => $statements,
            'siblings'   => $siblings,
            'prev'       => $prev,
            'next'       => $next,
            'audioUrl'   => $exam->audioUrl(),
        ]);
    }

    public function pdf(Request $request, string $teil)
    {
        if ($gate = $this->gate($request)) return $gate;
        $teilNum = self::TEIL_MAP[$teil];
        $level   = $this->resolveLevel($request);

        $module = HoerenModule::forLevelTeil($level, $teilNum);
        if (! $module) abort(404);

        $sections = [$this->buildPdfSection($module)];

        return $this->renderPdf("hoeren-{$level}-teil{$teilNum}-richtig", $level, $sections);
    }

    public function pdfAll(Request $request)
    {
        if ($gate = $this->gate($request)) return $gate;
        $level = $this->resolveLevel($request);

        $modules = HoerenModule::where('level', $level)
            ->where('is_published', true)
            ->orderBy('teil')
            ->get();

        $sections = $modules->map(fn ($m) => $this->buildPdfSection($m))->all();

        return $this->renderPdf("hoeren-{$level}-all-richtig", $level, $sections);
    }

    // ── helpers ───────────────────────────────────────────────────────

    private function gate(Request $request)
    {
        $user = $request->user();
        if (! $user) return redirect()->route('login');
        if (! $user->canSeeGermanContent()) {
            return view('content.coming-soon', ['access' => $user->currentAccess()]);
        }
        return null;
    }

    private function resolveLevel(Request $request): string
    {
        $user = $request->user();
        // Approved level wins for regular users; admins default to B2 but can
        // override via ?level=B1.
        $approved = $user->contentLevel();
        if ($approved) return $approved;

        $q = strtoupper((string) $request->query('level', 'B2'));
        return in_array($q, ['B1', 'B2'], true) ? $q : 'B2';
    }

    /**
     * Build one PDF section payload for a module (header + Richtig statements
     * grouped by exam title).
     */
    private function buildPdfSection(HoerenModule $module): array
    {
        // Single query: all Richtig statements for this module, with their exam title.
        // The (exam_id, answer) index makes this fast.
        $rows = HoerenExamStatement::query()
            ->select('hoeren_exam_statements.text', 'hoeren_exams.title as exam_title', 'hoeren_exams.position as exam_pos', 'hoeren_exam_statements.position as stmt_pos')
            ->join('hoeren_exams', 'hoeren_exams.id', '=', 'hoeren_exam_statements.exam_id')
            ->where('hoeren_exams.module_id', $module->id)
            ->where('hoeren_exams.is_published', true)
            ->where('hoeren_exam_statements.answer', HoerenExamStatement::ANSWER_RICHTIG)
            ->orderBy('hoeren_exams.position')
            ->orderBy('hoeren_exam_statements.position')
            ->get();

        // Group by exam title in PHP (preserves order from the SELECT).
        $groups = [];
        foreach ($rows as $r) {
            $groups[$r->exam_title][] = $r->text;
        }

        return [
            'teil'     => $module->teil,
            'subtitle' => $module->subtitle,
            'groups'   => $groups,
        ];
    }

    private function renderPdf(string $filenameBase, string $level, array $sections)
    {
        // Use mPDF — DomPDF can't shape Arabic letters (no joining/BiDi).
        // mPDF handles RTL + Arabic glyph joining + mixed AR/DE/digits natively.
        $html = view('hoeren.pdf', [
            'level'    => $level,
            'sections' => $sections,
        ])->render();

        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'margin_top'        => 16,
            'margin_bottom'     => 16,
            'margin_left'       => 14,
            'margin_right'      => 14,
            // Cache compiled fonts/styles here so subsequent generations are fast.
            'tempDir'           => storage_path('app/mpdf'),
            // mPDF picks the right Arabic font automatically when dir="rtl" is on
            // the <body>/<html>. We set DejaVu Sans as the latin default so German
            // diacritics render too.
            'default_font'      => 'dejavusans',
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
        ]);

        $mpdf->WriteHTML($html);

        $filename = Str::slug($filenameBase) . '.pdf';
        // 'S' returns the PDF as a string (vs 'D' which echoes + breaks Laravel responses).
        $pdfBytes = $mpdf->Output('', 'S');

        return response($pdfBytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Length'      => strlen($pdfBytes),
        ]);
    }
}
