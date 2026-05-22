@extends('layouts.app')

@section('title', 'Goethe B1 Lesen | ' . config('app.name'))
@section('description', 'Goethe B1 Leseverstehen — interaktive Übungen mit sofortigem Feedback.')

@push('head')
<style>
    /* Sliding teil-filter indicator. Class added after first paint so the initial
       position snap is instant, not animated from origin. */
    .lharba-teil-indicator--animate {
        transition: transform 400ms cubic-bezier(0.85, 0, 0.15, 1),
                    width     400ms cubic-bezier(0.85, 0, 0.15, 1),
                    height    400ms cubic-bezier(0.85, 0, 0.15, 1),
                    opacity   200ms ease;
    }

    /* Card entry — opacity+transform only (mobile GPU-friendly).
       Removed `content-visibility: auto` + `contain-intrinsic-size` — they caused
       iOS Safari to reserve 220px placeholder boxes that never got promoted to
       actual painted content, producing visible empty rectangles while scrolling. */
    @media (prefers-reduced-motion: no-preference) {
        @keyframes lharba-card-in {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .card-stagger {
            animation: lharba-card-in 280ms ease-out
                       calc(var(--card-i, 0) * 22ms) backwards;
        }
    }
</style>
@endpush

@section('content')

@php
    // "الكل" (All) option removed — heavy code path that crashed slow devices.
    // Default landing now goes to Teil 1 server-side.
    $teilOptions = [
        'teil1' => ['T1', 'Teil 1'],
        'teil2' => ['T2', 'Teil 2'],
        'teil3' => ['T3', 'Teil 3'],
        'teil4' => ['T4', 'Teil 4'],
        'teil5' => ['T5', 'Teil 5'],
    ];
    $teilFullNames = [
        'teil1' => 'Teil 1 · Richtig/Falsch',
        'teil2' => 'Teil 2 · Multiple Choice',
        'teil3' => 'Teil 3 · Zuordnung',
        'teil4' => 'Teil 4 · Dafür/Dagegen',
        'teil5' => 'Teil 5 · Multiple Choice',
    ];
    $teilDurations = [
        'teil1' => 15,
        'teil2' => 15,
        'teil3' => 15,
        'teil4' => 10,
        'teil5' => 10,
    ];
    // One card per topic for the active teil. Default-to-teil1 in controller
    // guarantees $teil is always set, so DOM caps at ~40 cards (1 per topic).
    $cardItems = [];
    foreach ($topics ?? [] as $t) {
        if (! $t->{'has_' . $teil}) continue;
        $cardItems[] = [$t, $teil];
    }
@endphp

<div class="max-w-7xl mx-auto px-4 md:px-6 pt-28 pb-16"
     x-data="goetheB1LesenIndex({{ json_encode($teil ?? '') }})"
     x-init="init()">

    {{-- Header --}}
    <div class="mb-10" dir="rtl">
        <div class="inline-flex items-center gap-2 text-amber-400 font-bold mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 7v14M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>
            </svg>
            <span>Goethe B1 · Lesen – القراءة</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">اختار الاختبار وابدأ التدريب</h1>
        <p class="text-slate-400 max-w-2xl">كل اختبار فيه 5 أجزاء: Teil 1 (Richtig/Falsch), Teil 2 (Multiple Choice — 2 نصوص), Teil 3 (Zuordnung), Teil 4 (Dafür / Dagegen), Teil 5 (Multiple Choice).</p>
    </div>

    {{-- Teil filter — server-side via URL navigation (keeps DOM small on iOS). --}}
    <div class="mb-6 p-3 md:p-5 rounded-2xl border border-white/[0.06] bg-gradient-to-br from-white/[0.03] to-transparent backdrop-blur-sm shadow-lg shadow-black/20">
        <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-2" dir="rtl">الجزء (Teil)</div>
        <div class="grid grid-cols-5 md:flex md:items-center gap-0.5 p-1 rounded-2xl bg-black/30 border border-white/[0.06] shadow-inner shadow-black/30" dir="rtl">
            @foreach($teilOptions as $val => [$short, $full])
            @php
                $isActive = $teil === $val;
                $href = route('goethe-b1.lesen.index', ['teil' => $val]);
            @endphp
            <a href="{{ $href }}"
               @class([
                    'relative z-10 px-2 md:px-4 py-1.5 md:py-2 rounded-xl text-[12px] md:text-sm font-semibold whitespace-nowrap transition-all duration-200 text-center',
                    'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/30' => $isActive,
                    'text-slate-400 hover:text-white' => ! $isActive,
               ])>
                <span class="md:hidden">{{ $short }}</span>
                <span class="hidden md:inline">{{ $full }}</span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Count (server-side; pagination total) --}}
    <div class="flex items-center gap-2 mb-4 text-xs text-slate-500" dir="rtl">
        <span class="font-bold text-white text-base tabular-nums">{{ $topics->total() }}</span>
        <span>تمرين</span>
        <span class="px-2 py-0.5 rounded-md bg-amber-500/15 border border-amber-500/30 text-amber-300 font-bold">{{ $teilOptions[$teil][1] ?? $teil }}</span>
    </div>

    {{-- Cards --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
        @forelse($cardItems as $idx => [$topic, $teilKey])
        @php
            $cardKey  = $topic->id . '-' . $teilKey;
            $href     = route('goethe-b1.lesen.topic', ['slug' => $topic->slug, 'teil' => $teilKey]);
        @endphp
        <div data-card-teil="{{ $teilKey }}"
             style="--card-i: {{ min($idx, 24) }};"
             class="card-stagger group relative p-3.5 rounded-2xl border bg-[#111216] flex flex-col gap-2.5 transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-xl hover:bg-[#13141A]"
             :class="statusBorder('{{ $cardKey }}')"
             dir="ltr">
            <div class="relative flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-amber-400 truncate">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M12 7v14M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                    <span>Goethe B1</span>
                    <span class="text-slate-700">·</span>
                    <span class="text-slate-400">{{ $teilFullNames[$teilKey] }}</span>
                </span>
                <span class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-black border bg-amber-500/10 border-amber-500/30 text-amber-300">B1</span>
            </div>

            <div class="relative">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="font-bold text-white text-base leading-snug">{{ $topic->title }}</h3>
                    @include('partials.topic-tag.badge', ['tag' => $topic->topicTag])
                </div>
                @if($topic->title_ar)
                <p class="text-xs text-slate-500 mt-0.5" dir="rtl">{{ $topic->title_ar }}</p>
                @endif
            </div>

            <div class="relative flex items-center justify-between gap-3">
                <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    {{ $teilDurations[$teilKey] }} min
                </span>
                <div class="flex items-center gap-1">
                    <button type="button" @click.prevent="toggleStatus('{{ $cardKey }}', 'mastered')"
                            class="inline-flex items-center gap-1 h-7 px-2 rounded-md border text-[10px] font-bold transition-all active:scale-95"
                            :class="statuses['{{ $cardKey }}'] === 'mastered' ? 'bg-emerald-500/20 border-emerald-400/50 text-emerald-200' : 'border-white/[0.06] text-slate-500 hover:text-emerald-300 hover:border-emerald-500/30'"
                            title="حفظتها">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>حفظت</span>
                    </button>
                    <button type="button" @click.prevent="toggleStatus('{{ $cardKey }}', 'revise')"
                            class="inline-flex items-center gap-1 h-7 px-2 rounded-md border text-[10px] font-bold transition-all active:scale-95"
                            :class="statuses['{{ $cardKey }}'] === 'revise' ? 'bg-amber-500/20 border-amber-400/50 text-amber-200' : 'border-white/[0.06] text-slate-500 hover:text-amber-300 hover:border-amber-500/30'"
                            title="عاود راجعها">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                        <span>راجع</span>
                    </button>
                </div>
            </div>

            <div class="relative flex items-center gap-1.5 mt-auto pt-2 border-t border-white/[0.05]">
                <button type="button" @click.prevent="toggleTimer('{{ $cardKey }}')"
                        :class="timer['{{ $cardKey }}'] ? 'border-amber-500/40 bg-amber-500/10 text-amber-300' : 'border-white/[0.08] text-slate-500 hover:text-white hover:border-white/20'"
                        class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg border transition-all"
                        :title="timer['{{ $cardKey }}'] ? 'مؤقت — تسليم تلقائي' : 'بدون مؤقت'">
                    <template x-if="timer['{{ $cardKey }}']">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2"/><path d="M5 3 2 6"/><path d="m22 6-3-3"/></svg>
                    </template>
                    <template x-if="!timer['{{ $cardKey }}']">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7"/><path d="M12 17a5 5 0 0 1-5-5"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
                    </template>
                </button>

                <div class="flex-1"></div>

                <a :href="startHref('{{ $href }}', '{{ $cardKey }}')"
                   class="inline-flex items-center gap-1.5 px-3.5 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white text-[12px] font-bold shadow-md shadow-amber-500/30 hover:shadow-lg hover:shadow-amber-500/40 active:scale-95 transition-all">
                    ابدأ
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            </div>
        </div>
        @empty
        @endforelse

        {{-- Empty state (server-rendered) --}}
        @if(empty($cardItems))
        <div class="sm:col-span-2 lg:col-span-3 text-center py-12 text-slate-500 text-sm" dir="rtl">
            <p>ما كاينش تمارين فهاد الجزء حاليا.</p>
            @if($teil !== 'teil1')
            <a href="{{ route('goethe-b1.lesen.index') }}" class="inline-block text-xs mt-2 text-amber-400 hover:text-amber-300 transition-colors">
                رجع لـ Teil 1
            </a>
            @endif
        </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if($topics->hasPages())
    <div class="mt-6">
        {{ $topics->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
// Slim Alpine component — filter is now URL-driven, only keep what's needed for
// localStorage-backed per-card state (status badges + per-card timer toggle).
function goetheB1LesenIndex(initialTeil) {
    return {
        statuses: {},
        timer: {},

        init() {
            this.statuses = (window.TopicStatus?.all()['goethe-b1-lesen']) || {};
            try {
                const t = JSON.parse(localStorage.getItem('goethe-b1-lesen-timer') || '{}');
                this.timer = t && typeof t === 'object' ? t : {};
            } catch (e) { this.timer = {}; }
            window.addEventListener('topic-status-changed', (e) => {
                if (e.detail?.skill === 'goethe-b1-lesen') {
                    this.statuses = window.TopicStatus.all()['goethe-b1-lesen'] || {};
                }
            });
        },

        toggleStatus(key, value) {
            window.TopicStatus?.toggle('goethe-b1-lesen', key, value);
        },

        statusBorder(key) {
            const v = this.statuses[key];
            if (v === 'mastered') return 'border-emerald-500/40 hover:border-emerald-400/60 hover:shadow-emerald-500/10';
            if (v === 'revise')   return 'border-amber-500/40 hover:border-amber-400/60 hover:shadow-amber-500/10';
            return 'border-white/[0.08] hover:border-amber-500/30 hover:shadow-amber-500/10';
        },

        toggleTimer(key) {
            this.timer[key] = !this.timer[key];
            this.timer = { ...this.timer };
            try { localStorage.setItem('goethe-b1-lesen-timer', JSON.stringify(this.timer)); } catch (e) {}
        },

        startHref(baseHref, key) {
            return this.timer[key] ? (baseHref + (baseHref.includes('?') ? '&' : '?') + 'timer=1') : baseHref;
        },
    };
}
</script>
@endpush

@endsection
