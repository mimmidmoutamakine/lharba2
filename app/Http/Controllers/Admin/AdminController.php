<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LesenTopic;
use App\Models\HoerenTopic;
use App\Models\GoetheB1LesenTopic;
use App\Models\MundlichB2PlanningStructure;
use App\Models\MundlichB2PlanningTopic;
use App\Services\GoetheB1LesenImportService;
use App\Services\MundlichB2PlanningImportService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'lesenCount'        => LesenTopic::count(),
            'hoerenCount'       => HoerenTopic::count(),
            'goetheB1LesenCount'=> GoetheB1LesenTopic::count(),
            'lesenRecent'       => LesenTopic::latest()->take(5)->get(),
            'hoerenRecent'      => HoerenTopic::latest()->take(5)->get(),
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

    public function hoerenIndex()
    {
        return view('admin.hoeren.index', [
            'topics' => HoerenTopic::latest()->paginate(20),
        ]);
    }

    public function hoerenDestroy(HoerenTopic $topic)
    {
        $topic->delete();
        return back()->with('success', 'Topic deleted.');
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
