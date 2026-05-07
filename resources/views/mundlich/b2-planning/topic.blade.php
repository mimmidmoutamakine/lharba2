@extends('layouts.app')

@section('title', $topic->label . ' | Telc B2 Mündlich | ' . config('app.name'))

@section('content')

@php
    $catColors = [
        'universal'      => ['bg-emerald-500/10', 'text-emerald-300', 'border-emerald-500/20', 'بنية موحَّدة'],
        'semi_universal' => ['bg-blue-500/10',    'text-blue-300',    'border-blue-500/20',    'بنية مشتركة'],
        'topic_specific' => ['bg-rose-500/10',    'text-rose-300',    'border-rose-500/30',    'خاص بهاد الموضوع'],
    ];
    $counts = ['universal' => 0, 'semi_universal' => 0, 'topic_specific' => 0];
    foreach ($aspekte as $a) {
        $c = $a['category'] ?? 'universal';
        $counts[$c] = ($counts[$c] ?? 0) + 1;
    }
@endphp

<div class="max-w-5xl mx-auto px-4 md:px-6 pt-28 pb-16" dir="rtl"
     x-data="{
        filter: { universal: true, semi_universal: true, topic_specific: true },
        openStructures: {},
        expandedExamples: {},
        showAspekt(cat) { return this.filter[cat] !== false; },
        toggleAll(val) { this.filter = { universal: val, semi_universal: val, topic_specific: val }; }
     }">

    {{-- Back link --}}
    <a href="{{ route('mundlich.b2-planning.index') }}"
       class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-amber-400 transition-colors mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="rotate-180"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        <span>العودة للمواضيع</span>
    </a>

    {{-- Header --}}
    <header class="mb-6">
        <div class="text-[10px] font-black uppercase tracking-[0.25em] text-amber-400 mb-2">Telc B2 · Mündlich · Teil 3 — Gemeinsam etwas planen</div>
        <h1 class="text-2xl md:text-3xl font-bold text-white" dir="ltr">{{ $topic->label }}</h1>
    </header>

    {{-- Original task --}}
    @if($topic->topic_text)
    <section class="mb-6 p-5 rounded-2xl border border-amber-500/20 bg-amber-500/[0.04]">
        <div class="flex items-center gap-2 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <h2 class="text-xs font-black uppercase tracking-widest text-amber-300">Aufgabe</h2>
        </div>
        <p class="text-[15px] text-white leading-relaxed" dir="ltr">{{ $topic->topic_text }}</p>
    </section>
    @endif

    {{-- Reminder link to Strukturen --}}
    <a href="{{ route('mundlich.b2-planning.structures') }}"
       class="block mb-6 p-3 rounded-xl border border-white/[0.06] bg-[#0B0C10] hover:border-amber-500/30 hover:bg-amber-500/[0.03] transition-all">
        <div class="flex items-center gap-3">
            <div class="shrink-0 w-9 h-9 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
            </div>
            <div class="flex-1">
                <div class="text-sm font-bold text-white">Strukturen — تدريب موحَّد</div>
                <div class="text-xs text-slate-500">راجع البنيات اللغوية مرة واحدة (تستعملها لكل المواضيع).</div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500 rotate-180"><path d="m9 18 6-6-6-6"/></svg>
        </div>
    </a>

    {{-- Filter bar --}}
    <div class="mb-4 p-3 rounded-2xl border border-white/[0.06] bg-[#0B0C10]">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="text-xs text-slate-500">{{ count($aspekte) }} aspekte لهاد الموضوع · صفّي حسب الصنف:</div>
            <div class="flex items-center gap-1.5 flex-wrap">
                @if(($counts['universal'] ?? 0) > 0)
                <button type="button" @click="filter.universal = !filter.universal"
                        :class="filter.universal ? 'bg-emerald-500/20 border-emerald-500/50 text-emerald-200' : 'bg-white/[0.02] border-white/[0.06] text-slate-600 line-through'"
                        class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-all">
                    الموحَّد <span class="opacity-70">({{ $counts['universal'] }})</span>
                </button>
                @endif
                @if(($counts['semi_universal'] ?? 0) > 0)
                <button type="button" @click="filter.semi_universal = !filter.semi_universal"
                        :class="filter.semi_universal ? 'bg-blue-500/20 border-blue-500/50 text-blue-200' : 'bg-white/[0.02] border-white/[0.06] text-slate-600 line-through'"
                        class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-all">
                    المشترك <span class="opacity-70">({{ $counts['semi_universal'] }})</span>
                </button>
                @endif
                @if(($counts['topic_specific'] ?? 0) > 0)
                <button type="button" @click="filter.topic_specific = !filter.topic_specific"
                        :class="filter.topic_specific ? 'bg-rose-500/20 border-rose-500/50 text-rose-200' : 'bg-white/[0.02] border-white/[0.06] text-slate-600 line-through'"
                        class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-all">
                    الخاص <span class="opacity-70">({{ $counts['topic_specific'] }})</span>
                </button>
                @endif
                <button type="button" @click="toggleAll(true)"
                        class="px-2.5 py-1 rounded-lg border border-white/[0.06] text-[11px] text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                    الكل
                </button>
            </div>
        </div>
    </div>

    {{-- Aspekt cards --}}
    <div class="space-y-3">
        @forelse($aspekte as $aspekt)
        @php
            $cat        = $aspekt['category'] ?? 'universal';
            $cls        = $catColors[$cat] ?? $catColors['universal'];
            $structures = $aspekt['structures_to_train'] ?? [];
            $vocab      = $topic->vocabFor($aspekt['id']);
            $aid        = $aspekt['id'];
        @endphp
        <div x-show="showAspekt('{{ $cat }}')"
             class="rounded-2xl border bg-[#111216] overflow-hidden {{ $cat === 'topic_specific' ? 'border-rose-500/30' : 'border-white/[0.08]' }}">

            {{-- Header (always visible): label + category badge --}}
            <div class="p-4 border-b border-white/[0.04]">
                <div class="flex items-start gap-3" dir="ltr">
                    <span class="px-2 h-5 flex items-center rounded text-[10px] font-bold uppercase tracking-wider {{ $cls[0] }} {{ $cls[1] }} mt-0.5 shrink-0" title="{{ $cls[3] }}">{{ str_replace('_', '-', $cat) }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-base font-bold text-white leading-snug">{{ $aspekt['label'] ?? $aid }}</div>
                        @if(! empty($aspekt['training_goal']))
                        <div class="text-xs text-slate-500 mt-1 leading-snug">{{ $aspekt['training_goal'] }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Vocabulary chips — ALWAYS visible (the topic-specific work pieces) --}}
            @if(! empty($vocab))
            <div class="p-4 bg-amber-500/[0.03]">
                <div class="flex items-center gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    <span class="text-[11px] font-black uppercase tracking-widest text-amber-300">المفردات لهاد الموضوع</span>
                </div>
                <div class="flex flex-wrap gap-1.5" dir="ltr">
                    @foreach($vocab as $v)
                    <span class="px-3 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-200 text-sm font-medium">{{ $v }}</span>
                    @endforeach
                </div>
            </div>
            @else
            <div class="px-4 py-3 bg-slate-500/[0.03] border-b border-white/[0.04]">
                <div class="text-xs text-slate-600">لا توجد مفردات خاصة لهاد الـ aspekt في هاد الموضوع — استعمل بنياتك العامة.</div>
            </div>
            @endif

            {{-- Toggle: structures (universal training material) — collapsed by default --}}
            @if(! empty($structures))
            <button @click="openStructures['{{ $aid }}'] = !openStructures['{{ $aid }}']"
                    class="w-full px-4 py-2.5 flex items-center justify-between text-right hover:bg-white/[0.02] transition-colors border-t border-white/[0.04]">
                <span class="text-[11px] text-slate-500 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-500 transition-transform" :class="openStructures['{{ $aid }}'] && 'rotate-180'"><path d="m6 9 6 6 6-6"/></svg>
                    <span x-text="openStructures['{{ $aid }}'] ? 'إخفاء البنيات' : 'عرض البنيات اللغوية ({{ count($structures) }})'"></span>
                </span>
                <span class="text-[10px] text-slate-700">reference</span>
            </button>

            <div x-show="openStructures['{{ $aid }}']" x-collapse>
                <div class="px-4 pb-4 pt-1 space-y-2">
                    @foreach($structures as $s)
                    @php $key = $aid . '::' . ($s['name'] ?? ''); @endphp
                    <div class="rounded-xl bg-[#0B0C10] border border-white/[0.05] p-3" dir="ltr">
                        @if(! empty($s['name']))
                        <div class="text-[10px] font-mono text-slate-600 mb-1.5">{{ $s['name'] }}</div>
                        @endif
                        <div class="text-[15px] text-amber-200 font-medium leading-relaxed mb-2 whitespace-pre-wrap">{{ $s['pattern'] ?? '' }}</div>
                        @if(! empty($s['examples']))
                        <button @click="expandedExamples['{{ $key }}'] = !expandedExamples['{{ $key }}']"
                                class="flex items-center gap-1.5 text-[11px] text-slate-500 hover:text-amber-400 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="expandedExamples['{{ $key }}'] && 'rotate-180'" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                            <span x-text="expandedExamples['{{ $key }}'] ? 'إخفاء الأمثلة' : 'عرض الأمثلة ({{ count($s['examples']) }})'"></span>
                        </button>
                        <div x-show="expandedExamples['{{ $key }}']" x-collapse class="mt-2 space-y-1.5 pt-2 border-t border-white/[0.04]">
                            <div class="text-[10px] text-slate-600 mb-1">⚠ الأمثلة عامة — قد تكون من موضوع آخر. استبدل المفردات بمفرداتك أعلاه.</div>
                            @foreach($s['examples'] as $ex)
                            <div class="text-sm text-slate-300 leading-snug pl-3 border-l-2 border-amber-500/30">{{ $ex }}</div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="py-16 text-center text-slate-500">
            <div class="text-sm">لا توجد بنيات مرتبطة بهاد الموضوع — تأكد من رفع ملف <code dir="ltr" class="text-amber-400">planning_structures.json</code>.</div>
        </div>
        @endforelse
    </div>

    {{-- Summary formula at bottom --}}
    @if(! empty($summaryFormula))
    <section class="mt-8 p-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.04]">
        <div class="flex items-center gap-2 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <h2 class="text-sm font-bold text-emerald-300 uppercase tracking-wider">صيغة الخلاصة في الآخر</h2>
        </div>
        @if(! empty($summaryFormula['pattern']))
        <div class="text-[15px] text-emerald-200 font-medium leading-relaxed mb-3 whitespace-pre-wrap" dir="ltr">{{ $summaryFormula['pattern'] }}</div>
        @endif
        @if(! empty($summaryFormula['example']))
        <div class="text-sm text-slate-300 leading-snug pl-3 border-l-2 border-emerald-500/30" dir="ltr">{{ $summaryFormula['example'] }}</div>
        @endif
    </section>
    @endif

</div>

@endsection
