<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TopicImportService;
use Illuminate\Http\Request;

class TopicImportController extends Controller
{
    public function __construct(private TopicImportService $importer) {}

    public function showImport(string $type)
    {
        // Hören uses its own dedicated import flow (see HoerenImportService + artisan command).
        abort_unless(in_array($type, ['lesen', 'schreiben']), 404);
        return view('admin.import', compact('type'));
    }

    public function handleImport(Request $request, string $type)
    {
        abort_unless(in_array($type, ['lesen', 'schreiben']), 404);

        // Schreiben works as a single bulk import — each entry is one full topic.
        // Lesen still uses the per-Teil column update flow below.
        if ($type === 'schreiben') {
            $request->validate([
                'source'    => 'required|in:json_text,json_file',
                'json_text' => 'required_if:source,json_text|nullable|string',
                'file'      => 'required_if:source,json_file|nullable|file|max:10240',
            ]);

            $json = $request->source === 'json_text'
                ? $request->json_text
                : file_get_contents($request->file('file')->getRealPath());

            $result = $this->importer->importSchreibenFromJson($json);

            session()->flash('import_result', $result);
            return redirect()->route('admin.import.show', $type);
        }

        // Below this point, only Lesen reaches us — keep the per-Teil column flow.
        $allowedParts = ['teil1', 'teil2', 'teil3', 'sprachbausteine1', 'sprachbausteine2'];

        $request->validate([
            'source'    => 'required|in:json_text,json_file,excel',
            'part'      => 'required_unless:source,excel|nullable|in:' . implode(',', $allowedParts),
            'json_text' => 'required_if:source,json_text|nullable|string',
            'file'      => 'required_if:source,json_file,excel|nullable|file|max:10240',
        ]);

        $part = $request->input('part');

        $result = match ($request->source) {
            'json_text' => $this->importer->importPartFromJson($request->json_text, $type, $part),
            'json_file' => $this->importer->importPartFromJson(
                file_get_contents($request->file('file')->getRealPath()), $type, $part
            ),
            'excel'     => $this->importer->importFromExcel($request->file('file'), $type),
        };

        session()->flash('import_result', $result);

        return redirect()->route('admin.import.show', $type);
    }

    public function previewJson(Request $request)
    {
        $request->validate([
            'json' => 'required|string',
            'part' => 'nullable|string',
        ]);

        $data = json_decode($request->json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON: ' . json_last_error_msg()], 422);
        }

        $entries     = $this->importer->extractEntries($data);
        $declaredPart = is_array($data) ? ($data['part'] ?? null) : null;

        $preview = [];
        foreach (array_slice($entries, 0, 5) as $e) {
            $title       = $e['examTitle'] ?? $e['title'] ?? '?';
            $level       = $e['level'] ?? 'B1';
            $part        = $request->input('part');
            $hasContent  = isset($e['content']) || ($part && isset($e[$part]));
            $preview[]   = [
                'title'            => $title,
                'individual_title' => $e['individualTitle'] ?? null,
                'level'            => $level,
                'has_content'      => $hasContent,
            ];
        }

        return response()->json([
            'count'         => count($entries),
            'preview'       => $preview,
            'declared_part' => $declaredPart,
        ]);
    }
}
