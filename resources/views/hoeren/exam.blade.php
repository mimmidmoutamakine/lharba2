@extends('layouts.app')

@section('title', $exam->title . ' · Hören Teil ' . $teilNum . ' | ' . config('app.name'))

@section('content')
@php
    // Payload for Alpine: statements + nav siblings + audio + persistence key.
    $statementsPayload = $statements->map(fn ($s) => [
        'id'      => $s->id,
        'text'    => $s->text,
        'answer'  => $s->answer,            // '+' or '-'
        'hl'      => $s->highlights ?? [],
        'expHl'   => $s->explanation_highlights ?? [],
    ])->values();

    $siblingsPayload = $siblings->map(fn ($e) => [
        'slug' => $e->slug,
        'title'=> $e->title,
    ])->values();

    $storageKey = "lh.hoeren.{$level}.t{$teilNum}.{$exam->slug}";
@endphp

<div class="max-w-4xl mx-auto px-3 md:px-6 pt-24 md:pt-28 pb-12"
     x-data="hoerenExam({{ Js::from([
         'statements'  => $statementsPayload,
         'examTitle'   => $exam->title,
         'audioUrl'    => $audioUrl,
         'storageKey'  => $storageKey,
         'teilKey'     => $teilKey,
         'imtihanatUrl'=> route('hoeren.imtihanat', ['teil' => $teilKey]),
         'siblings'    => $siblingsPayload,
         'prevUrl'     => $prev ? route('hoeren.exam', ['teil' => $teilKey, 'exam' => $prev->slug]) : null,
         'nextUrl'     => $next ? route('hoeren.exam', ['teil' => $teilKey, 'exam' => $next->slug]) : null,
     ]) }})"
     x-init="init()">

    {{-- Top bar: back, audio status, navigation --}}
    <div class="mb-5 flex items-center gap-2 flex-wrap" dir="rtl">
        <a href="{{ route('hoeren.imtihanat', ['teil' => $teilKey]) }}"
           class="w-9 h-9 flex items-center justify-center rounded-full bg-[#111216] border border-white/10 text-slate-400 hover:text-white hover:bg-[#13141A] transition-all"
           title="رجوع للائحة الإمتحانات">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        <span class="text-xs text-slate-500">Hören · Teil {{ $teilNum }} · {{ $level }}</span>

        {{-- Sibling dropdown — searchable, self-scrolling (overscroll-contain stops
             the page from scrolling under the open list on iOS). --}}
        <div class="relative ml-auto" @click.outside="navOpen = false; examQ = ''"
             @keydown.escape.window="navOpen = false; examQ = ''">
            <button @click="navOpen = !navOpen; if (navOpen) $nextTick(() => $refs.examSearch?.focus())"
                    class="px-3 h-9 flex items-center gap-1.5 rounded-full bg-[#111216] border border-white/10 text-xs font-bold text-white hover:bg-[#13141A] transition-all"
                    title="اختار امتحان آخر">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                <span>اختر امتحان</span>
            </button>
            <div x-show="navOpen" x-cloak x-transition
                 class="absolute top-full mt-2 right-0 w-[min(320px,calc(100vw-1.5rem))] bg-[#0B0C10] border border-white/10 rounded-2xl shadow-2xl shadow-black/60 z-30 overflow-hidden">

                {{-- Search bar at the top of the dropdown --}}
                <div class="p-2 border-b border-white/[0.06]">
                    <div class="relative">
                        <input type="search" x-model="examQ" x-ref="examSearch"
                               placeholder="بحث عن امتحان..."
                               class="w-full pl-7 pr-2.5 h-8 rounded-lg bg-black/40 border border-white/10 text-xs text-white placeholder:text-slate-600 focus:outline-none focus:border-amber-500" dir="rtl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-500"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                </div>

                {{-- Scrollable list. Explicit max-height in inline style so it
                     wins over anything; no flex/min-h-0 negotiation. --}}
                <div class="p-1"
                     style="max-height: 50vh; overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">
                    <template x-for="s in filteredSiblings()" :key="s.slug">
                        <a :href="'{{ route('hoeren.exam', ['teil' => $teilKey, 'exam' => '__SLUG__']) }}'.replace('__SLUG__', s.slug)"
                           class="block px-2.5 py-2 rounded-lg text-xs text-slate-300 hover:bg-white/5 hover:text-white transition-colors"
                           :class="s.slug === '{{ $exam->slug }}' ? 'bg-amber-500/15 text-amber-200 font-bold' : ''"
                           x-text="s.title"></a>
                    </template>
                    <div x-show="filteredSiblings().length === 0"
                         class="px-3 py-6 text-center text-[11px] text-slate-600" dir="rtl">
                        ما لقيناش حتى امتحان.
                    </div>
                </div>
            </div>
        </div>

        @if($prev)
        <a href="{{ route('hoeren.exam', ['teil' => $teilKey, 'exam' => $prev->slug]) }}"
           class="px-3 h-9 flex items-center gap-1 rounded-full bg-[#111216] border border-white/10 text-xs font-bold text-slate-300 hover:text-white hover:bg-[#13141A] transition-all" title="السابق">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            <span class="hidden sm:inline">السابق</span>
        </a>
        @endif
        @if($next)
        <a href="{{ route('hoeren.exam', ['teil' => $teilKey, 'exam' => $next->slug]) }}"
           class="px-3 h-9 flex items-center gap-1 rounded-full bg-[#111216] border border-white/10 text-xs font-bold text-slate-300 hover:text-white hover:bg-[#13141A] transition-all" title="التالي">
            <span class="hidden sm:inline">التالي</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        @endif
    </div>

    {{-- Title + key info (compact) --}}
    <h1 class="text-lg md:text-xl font-bold text-white leading-tight" dir="auto">{{ $exam->title }}</h1>
    <div class="text-[11px] text-slate-500 mt-1 mb-3" dir="rtl">
        جاوب بـ <span class="text-emerald-300 font-bold">+</span> (richtig) أو <span class="text-red-300 font-bold">−</span> (falsch)
    </div>

    {{-- Audio (or "not available") — slim, no oversized header --}}
    @if($audioUrl)
    <div class="mb-3" dir="ltr">
        <audio controls preload="metadata" class="w-full h-10 rounded-xl">
            <source src="{{ $audioUrl }}">
            متصفحك ما كيدعمش الصوت.
        </audio>
    </div>
    @else
    <div class="mb-3 px-3 py-1.5 rounded-lg border border-slate-700/60 bg-slate-800/30 text-center text-[11px] text-slate-500" dir="rtl">
        الملف الصوتي غير متوفر
    </div>
    @endif

    {{-- Controls bar: shuffle (real working toggle) + answered counter + reset.
         Compact: 32px tall, fits a phone width with room to spare. --}}
    <div class="mb-3 flex items-center gap-2 flex-wrap" dir="rtl">
        {{-- Shuffle: whole button IS the toggle. Reactive class swap; no broken peer-checked. --}}
        <button type="button" @click="shuffle = !shuffle; onShuffleToggle()"
                class="inline-flex items-center gap-1.5 px-3 h-8 rounded-lg border text-xs font-bold transition-all active:scale-95"
                :class="shuffle
                    ? 'border-amber-500/50 bg-amber-500/15 text-amber-200'
                    : 'border-white/10 text-slate-400 hover:text-white hover:border-white/20'"
                :title="shuffle ? 'الترتيب عشوائي · ضغط للتعطيل' : 'الترتيب أصلي · ضغط للتفعيل'">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/><line x1="4" y1="4" x2="9" y2="9"/></svg>
            <span>عشوائي</span>
            <span class="text-[9px] opacity-70" x-text="shuffle ? '✓' : '✕'"></span>
        </button>

        {{-- Answered counter (live) --}}
        <div class="inline-flex items-center gap-1 px-2.5 h-8 rounded-lg bg-black/30 border border-white/[0.06] text-xs">
            <span class="font-mono font-bold text-white tabular-nums" x-text="answeredCount()"></span>
            <span class="text-slate-500">/</span>
            <span class="font-mono text-slate-400 tabular-nums">{{ $statements->count() }}</span>
        </div>

        <button @click="reset()" type="button"
                class="ml-auto inline-flex items-center gap-1 px-2.5 h-8 rounded-lg border border-white/10 text-[11px] font-bold text-slate-400 hover:text-white hover:border-white/20 transition-all"
                title="إعادة الترتيب ومسح الإجابات">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
            عاود
        </button>
    </div>

    {{-- Slim score banner (after submit). Tap it to re-open the result overlay. --}}
    <template x-if="submitted">
        <button type="button" @click="resultOpen = true"
                class="w-full mb-3 px-3 py-2.5 rounded-xl flex items-center justify-between gap-3 transition-all hover:brightness-110 cursor-pointer"
                :class="score === total ? 'bg-emerald-500/10 border border-emerald-500/25' : score >= total/2 ? 'bg-amber-500/10 border border-amber-500/25' : 'bg-red-500/10 border border-red-500/25'">
            <div class="flex items-center gap-2" dir="rtl">
                <span class="text-lg" x-text="scoreEmoji"></span>
                <div class="text-right">
                    <div class="font-bold text-white text-sm" x-text="score === total ? 'كل الأجوبة صحيحة!' : score + '/' + total + ' صحيح'"></div>
                    <div class="text-[10px] text-slate-400 leading-tight">تفقد النتيجة</div>
                </div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        </button>
    </template>

    {{-- Statements list — compact single-row layout (text + buttons inline).
         Tight padding, smaller text, smaller buttons. Wraps gracefully on narrow screens. --}}
    <ol class="space-y-1.5" dir="ltr">
        <template x-for="(s, idx) in displayed" :key="s.id">
            <li class="rounded-xl border bg-[#111216] py-2 pl-2.5 pr-2 flex items-start gap-2.5 transition-colors"
                :class="submitted
                    ? (answers[s.id] === s.answer ? 'border-emerald-500/30 bg-emerald-500/[0.03]' : (answers[s.id] ? 'border-red-500/40 bg-red-500/[0.05]' : 'border-amber-500/30'))
                    : (answers[s.id] ? 'border-amber-500/30' : 'border-white/[0.08]')">

                {{-- Number badge --}}
                <span class="shrink-0 w-6 h-6 mt-0.5 rounded-md bg-amber-500/15 text-amber-300 font-bold text-[10px] flex items-center justify-center" x-text="idx + 1"></span>

                {{-- Statement text. After submit, the green/red highlight on the
                     buttons already shows what the right answer was. No need for
                     a separate "الصحيح" text row. --}}
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="text-[13.5px] md:text-sm text-slate-100 leading-snug" x-text="s.text"></p>
                </div>

                {{-- R / F buttons — compact, side-by-side, vertically centered with text --}}
                <div class="shrink-0 flex items-center gap-1 pt-0">
                    <button type="button" @click="!submitted && (answers[s.id] = '-')"
                            :disabled="submitted"
                            aria-label="Falsch"
                            class="w-8 h-8 md:w-9 md:h-9 flex items-center justify-center rounded-md border text-base md:text-lg font-black leading-none transition-all active:scale-95 disabled:cursor-not-allowed"
                            :class="answers[s.id] === '-'
                                ? (submitted
                                    ? (s.answer === '-' ? 'bg-emerald-500/25 border-emerald-400/60 text-emerald-200' : 'bg-red-500/30 border-red-400/70 text-red-100')
                                    : 'bg-red-500/20 border-red-400/50 text-red-200')
                                : 'border-white/10 text-slate-500 hover:text-red-300 hover:border-red-500/30'">
                        −
                    </button>
                    <button type="button" @click="!submitted && (answers[s.id] = '+')"
                            :disabled="submitted"
                            aria-label="Richtig"
                            class="w-8 h-8 md:w-9 md:h-9 flex items-center justify-center rounded-md border text-base md:text-lg font-black leading-none transition-all active:scale-95 disabled:cursor-not-allowed"
                            :class="answers[s.id] === '+'
                                ? (submitted
                                    ? (s.answer === '+' ? 'bg-emerald-500/25 border-emerald-400/60 text-emerald-200' : 'bg-red-500/30 border-red-400/70 text-red-100')
                                    : 'bg-emerald-500/20 border-emerald-400/50 text-emerald-200')
                                : 'border-white/10 text-slate-500 hover:text-emerald-300 hover:border-emerald-500/30'">
                        +
                    </button>
                </div>
            </li>
        </template>
    </ol>

    {{-- Submit button — sticky to the bottom of the viewport, slim. --}}
    <div class="mt-4 sticky bottom-3 z-10">
        <template x-if="!submitted">
            <button @click="submit()" type="button"
                    :disabled="answeredCount() === 0"
                    class="w-full px-5 py-2.5 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white font-bold text-sm shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <span x-text="answeredCount() + '/' + total + ' — تصحيح'"></span>
            </button>
        </template>
        <template x-if="submitted">
            <div class="grid grid-cols-2 gap-2">
                <button @click="reset()" type="button"
                        class="px-4 py-2.5 rounded-xl border border-white/10 bg-[#111216] text-white text-xs font-bold hover:bg-[#13141A] transition-all">
                    محاولة جديدة
                </button>
                @if($next)
                <a href="{{ route('hoeren.exam', ['teil' => $teilKey, 'exam' => $next->slug]) }}"
                   class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold text-center transition-all">
                    التالي →
                </a>
                @else
                <a href="{{ route('hoeren.imtihanat', ['teil' => $teilKey]) }}"
                   class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold text-center transition-all">
                    رجوع للائحة
                </a>
                @endif
            </div>
        </template>
    </div>

    {{-- ── Result overlay ───────────────────────────────────────────────
         Pops on submit. Backdrop click or X dismisses to reveal graded
         statements behind. Re-openable from the slim score banner above. --}}
    <div x-show="resultOpen" x-cloak
         x-transition.opacity
         @keydown.escape.window="resultOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
         @click.self="resultOpen = false">

        <div x-show="resultOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             class="relative w-full max-w-sm rounded-3xl border bg-[#0B0C10] shadow-2xl shadow-black/60 overflow-hidden"
             :class="{
                'border-emerald-500/40': scoreTone === 'emerald',
                'border-amber-500/40':   scoreTone === 'amber',
                'border-red-500/40':     scoreTone === 'red',
             }">

            {{-- Header strip — colored bar based on score --}}
            <div class="h-1.5"
                 :class="{
                    'bg-gradient-to-r from-emerald-500 to-emerald-600': scoreTone === 'emerald',
                    'bg-gradient-to-r from-amber-500 to-orange-600':    scoreTone === 'amber',
                    'bg-gradient-to-r from-red-500 to-red-700':         scoreTone === 'red',
                 }"></div>

            {{-- Close (X) --}}
            <button @click="resultOpen = false"
                    class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center rounded-full text-slate-500 hover:text-white hover:bg-white/5 transition-all"
                    aria-label="إغلاق">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>

            <div class="px-6 pt-8 pb-6 text-center" dir="rtl">
                {{-- Emoji + percentage ring --}}
                <div class="mb-4 inline-flex flex-col items-center">
                    <div class="text-5xl mb-1" x-text="scoreEmoji"></div>
                    <div class="text-4xl font-black text-white tabular-nums">
                        <span x-text="scorePct"></span><span class="text-xl text-slate-500">%</span>
                    </div>
                </div>

                {{-- Score breakdown --}}
                <div class="text-lg font-bold text-white mb-1">
                    <span x-text="score"></span> / <span x-text="total"></span>
                    <span class="text-sm font-normal text-slate-400 mr-1">إجابة صحيحة</span>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed mb-6" x-text="scoreMessage"></p>

                {{-- Wrong answers count (only if any) --}}
                <template x-if="score < total">
                    <div class="mb-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-500/10 border border-red-500/30 text-red-200 text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        <span class="font-bold"><span x-text="total - score"></span> خاطئة</span>
                        <span class="opacity-70">— معلّمة بالأحمر تحت</span>
                    </div>
                </template>

                {{-- Actions --}}
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <button @click="reset()" type="button"
                            class="px-4 py-2.5 rounded-xl border border-white/10 bg-[#111216] text-white text-sm font-bold hover:bg-[#13141A] active:scale-95 transition-all inline-flex items-center justify-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                        محاولة جديدة
                    </button>
                    @if($next)
                    <a href="{{ route('hoeren.exam', ['teil' => $teilKey, 'exam' => $next->slug]) }}"
                       class="px-4 py-2.5 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white text-sm font-bold text-center hover:shadow-lg hover:shadow-amber-500/30 active:scale-95 transition-all inline-flex items-center justify-center gap-1.5">
                        التالي
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
                    @else
                    <a href="{{ route('hoeren.imtihanat', ['teil' => $teilKey]) }}"
                       class="px-4 py-2.5 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white text-sm font-bold text-center hover:shadow-lg hover:shadow-amber-500/30 active:scale-95 transition-all">
                        رجوع للائحة
                    </a>
                    @endif
                </div>

                {{-- Dismiss to review --}}
                <button @click="resultOpen = false" type="button"
                        class="mt-3 text-xs text-slate-500 hover:text-white transition-colors">
                    راجع الإجابات تحت
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function hoerenExam(config) {
    return {
        statements: config.statements,
        siblings:   config.siblings,
        storageKey: config.storageKey,
        audioUrl:   config.audioUrl,

        displayed: [],       // statements in current order (shuffled or not)
        answers:   {},       // { [statementId]: '+' | '-' }
        submitted: false,
        score:     0,
        total:     config.statements.length,
        shuffle:   true,     // default: randomize order each attempt
        navOpen:   false,
        examQ:     '',       // search filter for the exam dropdown
        resultOpen: false,   // result overlay shown right after submit

        init() {
            this._loadShufflePref();
            this._loadState();
            this._buildDisplayed();
            // Persist answers + submit state on change.
            this.$watch('answers',   () => this._saveState());
            this.$watch('submitted', () => this._saveState());
        },

        // ── Order management ───────────────────────────────────────
        _buildDisplayed() {
            if (this.shuffle && !this.submitted) {
                this.displayed = this._shuffled(this.statements);
            } else {
                this.displayed = [...this.statements];
            }
        },
        _shuffled(arr) {
            const a = [...arr];
            for (let i = a.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [a[i], a[j]] = [a[j], a[i]];
            }
            return a;
        },
        onShuffleToggle() {
            try { localStorage.setItem(this.storageKey + ':shuffle', this.shuffle ? '1' : '0'); } catch (e) {}
            // Only rebuild order if user hasn't submitted yet (don't yank rendered order after grading).
            if (!this.submitted) this._buildDisplayed();
        },
        _loadShufflePref() {
            try {
                const v = localStorage.getItem(this.storageKey + ':shuffle');
                if (v !== null) this.shuffle = v === '1';
            } catch (e) {}
        },

        // ── Exam dropdown filter ───────────────────────────────────
        filteredSiblings() {
            const q = (this.examQ || '').trim().toLowerCase();
            if (!q) return this.siblings;
            return this.siblings.filter(s => (s.title || '').toLowerCase().includes(q));
        },

        // ── Answering ──────────────────────────────────────────────
        answeredCount() { return Object.keys(this.answers).length; },

        submit() {
            this.score = 0;
            for (const s of this.statements) {
                if (this.answers[s.id] === s.answer) this.score++;
            }
            this.submitted = true;
            this.resultOpen = true; // pop the overlay
        },

        reset() {
            this.answers = {};
            this.submitted = false;
            this.score = 0;
            this.resultOpen = false;
            this._clearState();
            this._buildDisplayed(); // re-shuffle if enabled
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        // ── Result overlay helpers ─────────────────────────────────
        get scorePct() {
            return this.total ? Math.round((this.score / this.total) * 100) : 0;
        },
        get scoreEmoji() {
            const p = this.scorePct;
            if (this.score === this.total)  return '🎉';
            if (p >= 80) return '🥷🏻';
            if (p >= 60) return '👍';
            if (p >= 40) return '🤦🏻‍♂️';
            return '☠️';
        },
        get scoreMessage() {
            const p = this.scorePct;
            if (this.score === this.total)  return 'كل الأجوبة صحيحة! ممتاز.';
            if (p >= 80) return 'قريب من الكمال — راجع الخاطئة.';
            if (p >= 60) return 'لا باس، خاصك تقوّي شي حاجة.';
            if (p >= 40) return 'خاصك مزيد ديال المراجعة.';
            return 'بدا بالمراجعة ديال هاد الموضوع من الأول.';
        },
        get scoreTone() {
            // amber / emerald / red palette for the overlay ring
            if (this.score === this.total) return 'emerald';
            if (this.scorePct >= 60)        return 'amber';
            return 'red';
        },

        // ── Persistence (so accidental back-nav doesn't lose progress) ──
        _loadState() {
            try {
                const raw = localStorage.getItem(this.storageKey);
                if (!raw) return;
                const st = JSON.parse(raw);
                if (st && typeof st === 'object') {
                    this.answers   = (st.answers && typeof st.answers === 'object') ? st.answers : {};
                    this.submitted = !!st.submitted;
                    this.score     = Number(st.score) || 0;
                }
            } catch (e) {}
        },
        _saveState() {
            try {
                if (!this.submitted && Object.keys(this.answers).length === 0) {
                    localStorage.removeItem(this.storageKey);
                    return;
                }
                localStorage.setItem(this.storageKey, JSON.stringify({
                    answers: this.answers, submitted: this.submitted, score: this.score,
                }));
            } catch (e) {}
        },
        _clearState() {
            try { localStorage.removeItem(this.storageKey); } catch (e) {}
        },
    };
}
</script>
@endpush
@endsection
