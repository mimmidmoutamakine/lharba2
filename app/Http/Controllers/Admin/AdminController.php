<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LesenTopic;
use App\Models\HoerenModule;
use App\Models\HoerenExam;
use App\Models\GoetheB1LesenTopic;
use App\Models\MundlichB2PlanningStructure;
use App\Models\MundlichB2PlanningTopic;
use App\Services\GoetheB1LesenImportService;
use App\Services\HoerenImportService;
use App\Services\MundlichB2PlanningImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'lesenCount'        => LesenTopic::count(),
            'hoerenCount'       => HoerenModule::count(),
            'goetheB1LesenCount'=> GoetheB1LesenTopic::count(),
            'lesenRecent'       => LesenTopic::latest()->take(5)->get(),
            'hoerenRecent'      => HoerenModule::latest()->take(5)->get(),
        ]);
    }

    public function goetheB1LesenIndex()
    {
        return view('admin.goethe-b1.lesen.index', [
            'topics' => GoetheB1LesenTopic::latest()->paginate(20),
        ]);
    }

    public function goetheB1LesenDestroy(GoetheB1LesenTopic $topic)
    {
        $topic->delete();
        return back()->with('success', 'Topic deleted.');
    }

    public function goetheB1LesenToggle(GoetheB1LesenTopic $topic)
    {
        $topic->update(['is_published' => ! $topic->is_published]);
        return back()->with('success', 'Status updated.');
    }

    public function goetheB1LesenImportShow()
    {
        return view('admin.goethe-b1.lesen.import');
    }

    public function goetheB1LesenImportHandle(Request $request, GoetheB1LesenImportService $importer)
    {
        $request->validate([
            'teil'      => 'required|in:teil1,teil2,teil3,teil4,teil5',
            'source'    => 'required|in:json_text,json_file',
            'json_text' => 'required_if:source,json_text|nullable|string',
            'file'      => 'required_if:source,json_file|nullable|file|max:10240',
        ]);

        $json = $request->source === 'json_text'
            ? $request->input('json_text')
            : file_get_contents($request->file('file')->getRealPath());

        $result = $importer->importTeilFromJson($json, $request->input('teil'));

        session()->flash('import_result', $result);
        session()->flash('import_teil', $request->input('teil'));
        return redirect()->route('admin.goethe-b1.lesen.import.show');
    }

    public function goetheB1LesenImportPreview(Request $request, GoetheB1LesenImportService $importer)
    {
        $request->validate(['json' => 'required|string']);
        $preview = $importer->preview($request->input('json'));
        return response()->json($preview, isset($preview['error']) ? 422 : 200);
    }

    public function lesenIndex()
    {
        return view('admin.lesen.index', [
            'topics' => LesenTopic::latest()->paginate(20),
        ]);
    }

    public function lesenDestroy(LesenTopic $topic)
    {
        $topic->delete();
        return back()->with('success', 'Topic deleted.');
    }

    public function lesenToggle(LesenTopic $topic)
    {
        $topic->update(['is_published' => !$topic->is_published]);
        return back()->with('success', 'Status updated.');
    }

    // ── Hören admin ───────────────────────────────────────────────────

    public function hoerenIndex()
    {
        // One row per (level, teil) module, with counts. Lightweight — no JSON columns.
        $modules = HoerenModule::orderBy('level')->orderBy('teil')
            ->withCount(['codes', 'exams'])
            ->get();

        // Per-module: exams (with audio status) for the inline upload UI.
        $modules->load(['exams' => function ($q) {
            $q->orderBy('position');
        }]);

        return view('admin.hoeren.index', compact('modules'));
    }

    public function hoerenImportShow()
    {
        return view('admin.hoeren.import');
    }

    public function hoerenImportHandle(Request $request, HoerenImportService $importer)
    {
        $request->validate([
            'source'    => 'required|in:json_text,json_file',
            'json_text' => 'required_if:source,json_text|nullable|string',
            'file'      => 'required_if:source,json_file|nullable|file|max:20480', // 20 MB
        ]);

        $json = $request->source === 'json_text'
            ? (string) $request->input('json_text')
            : (string) file_get_contents($request->file('file')->getRealPath());

        $result = $importer->import($json);

        session()->flash('import_result', $result);
        return redirect()->route('admin.hoeren.import.show');
    }

    public function hoerenExamAudioUpload(Request $request, HoerenExam $exam)
    {
        $request->validate([
            // Common audio formats; cap at 25 MB per file.
            'audio' => 'required|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/x-m4a,audio/mp4,audio/aac|max:25600',
        ]);

        // Store under storage/app/public/hoeren-audio/{module}/{exam-slug}.{ext}
        // The slug-based filename means re-importing the same exam doesn't break the link.
        $module = $exam->module;
        $ext  = $request->file('audio')->getClientOriginalExtension() ?: 'mp3';
        $name = "{$exam->slug}." . strtolower($ext);
        $dir  = "hoeren-audio/{$module->level}-teil{$module->teil}";
        $path = $request->file('audio')->storeAs($dir, $name, 'public');

        // Delete the previous file if it's a different path (e.g., different ext).
        if ($exam->audio_path && $exam->audio_path !== $path && Storage::disk('public')->exists($exam->audio_path)) {
            Storage::disk('public')->delete($exam->audio_path);
        }

        $exam->update(['audio_path' => $path]);

        return back()->with('ok', "تم رفع الصوت ديال « {$exam->title} ».");
    }

    public function hoerenExamAudioDelete(HoerenExam $exam)
    {
        if ($exam->audio_path && Storage::disk('public')->exists($exam->audio_path)) {
            Storage::disk('public')->delete($exam->audio_path);
        }
        $exam->update(['audio_path' => null]);

        return back()->with('ok', 'تم حذف الملف الصوتي.');
    }

    // ── Telc B2 Mündlich Teil 3 — Gemeinsam etwas planen ──────────────

    public function mundlichB2PlanningIndex()
    {
        return view('admin.mundlich.b2-planning.index', [
            'topics'         => MundlichB2PlanningTopic::orderBy('label')->paginate(40),
            'structuresRow'  => MundlichB2PlanningStructure::find(1),
        ]);
    }

    public function mundlichB2PlanningDestroy(MundlichB2PlanningTopic $topic)
    {
        $topic->delete();
        return back()->with('success', 'Topic deleted.');
    }

    public function mundlichB2PlanningToggle(MundlichB2PlanningTopic $topic)
    {
        $topic->update(['is_published' => ! $topic->is_published]);
        return back()->with('success', 'Status updated.');
    }

    public function mundlichB2PlanningImportShow()
    {
        return view('admin.mundlich.b2-planning.import');
    }

    public function mundlichB2PlanningImportHandle(Request $request, MundlichB2PlanningImportService $importer)
    {
        $request->validate([
            'kind'      => 'required|in:structures,topics',
            'source'    => 'required|in:json_text,json_file',
            'json_text' => 'required_if:source,json_text|nullable|string',
            'file'      => 'required_if:source,json_file|nullable|file|max:10240',
        ]);

        $json = $request->source === 'json_text'
            ? $request->input('json_text')
            : file_get_contents($request->file('file')->getRealPath());

        $result = $request->input('kind') === 'structures'
            ? $importer->importStructures($json)
            : $importer->importTopics($json);

        session()->flash('import_result', $result);
        session()->flash('import_kind', $request->input('kind'));
        return redirect()->route('admin.mundlich.b2-planning.import.show');
    }

    public function mundlichB2PlanningImportPreview(Request $request, MundlichB2PlanningImportService $importer)
    {
        $request->validate([
            'json' => 'required|string',
            'kind' => 'required|in:structures,topics',
        ]);
        $preview = $importer->preview($request->input('json'), $request->input('kind'));
        return response()->json($preview, isset($preview['error']) ? 422 : 200);
    }
}
