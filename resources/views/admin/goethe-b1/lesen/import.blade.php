@extends('admin.layout')
@section('title', 'استيراد – Goethe B1 Lesen')
@section('page-title', 'استيراد مواضيع Goethe B1 — Lesen')

@php
    $teilOptions = [
        'teil1' => 'Teil 1 · Richtig/Falsch',
        'teil2' => 'Teil 2 · 2 Texte + MC',
        'teil3' => 'Teil 3 · Zuordnung',
        'teil4' => 'Teil 4 · Dafür/Dagegen',
        'teil5' => 'Teil 5 · MC',
    ];
    $defaultTeil = session('import_teil', 'teil1');
@endphp

@section('content')

{{-- Validation errors --}}
@if($errors->any())
<div class="mb-6 p-5 rounded-2xl border bg-red-500/10 border-red-500/20">
    <div class="flex items-center gap-3 mb-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-400"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span class="font-bold text-red-400">خطأ في التحقق</span>
    </div>
    <ul class="text-sm text-red-300 space-y-1 list-disc list-inside">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Import Result --}}
@if(session('import_result'))
@php $r = session('import_result'); @endphp
<div class="mb-6 p-5 rounded-2xl border {{ $r['imported'] > 0 ? 'bg-green-500/10 border-green-500/20' : 'bg-red-500/10 border-red-500/20' }}">
    <div class="flex items-center gap-3 mb-2">
        @if($r['imported'] > 0)
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-400"><path d="M20 6 9 17l-5-5"/></svg>
        <span class="font-bold text-green-400">تم الاستيراد بنجاح</span>
        @else
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-400"><circle cx="12" cy="12" r="10"/></svg>
        <span class="font-bold text-red-400">فشل الاستيراد</span>
        @endif
    </div>
    <div class="text-sm space-y-1">
        <div class="{{ $r['imported'] > 0 ? 'text-green-300' : 'text-slate-400' }}">
            @if($r['imported'] > 0)
                ✓ تم استيراد/تحديث: <strong>{{ $r['imported'] }}</strong> اختبار
            @else
                ⚠ لم يتم استيراد أي اختبار
            @endif
        </div>
        @if($r['skipped'] > 0)
        <div class="text-amber-400">⚠ تم تخطي: <strong>{{ $r['skipped'] }}</strong> صف</div>
        @endif
        @foreach($r['errors'] as $err)
        <div class="text-red-400 text-xs font-mono bg-red-500/10 px-3 py-1.5 rounded-lg mt-1">{{ $err }}</div>
        @endforeach
    </div>
</div>
@endif

<div x-data="goetheB1Import()" class="space-y-6">

    {{-- Teil picker --}}
    <div class="rounded-2xl border bg-[#111216] border-white/[0.08] p-6">
        <div class="flex items-baseline justify-between mb-3">
            <h2 class="font-bold text-white">اختر الجزء (Teil)</h2>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">سيتم تحديث هذا العمود فقط</span>
        </div>
        <p class="text-xs text-slate-500 mb-4" dir="rtl">كل ملف JSON خاص بجزء واحد. الـ slug المطابق في قاعدة البيانات (<code class="text-amber-400" dir="ltr">arena-b1-test-{N}</code>) يحافظ على باقي الأجزاء.</p>
        <div class="flex flex-wrap gap-2">
            @foreach($teilOptions as $key => $label)
            <button type="button" @click="setTeil('{{ $key }}')"
                    :class="teil === '{{ $key }}' ? 'border-amber-500/60 bg-amber-500/15 text-white shadow-lg shadow-amber-500/10' : 'border-white/[0.08] bg-[#0B0C10] text-slate-400 hover:border-white/20 hover:text-white'"
                    class="px-4 py-2.5 rounded-xl border text-sm font-bold transition-all">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Source picker --}}
    <div class="rounded-2xl border bg-[#111216] border-white/[0.08] p-6">
        <h2 class="font-bold text-white mb-4">اختار طريقة الاستيراد</h2>
        <div class="grid sm:grid-cols-2 gap-3">
            <button type="button" @click="setSource('json_text')"
                    :class="source === 'json_text' ? 'border-amber-500/50 bg-amber-500/10 text-white' : 'border-white/[0.08] text-slate-400 hover:border-white/20 hover:text-slate-300'"
                    class="p-4 rounded-xl border text-right transition-all">
                <div class="font-bold text-sm mb-0.5">JSON نص مباشر</div>
                <div class="text-xs opacity-60">الصق محتوى الملف</div>
            </button>
            <button type="button" @click="setSource('json_file')"
                    :class="source === 'json_file' ? 'border-amber-500/50 bg-amber-500/10 text-white' : 'border-white/[0.08] text-slate-400 hover:border-white/20 hover:text-slate-300'"
                    class="p-4 rounded-xl border text-right transition-all">
                <div class="font-bold text-sm mb-0.5">ملف JSON</div>
                <div class="text-xs opacity-60">ارفع <code class="text-amber-400" dir="ltr">lesen_teilN.json</code></div>
            </button>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.goethe-b1.lesen.import.handle') }}" enctype="multipart/form-data"
          class="rounded-2xl border bg-[#111216] border-white/[0.08] p-6 space-y-5">
        @csrf
        <input type="hidden" name="teil" :value="teil">
        <input type="hidden" name="source" :value="source">

        <div class="flex items-center justify-between pb-3 border-b border-white/[0.05]">
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500" dir="rtl">سيتم استيراد:</span>
                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-500/15 border border-amber-500/30 text-amber-300" x-text="teilLabel()"></span>
            </div>
            <span class="text-xs text-slate-600" x-text="'column: ' + teil"></span>
        </div>

        {{-- JSON text --}}
        <template x-if="source === 'json_text'">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-white">JSON Content</label>
                    <div class="flex items-center gap-2">
                        <span x-show="previewMsg" class="text-xs text-green-400" x-text="previewMsg"></span>
                        <button type="button" @click="validateJson()"
                                class="text-xs px-3 py-1.5 rounded-lg border border-amber-500/30 text-amber-400 hover:bg-amber-500/10 transition-colors">
                            معاينة
                        </button>
                    </div>
                </div>
                <textarea name="json_text" x-model="jsonText" rows="14"
                          class="w-full bg-[#0B0C10] border border-white/10 rounded-xl p-4 text-sm font-mono text-slate-300 focus:outline-none focus:border-amber-500/50 resize-y placeholder:text-slate-700"
                          dir="ltr"
                          :placeholder="placeholders[teil] || ''"></textarea>

                <div x-show="previewTests.length > 0" x-cloak class="mt-3 p-4 rounded-xl border border-white/[0.05] bg-[#0B0C10] text-xs font-mono">
                    <div class="text-slate-500 mb-2 flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md bg-amber-500/15 text-amber-400 text-[10px] font-bold uppercase" x-text="teilLabel()"></span>
                        <template x-if="declaredTeil !== null">
                            <span class="px-2 py-0.5 rounded-md bg-orange-500/15 text-orange-300 text-[10px] font-bold" x-text="'declared: teil' + declaredTeil"></span>
                        </template>
                        <span><span x-text="previewTests.length"></span> اختبار:</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="t in previewTests" :key="t">
                            <span class="px-2 py-1 rounded-md bg-white/[0.04] text-slate-300" x-text="'Test ' + t"></span>
                        </template>
                    </div>
                </div>
                <div x-show="jsonError" x-cloak class="mt-2 text-xs text-red-400 font-mono" x-text="jsonError"></div>
            </div>
        </template>

        {{-- JSON file --}}
        <template x-if="source === 'json_file'">
            <div>
                <label class="text-sm font-medium text-white block mb-2">ارفع ملف JSON</label>
                <label class="block cursor-pointer rounded-xl border-2 border-dashed transition-all p-6 text-center"
                       :class="fileName ? 'border-green-500/40 bg-green-500/5' : 'border-white/10 bg-[#0B0C10] hover:border-amber-500/40 hover:bg-amber-500/5'">
                    <input type="file" name="file" accept=".json" class="hidden" @change="onFile($event)">
                    <template x-if="!fileName">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2 text-slate-500"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <div class="text-sm font-medium text-slate-300">اضغط لاختيار <code class="text-amber-400" dir="ltr">lesen_teil<span x-text="teil.replace('teil','')"></span>.json</code></div>
                            <div class="text-xs text-slate-600 mt-1">حد أقصى 10MB</div>
                        </div>
                    </template>
                    <template x-if="fileName">
                        <div class="flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-400 shrink-0"><path d="M20 6 9 17l-5-5"/></svg>
                            <div class="text-left" dir="ltr">
                                <div class="text-sm font-bold text-white" x-text="fileName"></div>
                                <div class="text-xs text-slate-500" x-text="fileSizeKb + ' KB'"></div>
                            </div>
                        </div>
                    </template>
                </label>
            </div>
        </template>

        {{-- Submit --}}
        <div class="flex items-center justify-between pt-2 border-t border-white/[0.05] gap-3 flex-wrap">
            <div class="text-xs" dir="rtl">
                <p class="text-slate-600">الاختبارات تُطابق بـ <code class="text-slate-400" dir="ltr">arena-b1-test-{N}</code> — الموجود يُحدَّث، الجديد يُنشأ.</p>
                <p x-show="!canSubmit()" class="text-amber-400 mt-1" x-text="submitBlockedReason()"></p>
            </div>
            <button type="submit" :disabled="!canSubmit()"
                    class="btn-shine px-7 py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span x-text="'استيراد ' + teilLabel()"></span>
            </button>
        </div>
    </form>

    {{-- Schema reference --}}
    <div class="rounded-2xl border border-white/[0.08] bg-[#111216] p-6" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center justify-between w-full text-right">
            <span class="font-bold text-white text-sm">📋 شكل JSON المتوقع — <span x-text="teilLabel()" class="text-amber-400"></span></span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500 transition-transform" :class="open && 'rotate-180'"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div x-show="open" x-transition class="mt-4">
            <pre class="text-xs font-mono text-slate-400 bg-[#0B0C10] p-4 rounded-xl overflow-x-auto leading-relaxed" x-text="schemas[teil] || ''"></pre>
        </div>
    </div>
</div>

@push('scripts')
<script>
function goetheB1Import() {
    const TEIL_LABELS = @json($teilOptions);

    const schemas = {
        teil1: `// Teil 1 — Richtig/Falsch
{
  "module": "Lesen",
  "teil": 1,
  "exams": [
    {
      "test": 1,
      "instructions": "Lies den Text und die Aufgaben 1 bis 6 dazu.",
      "passage_text": "Vollständiger Text …",
      "questions": [
        { "number": 1, "prompt": "Aussage 1 …", "answer": "R" },
        { "number": 2, "prompt": "Aussage 2 …", "answer": "F" }
      ]
    }
  ]
}`,
        teil2: `// Teil 2 — 2 Texte + Multiple Choice (a/b/c)
{
  "module": "Lesen",
  "teil": 2,
  "exams": [
    {
      "test": 1,
      "instructions": "Lies den Text und beantworte die Aufgaben.",
      "source_texts": [
        { "label": "Text 1", "title": "…", "text": "…", "question_range": [7, 9] },
        { "label": "Text 2", "title": "…", "text": "…", "question_range": [10, 12] }
      ],
      "questions": [
        {
          "number": 7, "prompt": "…",
          "choices": [
            { "label": "a", "text": "…" },
            { "label": "b", "text": "…" },
            { "label": "c", "text": "…" }
          ],
          "answer": "b"
        }
      ]
    }
  ]
}`,
        teil3: `// Teil 3 — Zuordnung (Situationen ↔ Anzeigen A–J)
{
  "module": "Lesen",
  "teil": 3,
  "exams": [
    {
      "test": 1,
      "instructions": "Welche Anzeige passt zu welcher Situation?",
      "theme": "Schüler suchen Minijobs.",
      "beispiel": { "anzeige": "A" },
      "situations": [
        { "number": 13, "prompt": "Fabian möchte …", "answer": "G" },
        { "number": 14, "prompt": "Carla möchte …", "answer": "0" }
      ],
      "advertisements_text": "A Nette Aushilfe …\\nB Du bist 13 Jahre alt …\\nC …"
    }
  ]
}`,
        teil4: `// Teil 4 — Dafür / Dagegen
{
  "module": "Lesen",
  "teil": 4,
  "exams": [
    {
      "test": 1,
      "instructions": "Lies die Texte. Ist die Person dafür oder dagegen?",
      "topic": "den Vorschlag, Fast-Food an Schulen zu verbieten.",
      "comments": [
        {
          "number": 20, "prompt": "An unserer Schule …",
          "author": { "name": "Svenja", "age": 13, "city": "Wien" },
          "answer": "J"
        }
      ]
    }
  ]
}`,
        teil5: `// Teil 5 — Reading + Multiple Choice
{
  "module": "Lesen",
  "teil": 5,
  "exams": [
    {
      "test": 1,
      "instructions": "Wähle die richtige Lösung a, b oder c.",
      "situation": "Hausordnung im Yoga-Zentrum …",
      "reading_text": "Yoga-Zentrum Erding …",
      "questions": [
        {
          "number": 27, "prompt": "Kurz vor der Yoga-Stunde …",
          "choices": [
            { "label": "a", "text": "…" },
            { "label": "b", "text": "…" },
            { "label": "c", "text": "…" }
          ],
          "answer": "b"
        }
      ]
    }
  ]
}`,
    };

    return {
        teil: @json($defaultTeil),
        source: 'json_text',
        jsonText: '',
        fileName: '',
        fileSizeKb: 0,
        previewMsg: '',
        previewTests: [],
        declaredTeil: null,
        jsonError: '',
        placeholders: schemas,
        schemas: schemas,

        teilLabel() { return TEIL_LABELS[this.teil] || this.teil; },

        setTeil(t) {
            this.teil = t;
            this.previewMsg = '';
            this.previewTests = [];
            this.declaredTeil = null;
            this.jsonError = '';
        },

        setSource(s) {
            this.source = s;
            this.fileName = '';
            this.fileSizeKb = 0;
        },

        onFile(event) {
            const f = event.target.files?.[0];
            if (!f) { this.fileName = ''; this.fileSizeKb = 0; return; }
            this.fileName = f.name;
            this.fileSizeKb = Math.round(f.size / 1024);
        },

        async validateJson() {
            this.jsonError = '';
            this.previewMsg = '';
            this.previewTests = [];
            this.declaredTeil = null;
            if (!this.jsonText.trim()) {
                this.jsonError = 'Empty';
                return;
            }
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(@json(route('admin.goethe-b1.lesen.import.preview')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ json: this.jsonText }),
                });
                const data = await res.json();
                if (!res.ok || data.error) {
                    this.jsonError = data.error || 'Preview failed';
                    return;
                }
                this.previewTests = data.tests || [];
                this.declaredTeil = data.declared_teil ?? null;
                this.previewMsg = '✓ ' + (data.count || 0) + ' tests';
            } catch (e) {
                this.jsonError = e.message;
            }
        },

        canSubmit() {
            if (this.source === 'json_text') return this.jsonText.trim().length > 10;
            return !!this.fileName;
        },

        submitBlockedReason() {
            if (this.source === 'json_text') return 'الصق JSON في المربع أعلاه.';
            return 'اختر ملف JSON.';
        },
    };
}
</script>
@endpush

@endsection
