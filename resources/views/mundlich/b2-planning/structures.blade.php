@extends('layouts.app')

@section('title', 'Strukturen — Gemeinsam etwas planen | ' . config('app.name'))
@section('description', 'البنيات اللغوية الموحَّدة لـ Telc B2 Mündlich Teil 3.')

@section('content')

@php
    $counts = ['universal' => 0, 'semi_universal' => 0];
    foreach ($aspekte as $a) {
        $c = $a['category'] ?? 'universal';
        if (isset($counts[$c])) $counts[$c]++;
    }
@endphp

<div class="max-w-5xl mx-auto px-4 md:px-6 pt-28 pb-16" dir="rtl"
     x-data="{
        openId: null,
        expandedExamples: {},
        filter: { universal: true, semi_universal: true },
        showAspekt(cat) { return this.filter[cat] !== false; }
     }">

    {{-- Back link --}}
    <a href="{{ route('mundlich.b2-planning.index') }}"
       class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-amber-400 transition-colors mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="rotate-180"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        <span>العودة للمواضيع</span>
    </a>

    {{-- Header --}}
    <header class="mb-8">
        <div class="text-[10px] font-black uppercase tracking-[0.25em] text-amber-400 mb-2">Telc B2 · Mündlich · Teil 3</div>
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Strukturen — Gemeinsam etwas planen</h1>
        <p class="text-slate-400 leading-relaxed max-w-3xl">
            هاد الصفحة فيها كل البنيات اللغوية اللي خاصك تتعلمها <strong class="text-amber-300">مرة واحدة</strong>. منين تجي للموضوع، حتى تبدّل الكلمات (المفردات) — البنية كتبقى نفسها.
        </p>
    </header>

    {{-- Conversation flow --}}
    @if(! empty($conversationFlow))
    <section class="mb-8 p-5 rounded-2xl border border-amber-500/20 bg-amber-500/[0.04]">
        <div class="flex items-center gap-2 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            <h2 class="text-sm font-bold text-amber-300 uppercase tracking-wider">ترتيب المحادثة</h2>
        </div>
        <ol class="space-y-1.5 text-sm text-slate-200" dir="ltr">
            @foreach($conversationFlow as $step)
            <li class="leading-snug">{{ $step }}</li>
            @endforeach
        </ol>
    </section>
    @endif

    {{-- Filter bar --}}
    <div class="mb-4 p-3 rounded-2xl border border-white/[0.06] bg-[#0B0C10]">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="text-xs text-slate-500">{{ count($aspekte) }} aspekte · صفّي حسب الصنف:</div>
            <div class="flex items-center gap-1.5 flex-wrap">
                @if($counts['universal'] > 0)
                <button type="button" @click="filter.universal = !filter.universal"
                        :class="filter.universal ? 'bg-emerald-500/20 border-emerald-500/50 text-emerald-200' : 'bg-white/[0.02] border-white/[0.06] text-slate-600 line-through'"
                        class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-all">
                    الموحَّد <span class="opacity-70">({{ $counts['universal'] }})</span>
                </button>
                @endif
                @if($counts['semi_universal'] > 0)
                <button type="button" @click="filter.semi_universal = !filter.semi_universal"
                        :class="filter.semi_universal ? 'bg-blue-500/20 border-blue-500/50 text-blue-200' : 'bg-white/[0.02] border-white/[0.06] text-slate-600 line-through'"
                        class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-all">
                    المشترك <span class="opacity-70">({{ $counts['semi_universal'] }})</span>
                </button>
                @endif
                <button type="button" @click="filter = { universal: true, semi_universal: true }"
                        class="px-2.5 py-1 rounded-lg border border-white/[0.06] text-[11px] text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                    الكل
                </button>
            </div>
        </div>
    </div>

    <div class="space-y-3 mb-8">
        @foreach($aspekte as $aspekt)
        @php
            $cat = $aspekt['category'] ?? 'universal';
            $catColors = [
                'universal'      => ['bg-emerald-500/10', 'text-emerald-300', 'border-emerald-500/20'],
                'semi_universal' => ['bg-blue-500/10',    'text-blue-300',    'border-blue-500/20'],
            ];
            $cls = $catColors[$cat] ?? $catColors['universal'];
            $structures = $aspekt['structures_to_train'] ?? [];
        @endphp
        <div x-show="showAspekt('{{ $cat }}')" class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden" dir="ltr">
            <button @click="openId = openId === '{{ $aspekt['id'] }}' ? null : '{{ $aspekt['id'] }}'"
                    class="w-full p-4 flex items-start gap-3 text-left hover:bg-white/[0.02] transition-colors">
                <span class="px-2 h-5 flex items-center rounded text-[10px] font-bold uppercase tracking-wider {{ $cls[0] }} {{ $cls[1] }} mt-0.5">{{ str_replace('_', '-', $cat) }}</span>
                <div class="flex-1">
                    <div class="text-base font-bold text-white leading-snug">{{ $aspekt['label'] ?? $aspekt['id'] }}</div>
                    @if(! empty($aspekt['training_goal']))
                    <div class="text-xs text-slate-500 mt-1 leading-snug">{{ $aspekt['training_goal'] }}</div>
                    @endif
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="shrink-0 mt-1 text-slate-500 transition-transform" :class="openId === '{{ $aspekt['id'] }}' && 'rotate-180'"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            <div x-show="openId === '{{ $aspekt['id'] }}'" x-collapse>
                <div class="px-4 pb-4 pt-1 border-t border-white/[0.04] space-y-3">
                    @foreach($structures as $s)
                    @php
                        $key = $aspekt['id'] . '::' . ($s['name'] ?? '');
                    @endphp
                    <div class="rounded-xl bg-[#0B0C10] border border-white/[0.05] p-3">
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
                            @foreach($s['examples'] as $ex)
                            <div class="text-sm text-slate-300 leading-snug pl-3 border-l-2 border-amber-500/30">{{ $ex }}</div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Summary formula --}}
    @if(! empty($summaryFormula))
    <section class="p-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.04]" dir="ltr">
        <div class="flex items-center gap-2 mb-3" dir="rtl">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <h2 class="text-sm font-bold text-emerald-300 uppercase tracking-wider">صيغة الخلاصة في الآخر</h2>
        </div>
        @if(! empty($summaryFormula['pattern']))
        <div class="text-[15px] text-emerald-200 font-medium leading-relaxed mb-3 whitespace-pre-wrap">{{ $summaryFormula['pattern'] }}</div>
        @endif
        @if(! empty($summaryFormula['example']))
        <div class="text-sm text-slate-300 leading-snug pl-3 border-l-2 border-emerald-500/30">{{ $summaryFormula['example'] }}</div>
        @endif
    </section>
    @endif

</div>

@endsection
