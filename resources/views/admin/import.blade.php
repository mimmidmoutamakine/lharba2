@extends('admin.layout')
@section('title', 'استيراد – ' . strtoupper($type))
@section('page-title', 'استيراد مواضيع ' . strtoupper($type))

@php
    // Only Lesen has multiple Teils per topic.
    // Hören & Schreiben: each entry = one full topic (teil is just a column on Hören).
    $partOptions = $type === 'lesen' ? [
        'teil1'            => 'Lesen Teil 1',
        'teil2'            => 'Lesen Teil 2',
        'teil3'            => 'Lesen Teil 3',
        'sprachbausteine1' => 'Sprachbausteine 1',
        'sprachbausteine2' => 'Sprachbausteine 2',
    ] : [];
    $hasParts    = !empty($partOptions);
    $supportsXls = $type === 'lesen';
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

{{-- Import Result Flash --}}
@if(session('import_result'))
@php $r = session('import_result'); @endphp
<div class="mb-6 p-5 rounded-2xl border {{ $r['imported'] > 0 ? 'bg-green-500/10 border-green-500/20' : 'bg-red-500/10 border-red-500/20' }}">
    <div class="flex items-center gap-3 mb-2">
        @if($r['imported'] > 0)
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-400"><path d="M20 6 9 17l-5-5"/></svg>
        <span class="font-bold text-green-400">تم الاستيراد بنجاح</span>
        @else
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-400"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span class="font-bold text-red-400">فشل الاستيراد</span>
        @endif
    </div>
    <div class="text-sm space-y-1">
        <div class="{{ $r['imported'] > 0 ? 'text-green-300' : 'text-slate-400' }}">
            @if($r['imported'] > 0)
                ✓ تم استيراد/تحديث: <strong>{{ $r['imported'] }}</strong> موضوع
            @else
                ⚠ لم يتم استيراد أي موضوع
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

{{-- Tab switcher (lesen / hoeren / schreiben) --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.import.show', 'lesen') }}"
       class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ $type === 'lesen' ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400 hover:text-white' }}">
        Lesen
    </a>
    <a href="{{ route('admin.import.show', 'hoeren') }}"
       class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ $type === 'hoeren' ? 'bg-orange-600 text-white' : 'bg-white/5 text-slate-400 hover:text-white' }}">
        Hören
    </a>
    <a href="{{ route('admin.import.show', 'schreiben') }}"
       class="px-4 py-2 rounded-full text-sm font-medium transition-all {{ $type === 'schreiben' ? 'bg-emerald-600 text-white' : 'bg-white/5 text-slate-400 hover:text-white' }}">
        Schreiben
    </a>
</div>

<div x-data="importPage()" class="space-y-6">

    @if($hasParts)
    {{-- Part picker (Teil) — only for Lesen / Hören --}}
    <div class="rounded-2xl border bg-[#111216] border-white/[0.08] p-6">
        <div class="flex items-baseline justify-between mb-3">
            <h2 class="font-bold text-white">اختر الجزء (Teil)</h2>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">سيتم تحديث هذا العمود فقط</span>
        </div>
        <p class="text-xs text-slate-500 mb-4">كل ملف JSON خاص بجزء واحد. الـ slug المطابق في قاعدة البيانات يحافظ على باقي الأجزاء (مثلاً: لو رفعت Teil 2، فإن Teil 1 الموجود سيبقى كما هو).</p>
        <div class="flex flex-wrap gap-2">
            @foreach($partOptions as $key => $label)
            <button type="button" @click="setPart('{{ $key }}')"
                    :class="part === '{{ $key }}' ? 'border-amber-500/60 bg-amber-500/15 text-white shadow-lg shadow-amber-500/10' : 'border-white/[0.08] bg-[#0B0C10] text-slate-400 hover:border-white/20 hover:text-white'"
                    class="px-4 py-2.5 rounded-xl border text-sm font-bold transition-all">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>
    @else
    {{-- Hören & Schreiben — single bulk import notice --}}
    @php
        $tone = $type === 'schreiben' ? 'emerald' : 'orange';
        $title = $type === 'schreiben' ? 'استيراد Schreiben' : 'استيراد Hören';
        $shape = $type === 'schreiben'
            ? '<code class="text-emerald-300">title</code>, <code class="text-emerald-300">scenario</code>, <code class="text-emerald-300">level</code> (B1/B2), <code class="text-emerald-300">type</code>, <code class="text-emerald-300">points[]</code>, <code class="text-emerald-300">minutes</code>'
            : '<code class="text-orange-300">title</code>, <code class="text-orange-300">level</code> (B1/B2), <code class="text-orange-300">teil</code> (1-4), <code class="text-orange-300">audio_url</code>, <code class="text-orange-300">duration</code>, <code class="text-orange-300">statements[]</code>, <code class="text-orange-300">correct_numbers[]</code>, <code class="text-orange-300">flashcards[]</code>';
    @endphp
    <div class="rounded-2xl border bg-{{ $tone }}-500/[0.04] border-{{ $tone }}-500/20 p-5">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 shrink-0 rounded-lg bg-{{ $tone }}-500/15 border border-{{ $tone }}-500/30 flex items-center justify-center">
                @if($type === 'schreiben')
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-{{ $tone }}-300"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                @else
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-{{ $tone }}-300"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                @endif
            </div>
            <div>
                <div class="font-bold text-white text-sm">{{ $title }}</div>
                <div class="text-xs text-slate-400 mt-1 leading-relaxed">ملف JSON واحد يحتوي على جميع المواضيع — كل entry موضوع كامل. الحقول: {!! $shape !!}.</div>
                @if($type === 'hoeren')
                <div class="text-[11px] text-slate-500 mt-2 leading-relaxed">ملاحظة: <code class="text-orange-300/80">audio_url</code> خاصو يكون مسار نسبي للملف الصوتي (مثال: <code class="text-slate-400">/audio/teil1/restaurant.mp3</code>) — ارفع الملفات الصوتية فـ <code class="text-slate-400">public/audio/</code> منفصلة.</div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Source picker --}}
    <div class="rounded-2xl border bg-[#111216] border-white/[0.08] p-6">
        <h2 class="font-bold text-white mb-4">اختار طريقة الاستيراد</h2>
        @php
            $sourceOptions = [
                ['value' => 'json_text', 'label' => 'JSON نص مباشر', 'desc' => 'الصق JSON مباشرة'],
                ['value' => 'json_file', 'label' => 'ملف JSON',       'desc' => 'ارفع ملف .json'],
            ];
            if ($supportsXls) {
                $sourceOptions[] = ['value' => 'excel', 'label' => 'Excel / CSV', 'desc' => 'ارفع .xlsx أو .csv (multi-part)'];
            }
        @endphp
        <div class="grid sm:grid-cols-{{ $supportsXls ? 3 : 2 }} gap-3">
            @foreach($sourceOptions as $opt)
            <button type="button" @click="setSource('{{ $opt['value'] }}')"
                    :class="source === '{{ $opt['value'] }}' ? 'border-amber-500/50 bg-amber-500/10 text-white' : 'border-white/[0.08] text-slate-400 hover:border-white/20 hover:text-slate-300'"
                    class="p-4 rounded-xl border text-right transition-all">
                <div class="font-bold text-sm mb-0.5">{{ $opt['label'] }}</div>
                <div class="text-xs opacity-60">{{ $opt['desc'] }}</div>
            </button>
            @endforeach
        </div>
    </div>

    {{-- Import Form --}}
    <form method="POST" action="{{ route('admin.import.handle', $type) }}" enctype="multipart/form-data"
          class="rounded-2xl border bg-[#111216] border-white/[0.08] p-6 space-y-5">
        @csrf
        <input type="hidden" name="source" :value="source">
        @if($hasParts)
        <input type="hidden" name="part" :value="part">

        {{-- Active part badge --}}
        <div class="flex items-center justify-between pb-3 border-b border-white/[0.05]">
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500" dir="rtl">سيتم استيراد:</span>
                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-amber-500/15 border border-amber-500/30 text-amber-300" x-text="partLabel()"></span>
            </div>
            <span class="text-xs text-slate-600" x-text="'column: ' + part"></span>
        </div>
        @endif

        {{-- JSON Text — only rendered when active so it's the only field submitted --}}
        <template x-if="source === 'json_text'">
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-white">JSON Content</label>
                <div class="flex items-center gap-2">
                    <span x-show="preview" class="text-xs text-green-400" x-text="preview"></span>
                    <button type="button" @click="validateJson()"
                            class="text-xs px-3 py-1.5 rounded-lg border border-amber-500/30 text-amber-400 hover:bg-amber-500/10 transition-colors">
                        معاينة
                    </button>
                </div>
            </div>
            <textarea
                name="json_text"
                x-model="jsonText"
                rows="14"
                class="w-full bg-[#0B0C10] border border-white/10 rounded-xl p-4 text-sm font-mono text-slate-300 focus:outline-none focus:border-amber-500/50 resize-y placeholder:text-slate-700"
                dir="ltr"
                :placeholder="placeholders[part] || ''"></textarea>

            {{-- JSON Preview Panel --}}
            <div x-show="previewData.length > 0" x-cloak class="mt-3 p-4 rounded-xl border border-white/[0.05] bg-[#0B0C10] text-xs font-mono overflow-x-auto">
                <div class="text-slate-500 mb-2 flex items-center gap-2 flex-wrap">
                    <span class="px-2 py-0.5 rounded-md bg-amber-500/15 text-amber-400 text-[10px] font-bold uppercase" x-text="partLabel()"></span>
                    <template x-if="declaredPart">
                        <span class="px-2 py-0.5 rounded-md bg-orange-500/15 text-orange-300 text-[10px] font-bold" x-text="'declared: ' + declaredPart"></span>
                    </template>
                    <span>Preview (<span x-text="previewCount"></span> امتحان):</span>
                </div>
                <template x-for="(t, i) in previewData" :key="i">
                    <div class="mb-2 p-2 rounded-lg bg-white/[0.03] text-slate-400">
                        <template x-if="t.individual_title">
                            <span class="text-white font-bold mr-1" x-text="t.individual_title"></span>
                        </template>
                        <span class="text-amber-400 font-bold" :class="t.individual_title ? 'text-[10px] opacity-70' : ''" x-text="t.individual_title ? '· ' + t.title : t.title"></span>
                        <span class="text-slate-600 ml-2" x-text="'[' + (t.level || 'B1') + ']'"></span>
                        <template x-if="t.has_content">
                            <span class="text-green-500/70 ml-2 text-[10px]">✓ content</span>
                        </template>
                        <template x-if="!t.has_content">
                            <span class="text-amber-500/70 ml-2 text-[10px]">⚠ no content field</span>
                        </template>
                    </div>
                </template>
            </div>
            <div x-show="jsonError" x-cloak class="mt-2 text-xs text-red-400 font-mono" x-text="jsonError"></div>
        </div>
        </template>

        {{-- JSON File --}}
        <template x-if="source === 'json_file'">
        <div>
            <label class="text-sm font-medium text-white block mb-2">ارفع ملف JSON</label>
            <label class="block cursor-pointer rounded-xl border-2 border-dashed transition-all p-6 text-center"
                   :class="fileName ? 'border-green-500/40 bg-green-500/5' : 'border-white/10 bg-[#0B0C10] hover:border-amber-500/40 hover:bg-amber-500/5'">
                <input type="file" name="file" accept=".json" class="hidden" @change="onFile($event)">
                <template x-if="!fileName">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2 text-slate-500"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <div class="text-sm font-medium text-slate-300">اضغط لاختيار ملف JSON</div>
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
            <p class="mt-2 text-xs text-slate-600">ملف export (يحتوي على <code class="text-slate-400">entries</code>) أو array مباشرة.</p>
        </div>
        </template>

        {{-- Excel / CSV (multi-part) --}}
        <template x-if="source === 'excel'">
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-white block mb-2">ارفع ملف Excel أو CSV</label>
                <label class="block cursor-pointer rounded-xl border-2 border-dashed transition-all p-6 text-center"
                       :class="fileName ? 'border-green-500/40 bg-green-500/5' : 'border-white/10 bg-[#0B0C10] hover:border-amber-500/40 hover:bg-amber-500/5'">
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden" @change="onFile($event)">
                    <template x-if="!fileName">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-2 text-slate-500"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <div class="text-sm font-medium text-slate-300">اضغط لاختيار ملف Excel/CSV</div>
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
            <div class="p-4 rounded-xl border border-amber-500/20 bg-amber-500/5">
                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400 shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span class="text-xs font-bold text-amber-400">ملاحظة</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">Excel/CSV multi-part — كل صف موضوع كامل. اختيار الـ Teil أعلاه لا ينطبق هنا (يتم تعبئة كل الأعمدة الموجودة في الملف).</p>
                <div class="mt-3 text-xs text-slate-500 font-mono">
                    @if($type === 'lesen')
                    الأعمدة: title, title_ar, slug, level, category, is_published, teil1, teil2, teil3, sprachbausteine1, sprachbausteine2
                    @else
                    الأعمدة: title, title_ar, slug, level, category, is_published, audio_path, teil1, teil2, teil3, teil4
                    @endif
                </div>
            </div>
        </div>
        </template>

        {{-- Submit --}}
        <div class="flex items-center justify-between pt-2 border-t border-white/[0.05] gap-3 flex-wrap">
            <div class="text-xs" dir="rtl">
                <p class="text-slate-600">المواضيع تُطابق بـ slug — الموجود يُحدَّث، الجديد يُنشأ.</p>
                <p x-show="!canSubmit()" class="text-amber-400 mt-1" x-text="submitBlockedReason()"></p>
            </div>
            <button type="submit"
                    :disabled="!canSubmit()"
                    class="btn-shine px-7 py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm transition-all shadow-lg shadow-amber-500/20 flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                <span x-text="'استيراد ' + partLabel()"></span>
            </button>
        </div>
    </form>

    {{-- JSON Schema Reference (per-Teil) --}}
    <div class="rounded-2xl border border-white/[0.08] bg-[#111216] p-6" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center justify-between w-full text-right">
            <span class="font-bold text-white text-sm">📋 مرجع هيكل JSON — <span x-text="partLabel()" class="text-amber-400"></span></span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500 transition-transform" :class="open && 'rotate-180'"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div x-show="open" x-transition class="mt-4">
            <pre class="text-xs font-mono text-slate-400 bg-[#0B0C10] p-4 rounded-xl overflow-x-auto leading-relaxed" x-text="references[part] || ''"></pre>
            <div class="mt-3 flex items-center gap-4 flex-wrap">
                <a href="#" @click.prevent="downloadSample()"
                   class="inline-flex items-center gap-2 text-xs text-amber-400 hover:text-amber-300 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span x-text="'تحميل مثال — ' + partLabel()"></span>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function importPage() {
    const PART_LABELS = @json($partOptions);

    // Per-Teil shape descriptions (placeholder + reference). Reading shapes match what
    // the topic view actually consumes — see resources/views/lesen/topic.blade.php.
    const shapes = {
        teil1: `// Teil 1 — Überschriften zuordnen (Headlines ↔ Texts)
{
  "exportedAt": "2026-04-01T03:59:40Z",
  "part": "Lesen Teil 1 B1",
  "entries": [
    {
      "examId": "exam-167799",
      "examTitle": "Alex und Cora",
      "individualTitle": "Headline-Text",
      "arabic_title": "",
      "level": "B1",
      "visibility": "public",
      "partId": "lesen1",
      "partTitle": "Lesen Teil 1",
      "content": {
        "headlines": [
          { "id": "a", "text": "Headline A …" },
          { "id": "b", "text": "Headline B …" }
        ],
        "texts": [
          { "id": 1, "content": "First German paragraph …", "summary": "Optional Arabic summary" },
          { "id": 2, "content": "Second German paragraph …" }
        ],
        "correctAnswers": { "1": "b", "2": "a" }
      }
    }
  ]
}`,
        teil2: `// Teil 2 — Multiple Choice (a / b / c)
{
  "exportedAt": "2026-04-01T03:59:40Z",
  "part": "Lesen Teil 2 B1",
  "entries": [
    {
      "examId": "exam-167799",
      "examTitle": "Alex und Cora",
      "individualTitle": "Haustiere in Deutschland",
      "arabic_title": "",
      "level": "B1",
      "visibility": "public",
      "partId": "lesen2",
      "partTitle": "Lesen Teil 2",
      "content": {
        "textTitle": "Haustiere in Deutschland",
        "textContent": "Egal ob Hund, Katze oder Maus …",
        "questions": [
          {
            "id": 6,
            "text": "Bettina Schröther …",
            "options": [
              "lässt ihren Hund in einer Hundeschule erziehen.",
              "macht mit ihrem Hund regelmäßig Wellnessurlaube.",
              "verbringt die ganze Freizeit mit ihrem Hund."
            ],
            "correct": 2
          }
        ]
      }
    }
  ]
}`,
        teil3: `// Teil 3 — Zuordnungen (situations ↔ ads)
{
  "exportedAt": "2026-04-01T03:49:06Z",
  "part": "Lesen Teil 3 B2",
  "entries": [
    {
      "examId": "exam-728871",
      "examTitle": "Auf dem weg",
      "individualTitle": "Musikinstrumente",
      "arabic_title": "في الطريق",
      "level": "B2",
      "visibility": "public",
      "partId": "lesen3",
      "partTitle": "Lesen Teil 3",
      "content": {
        "textTitle": "",
        "ads": [
          { "id": "a", "title": "Didgeridoo …", "text": "(Samstag …)\\nErlernt werden …",
            "summary": "ترجمة عربية اختيارية" },
          { "id": "b", "title": "Deutsch im Beruf", "text": "…", "summary": "" },
          { "id": "x", "title": "", "text": "Keine passende Anzeige", "summary": "" }
        ],
        "situations": [
          { "id": 11, "text": "Ein Freund interessiert sich für Musikinstrumente …" },
          { "id": 12, "text": "An Ihrem Arbeitsplatz müssen Sie viele deutsche Geschäftsbriefe …" }
        ],
        "correctAnswers": { "11": "x", "12": "b" }
      }
    }
  ]
}`,
        sprachbausteine1: `// Sprachbausteine Teil 1 — fill in the blanks (placeholder)
{
  "entries": [
    {
      "examTitle": "Alex und Cora",
      "level": "B1",
      "partId": "sprachbausteine1",
      "content": {
        "textContent": "Text mit ___ Lücken.",
        "questions": [
          { "gap": 1, "options": ["a", "b", "c"], "correct": 0 }
        ]
      }
    }
  ]
}`,
        sprachbausteine2: `// Sprachbausteine Teil 2 — word bank (placeholder)
{
  "entries": [
    {
      "examTitle": "Alex und Cora",
      "level": "B1",
      "partId": "sprachbausteine2",
      "content": { "textContent": "…", "questions": [] }
    }
  ]
}`,
        teil4: `// Hören Teil 4 (placeholder)
{
  "entries": [
    {
      "examTitle": "Beispiel",
      "level": "B1",
      "partId": "hoeren4",
      "content": { "questions": [] }
    }
  ]
}`,
    };

    return {
        part: @json(array_key_first($partOptions)),
        source: 'json_text',
        jsonText: '',
        fileName: '',
        fileSizeKb: 0,
        preview: '',
        previewData: [],
        previewCount: 0,
        declaredPart: null,
        jsonError: '',
        placeholders: shapes,
        references: shapes,

        partLabel() {
            return PART_LABELS[this.part] || this.part;
        },

        setPart(p) {
            this.part = p;
            this.preview = '';
            this.previewData = [];
            this.previewCount = 0;
            this.declaredPart = null;
            this.jsonError = '';
        },

        setSource(s) {
            this.source = s;
            this.fileName = '';
            this.fileSizeKb = 0;
        },

        onFile(event) {
            const f = event.target.files?.[0];
            if (!f) {
                this.fileName = '';
                this.fileSizeKb = 0;
                return;
            }
            this.fileName = f.name;
            this.fileSizeKb = Math.round(f.size / 1024);
        },

        canSubmit() {
            if (this.source === 'json_text') return this.jsonText.trim().length > 0;
            return !!this.fileName;
        },

        submitBlockedReason() {
            if (this.source === 'json_text') return 'الصق محتوى JSON قبل الاستيراد.';
            if (this.source === 'json_file') return 'اختر ملف JSON قبل الاستيراد.';
            if (this.source === 'excel')     return 'اختر ملف Excel/CSV قبل الاستيراد.';
            return '';
        },

        async validateJson() {
            this.jsonError = '';
            this.previewData = [];
            this.preview = '';
            this.previewCount = 0;
            this.declaredPart = null;
            try {
                const resp = await fetch('{{ route('admin.import.preview') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ json: this.jsonText, part: this.part }),
                });
                const data = await resp.json();
                if (!resp.ok) {
                    this.jsonError = data.error || 'خطأ في JSON';
                    return;
                }
                this.previewData   = data.preview;
                this.previewCount  = data.count;
                this.declaredPart  = data.declared_part || null;
                this.preview       = `✓ ${data.count} امتحان`;
            } catch (e) {
                this.jsonError = 'خطأ في الاتصال';
            }
        },

        downloadSample() {
            const sample = {
                exportedAt: new Date().toISOString(),
                total: 1,
                part: PART_LABELS[this.part] + ' B1',
                entries: [{
                    examId: 'exam-sample',
                    examTitle: 'Beispiel-Thema',
                    individualTitle: 'Titre individuel de ce Teil',
                    arabic_title: 'موضوع تجريبي',
                    level: 'B1',
                    visibility: 'public',
                    partId: this.part === 'sprachbausteine1' ? 'sprachbausteine1'
                          : this.part === 'sprachbausteine2' ? 'sprachbausteine2'
                          : (this.part.startsWith('teil') ? 'lesen' + this.part.slice(4) : this.part),
                    partTitle: PART_LABELS[this.part],
                    content: this.sampleContentFor(this.part),
                }],
            };
            const blob = new Blob([JSON.stringify(sample, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `sample-${this.part}.json`;
            a.click();
            URL.revokeObjectURL(url);
        },

        sampleContentFor(part) {
            switch (part) {
                case 'teil1': return {
                    headlines: [
                        { id: 'a', text: 'Headline A' },
                        { id: 'b', text: 'Headline B' }
                    ],
                    texts: [
                        { id: 1, content: 'Erster Absatz …' },
                        { id: 2, content: 'Zweiter Absatz …' }
                    ],
                    correctAnswers: { 1: 'b', 2: 'a' }
                };
                case 'teil2': return {
                    textTitle: 'Haustiere in Deutschland',
                    textContent: 'Egal ob Hund, Katze oder Maus …',
                    questions: [
                        { id: 6, text: 'Bettina Schröther …', options: ['Option a', 'Option b', 'Option c'], correct: 2 },
                        { id: 7, text: 'Marius Klotz meint …', options: ['Option a', 'Option b', 'Option c'], correct: 0 }
                    ]
                };
                case 'teil3': return {
                    textTitle: '',
                    ads: [
                        { id: 'a', title: 'Didgeridoo – der klingende Stock', text: '(Samstag …)\nErlernt werden …', summary: 'ترجمة' },
                        { id: 'b', title: 'Deutsch im Beruf', text: '(Samstag …)\nWie schreibt man …', summary: '' },
                        { id: 'x', title: '', text: 'Keine passende Anzeige', summary: '' }
                    ],
                    situations: [
                        { id: 11, text: 'Ein Freund interessiert sich für Musikinstrumente …' },
                        { id: 12, text: 'An Ihrem Arbeitsplatz müssen Sie viele deutsche Geschäftsbriefe …' }
                    ],
                    correctAnswers: { 11: 'x', 12: 'b' }
                };
                default: return { questions: [] };
            }
        },
    };
}
</script>
@endpush

@endsection
