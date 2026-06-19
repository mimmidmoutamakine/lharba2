@extends('admin.layout')
@section('title', 'استيراد – Telc B2 Mündlich Teil 2')
@section('page-title', 'استيراد بيانات Telc B2 Mündlich · Teil 2')

@php
    $kindOptions = [
        'universal' => 'Universal (العُدّة الشاملة)',
        'clusters'  => 'Clusters (عائلات المواضيع)',
        'topics'    => 'Topics (المواضيع)',
    ];
    $defaultKind = session('import_kind', 'universal');
@endphp

@section('content')

@if($errors->any())
<div class="mb-6 p-5 rounded-2xl border bg-red-500/10 border-red-500/20">
    <span class="font-bold text-red-400">خطأ في التحقق</span>
    <ul class="text-sm text-red-300 space-y-1 list-disc list-inside mt-2">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

@if(session('import_result'))
@php $r = session('import_result'); @endphp
<div class="mb-6 p-5 rounded-2xl border {{ $r['imported'] > 0 ? 'bg-green-500/10 border-green-500/20' : 'bg-red-500/10 border-red-500/20' }}">
    <div class="font-bold {{ $r['imported'] > 0 ? 'text-green-400' : 'text-red-400' }} mb-2">
        {{ $r['imported'] > 0 ? 'تم الاستيراد بنجاح' : 'فشل الاستيراد' }}
    </div>
    <div class="text-sm space-y-1">
        <div class="{{ $r['imported'] > 0 ? 'text-green-300' : 'text-slate-400' }}">
            @if($r['imported'] > 0)
                ✓ تم استيراد/تحديث: <strong>{{ $r['imported'] }}</strong>
                @if(($r['summary']['topic_count'] ?? null) !== null) موضوع @endif
                @if(($r['summary']['cluster_count'] ?? null) !== null) عائلة @endif
                @if(($r['summary']['argument_categories'] ?? null) !== null) ملف شامل ({{ $r['summary']['argument_categories'] }} صنف حجج) @endif
            @else
                ⚠ لم يتم استيراد أي عنصر
            @endif
        </div>
        @if($r['skipped'] > 0)<div class="text-amber-400">⚠ تم تخطي: <strong>{{ $r['skipped'] }}</strong></div>@endif
        @foreach($r['errors'] as $err)
        <div class="text-red-400 text-xs font-mono bg-red-500/10 px-3 py-1.5 rounded-lg mt-1">{{ $err }}</div>
        @endforeach
    </div>
</div>
@endif

<div x-data="sprechenImport()" class="space-y-6">

    {{-- Kind picker --}}
    <div class="rounded-2xl border bg-[#111216] border-white/[0.08] p-6">
        <div class="flex items-baseline justify-between mb-3">
            <h2 class="font-bold text-white">شنو غادي تستورد؟</h2>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">ملف JSON واحد لكل نوع</span>
        </div>
        <p class="text-xs text-slate-500 mb-4" dir="rtl">
            <strong class="text-amber-300">Universal</strong> = <code dir="ltr" class="text-amber-400">sprechen_teil2_universal.json</code> (صف وحيد، يُستبدل بالكامل).
            <strong class="text-amber-300">Clusters</strong> = <code dir="ltr" class="text-amber-400">sprechen_teil2_clusters.json</code>.
            <strong class="text-amber-300">Topics</strong> = <code dir="ltr" class="text-amber-400">sprechen_teil2_topics.json</code>.
            (Clusters/Topics: يطابق بالـ id — الموجود يُحدَّث، غير المذكور يبقى.)
        </p>
        <div class="grid sm:grid-cols-3 gap-3">
            @foreach($kindOptions as $key => $label)
            <button type="button" @click="setKind('{{ $key }}')"
                    :class="kind === '{{ $key }}' ? 'border-amber-500/50 bg-amber-500/10 text-white' : 'border-white/[0.08] text-slate-400 hover:border-white/20 hover:text-slate-300'"
                    class="p-4 rounded-xl border text-right transition-all">
                <div class="font-bold text-sm mb-0.5">{{ $label }}</div>
            </button>
            @endforeach
        </div>
    </div>

    {{-- Source picker --}}
    <div class="rounded-2xl border bg-[#111216] border-white/[0.08] p-6">
        <h2 class="font-bold text-white mb-4">اختار طريقة الاستيراد</h2>
        <div class="grid sm:grid-cols-2 gap-3">
            <button type="button" @click="setSource('json_text')"
                    :class="source === 'json_text' ? 'border-amber-500/50 bg-amber-500/10 text-white' : 'border-white/[0.08] text-slate-400 hover:border-white/20'"
                    class="p-4 rounded-xl border text-right transition-all">
                <div class="font-bold text-sm mb-0.5">JSON نص مباشر</div>
                <div class="text-xs opacity-60">الصق محتوى الملف</div>
            </button>
            <button type="button" @click="setSource('json_file')"
                    :class="source === 'json_file' ? 'border-amber-500/50 bg-amber-500/10 text-white' : 'border-white/[0.08] text-slate-400 hover:border-white/20'"
                    class="p-4 rounded-xl border text-right transition-all">
                <div class="font-bold text-sm mb-0.5">ملف JSON</div>
                <div class="text-xs opacity-60">ارفع الملف الكامل</div>
            </button>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.mundlich.b2-sprechen.import.handle') }}" enctype="multipart/form-data"
          class="rounded-2xl border bg-[#111216] border-white/[0.08] p-6 space-y-5">
        @csrf
        <input type="hidden" name="kind" :value="kind">
        <input type="hidden" name="source" :value="source">

        <div class="flex items-center justify-between pb-3 border-b border-white/[0.05]">
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500" dir="rtl">سيتم استيراد:</span>
                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-500/15 border border-amber-500/30 text-amber-300" x-text="kindLabel()"></span>
            </div>
            <span class="text-xs text-slate-600" x-text="'kind: ' + kind"></span>
        </div>

        <template x-if="source === 'json_text'">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-white">JSON Content</label>
                    <div class="flex items-center gap-2">
                        <span x-show="previewMsg" class="text-xs text-green-400" x-text="previewMsg"></span>
                        <button type="button" @click="validateJson()" class="text-xs px-3 py-1.5 rounded-lg border border-amber-500/30 text-amber-400 hover:bg-amber-500/10 transition-colors">معاينة</button>
                    </div>
                </div>
                <textarea name="json_text" x-model="jsonText" rows="14"
                          class="w-full bg-[#0B0C10] border border-white/10 rounded-xl p-4 text-sm font-mono text-slate-300 focus:outline-none focus:border-amber-500/50 resize-y placeholder:text-slate-700"
                          dir="ltr" :placeholder="placeholders[kind] || ''"></textarea>
                <div x-show="previewSummary" x-cloak class="mt-3 p-4 rounded-xl border border-white/[0.05] bg-[#0B0C10] text-xs font-mono">
                    <div class="text-slate-400 whitespace-pre-wrap" x-text="previewSummary"></div>
                </div>
                <div x-show="jsonError" x-cloak class="mt-2 text-xs text-red-400 font-mono" x-text="jsonError"></div>
            </div>
        </template>

        <template x-if="source === 'json_file'">
            <div>
                <label class="text-sm font-medium text-white block mb-2">ارفع ملف JSON</label>
                <label class="block cursor-pointer rounded-xl border-2 border-dashed transition-all p-6 text-center"
                       :class="fileName ? 'border-green-500/40 bg-green-500/5' : 'border-white/10 bg-[#0B0C10] hover:border-amber-500/40 hover:bg-amber-500/5'">
                    <input type="file" name="file" accept=".json" class="hidden" @change="onFile($event)">
                    <template x-if="!fileName">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2 text-slate-500"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <div class="text-sm font-medium text-slate-300" x-text="expectedFile()"></div>
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

        <div class="flex items-center justify-between pt-2 border-t border-white/[0.05] gap-3 flex-wrap">
            <div class="text-xs" dir="rtl">
                <p class="text-slate-600" x-text="kind === 'universal'
                    ? 'الـ Universal صف وحيد — كل رفع يستبدل النسخة الحالية.'
                    : 'يطابَق بالـ id — الموجود يُحدَّث، الجديد يُنشأ، غير المذكور يبقى كما هو.'"></p>
                <p x-show="!canSubmit()" class="text-amber-400 mt-1" x-text="submitBlockedReason()"></p>
            </div>
            <button type="submit" :disabled="!canSubmit()"
                    class="btn-shine px-7 py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span x-text="'استيراد ' + kindLabel()"></span>
            </button>
        </div>
    </form>

    {{-- Schema reference --}}
    <div class="rounded-2xl border border-white/[0.08] bg-[#111216] p-6" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center justify-between w-full text-right">
            <span class="font-bold text-white text-sm">📋 شكل JSON المتوقع</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-500 transition-transform" :class="open && 'rotate-180'"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div x-show="open" x-transition class="mt-4 space-y-4">
            <div>
                <div class="text-xs font-bold text-amber-300 mb-1">universal</div>
                <pre class="text-xs font-mono text-slate-400 bg-[#0B0C10] p-4 rounded-xl overflow-x-auto leading-relaxed">{
  "meta": { "level": "B2", "philosophy": "..." },
  "universal_argument_categories": [
    { "id": "gesundheit", "title": "Gesundheit",
      "core_sentences": ["..."], "vocabulary_chips": ["..."], "example": "..." }
  ],
  "presentation_structures": { "presentation": {...}, "meinung": {...}, ... },
  "emergency_blocks": { "balanced_opinion": "...", ... }
}</pre>
            </div>
            <div>
                <div class="text-xs font-bold text-amber-300 mb-1">clusters</div>
                <pre class="text-xs font-mono text-slate-400 bg-[#0B0C10] p-4 rounded-xl overflow-x-auto leading-relaxed">{
  "clusters": [
    { "id": "ernaehrung_gesundheit", "title": "Ernährung & Gesundheit",
      "topic_orders": [1, 2, 4],
      "universal_argument_ids": ["gesundheit", "zeit"],
      "selected_universal_arguments": ["..."],
      "cluster_vocabulary": ["..."] }
  ]
}</pre>
            </div>
            <div>
                <div class="text-xs font-bold text-amber-300 mb-1">topics</div>
                <pre class="text-xs font-mono text-slate-400 bg-[#0B0C10] p-4 rounded-xl overflow-x-auto leading-relaxed">{
  "topics": [
    { "order": 1, "id": "sprechen_t2_fast_food...", "title": "Fast Food...",
      "cluster_ids": ["ernaehrung_gesundheit"],
      "highlight_sentences": ["..."], "main_ideas": ["..."],
      "arguments": { "dafuer": ["..."], "dagegen": ["..."] },
      "opinion_adjectives": { "positive": ["..."], "negative": ["..."] },
      "opinion_example": "...", "experience_example": "...",
      "difficult_vocabulary": ["..."] }
  ]
}</pre>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function sprechenImport() {
    return {
        kind: @json($defaultKind),
        source: 'json_text',
        jsonText: '', fileName: '', fileSizeKb: 0,
        previewMsg: '', previewSummary: '', jsonError: '',
        placeholders: {
            universal: '{\n  "meta": {...},\n  "universal_argument_categories": [...],\n  "presentation_structures": {...},\n  "emergency_blocks": {...}\n}',
            clusters:  '{\n  "clusters": [\n    { "id": "...", "title": "...", "topic_orders": [...], "universal_argument_ids": [...] }\n  ]\n}',
            topics:    '{\n  "topics": [\n    { "order": 1, "id": "...", "title": "...", "arguments": {...}, "main_ideas": [...] }\n  ]\n}',
        },
        labels: { universal: 'Universal', clusters: 'Clusters', topics: 'Topics' },
        files:  { universal: 'sprechen_teil2_universal.json', clusters: 'sprechen_teil2_clusters.json', topics: 'sprechen_teil2_topics.json' },
        kindLabel() { return this.labels[this.kind] || this.kind; },
        expectedFile() { return 'اضغط لاختيار ' + (this.files[this.kind] || '.json'); },
        setKind(k) { this.kind = k; this.jsonText = ''; this.fileName=''; this.fileSizeKb=0; this.previewMsg=''; this.previewSummary=''; this.jsonError=''; },
        setSource(s) { this.source = s; this.fileName=''; this.fileSizeKb=0; },
        onFile(e) { const f = e.target.files?.[0]; if (!f) return; this.fileName = f.name; this.fileSizeKb = (f.size/1024).toFixed(1); },
        canSubmit() { return this.source === 'json_text' ? this.jsonText.trim().length > 0 : this.fileName !== ''; },
        submitBlockedReason() { return this.source === 'json_text' ? 'الصق محتوى JSON أولاً' : 'اختر ملفاً'; },
        validateJson() {
            this.previewMsg=''; this.previewSummary=''; this.jsonError='';
            if (!this.jsonText.trim()) { this.jsonError = 'لا يوجد محتوى'; return; }
            try {
                const obj = JSON.parse(this.jsonText);
                if (this.kind === 'universal') {
                    if (!Array.isArray(obj.universal_argument_categories)) { this.jsonError = 'يجب أن يحتوي على "universal_argument_categories" array.'; return; }
                    this.previewSummary = `argument categories: ${obj.universal_argument_categories.length}\nstructures: ${obj.presentation_structures ? 'yes' : 'no'} · emergency: ${obj.emergency_blocks ? 'yes' : 'no'}`;
                } else if (this.kind === 'clusters') {
                    if (!Array.isArray(obj.clusters)) { this.jsonError = 'يجب أن يحتوي على "clusters" array.'; return; }
                    const keys = obj.clusters.map(c => c.id).filter(Boolean);
                    this.previewSummary = `clusters: ${keys.length}\n${keys.join(', ')}`;
                } else {
                    if (!Array.isArray(obj.topics)) { this.jsonError = 'يجب أن يحتوي على "topics" array.'; return; }
                    const slugs = obj.topics.map(t => t.id).filter(Boolean);
                    this.previewSummary = `topics: ${slugs.length}\n${slugs.slice(0, 6).join(', ')}${slugs.length > 6 ? ', …' : ''}`;
                }
                this.previewMsg = 'JSON صالح ✓';
            } catch (e) { this.jsonError = 'JSON غير صالح: ' + e.message; }
        },
    };
}
</script>
@endpush

@endsection
