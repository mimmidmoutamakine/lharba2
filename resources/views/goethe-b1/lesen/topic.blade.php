@extends('layouts.app')
@section('title', $topic->title . ' | Goethe B1 Lesen | ' . config('app.name'))

@push('head')
<style>
    /* Highlighter-pen evidence marks (only on submit, only in DE view). */
    mark.lharba-evidence {
        background: linear-gradient(180deg, transparent 55%, rgba(245, 158, 11, 0.32) 55%);
        color: #fef3c7;
        padding: 0 1px;
        border-radius: 2px;
    }
    mark.lharba-evidence:hover {
        background: linear-gradient(180deg, transparent 0%, rgba(245, 158, 11, 0.40) 0%);
        color: #ffffff;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 pt-28 md:pt-32 pb-8"
     x-data="goetheB1LesenTopic({{ json_encode([
         'teil1' => $topic->teil1,
         'teil2' => $topic->teil2,
         'teil3' => $topic->teil3,
         'teil4' => $topic->teil4,
         'teil5' => $topic->teil5,
     ]) }}, {{ json_encode($activePart ?? null) }}, {{ ($timerEnabled ?? false) ? 'true' : 'false' }}, {{ json_encode($topic->slug) }})"
     x-effect="_lockBodyScroll(t3SheetOpen || t1SheetOpen)"
     @keydown.escape.window="if (t1SheetOpen) t1SheetOpen = false; else if (t3SheetOpen) t3SheetOpen = false"
>
    @php $partLabels = ['teil1'=>'Teil 1','teil2'=>'Teil 2','teil3'=>'Teil 3','teil4'=>'Teil 4','teil5'=>'Teil 5']; @endphp

    {{-- Floating top-right: back + part switcher --}}
    <div class="fixed top-[72px] md:top-[88px] right-3 md:right-4 z-30 flex items-center gap-1.5" dir="ltr">
        <a href="{{ route('goethe-b1.lesen.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-full bg-[#111216]/85 backdrop-blur border border-white/10 text-slate-400 hover:text-white hover:bg-[#111216] active:scale-95 transition-all shadow-lg shadow-black/30"
           title="رجوع للائحة">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        </a>
        <div class="px-1 h-9 flex items-center gap-0.5 rounded-full bg-[#111216]/85 backdrop-blur border border-white/10 shadow-lg shadow-black/30">
            @foreach($partLabels as $key => $label)
            <button type="button" @click="setActivePart('{{ $key }}')"
                    :class="activePart === '{{ $key }}' ? 'bg-amber-600 text-white' : 'text-slate-400 hover:text-white'"
                    class="px-2 h-7 rounded-full text-[11px] font-bold transition-all">{{ str_replace('Teil ', 'T', $label) }}</button>
            @endforeach
        </div>
    </div>

    <header class="mb-6" dir="rtl">
        <div class="text-[10px] font-black uppercase tracking-[0.25em] text-amber-400 mb-2">Goethe B1 · Lesen</div>
        <h1 class="text-2xl md:text-3xl font-bold text-white" dir="ltr">{{ $topic->title }}</h1>
        @if($topic->title_ar)
        <p class="text-sm text-slate-500 mt-1">{{ $topic->title_ar }}</p>
        @endif
        <p class="text-xs text-slate-500 mt-2" dir="ltr" x-text="parts[activePart]?.instructions ?? ''"></p>
    </header>

    {{-- Score bar (after submit) --}}
    <template x-if="submitted">
        <div class="mb-6 p-4 rounded-xl flex items-center justify-between gap-4"
             :class="score === total ? 'bg-green-500/10 border border-green-500/20' : score >= total/2 ? 'bg-yellow-500/10 border border-yellow-500/20' : 'bg-red-500/10 border border-red-500/20'">
            <div dir="rtl">
                <div class="font-bold text-white text-lg" x-text="score === total ? '🎉 ممتاز! كل الأجوبة صحيحة' : score + '/' + total + ' إجابة صحيحة'"></div>
            </div>
            <button @click="reset()" class="shrink-0 px-4 py-2 rounded-xl border border-white/10 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-all" dir="rtl">إعادة المحاولة</button>
        </div>
    </template>

    {{-- ── TEIL 1 — Read + R/F ─────────────────────────────────────── --}}
    <section x-show="activePart === 'teil1' && parts.teil1" x-cloak>
        <template x-if="parts.teil1">
        <div>
            {{-- Framed instruction card (runner icon + Lies den Text…) --}}
            <div class="mb-4 p-4 rounded-xl border border-amber-500/20 bg-amber-500/5 flex items-start gap-3" dir="ltr">
                <div class="shrink-0 w-9 h-9 rounded-full bg-amber-500/15 text-amber-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13" cy="4" r="2"/><path d="m4 22 5-9 4 3 5-7"/><path d="M18 9h3"/></svg>
                </div>
                <p class="text-sm text-white leading-snug pt-1.5" x-text="parts.teil1.instructions || 'Lies den Text und die Aufgaben dazu. Wähle: Sind die Aussagen richtig oder falsch?'"></p>
            </div>

        <div class="grid lg:grid-cols-[1fr_400px] gap-6 items-start pb-28 lg:pb-0">
            <article class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden relative">
                {{-- Blog header banner (red) when blog_name present, else generic Text label --}}
                <template x-if="parts.teil1.blog_name">
                    <div class="bg-gradient-to-r from-rose-600 via-red-600 to-rose-600 px-5 py-3 text-center shadow-inner">
                        <span class="text-white text-sm md:text-base font-bold tracking-wide" x-text="parts.teil1.blog_name"></span>
                    </div>
                </template>
                <template x-if="!parts.teil1.blog_name">
                    <div class="px-5 py-3 border-b border-white/[0.05] text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Text</div>
                </template>

                {{-- Language toggle (only when AR translation exists) --}}
                <template x-if="t1HasTranslation()">
                    <button @click="langView = langView === 'ar' ? 'de' : 'ar'"
                            class="absolute top-2 right-2 z-10 px-2 h-7 rounded-md bg-black/40 border border-white/10 text-[10px] font-black tracking-widest backdrop-blur-sm transition-all hover:bg-black/60 hover:border-amber-500/40"
                            :class="langView === 'ar' ? 'text-amber-300' : 'text-slate-300'"
                            :title="langView === 'ar' ? 'عرض الألمانية' : 'عرض الترجمة العربية'">
                        <span x-text="langView === 'ar' ? 'DE' : 'ع'"></span>
                    </button>
                </template>

                <div class="px-5 md:px-7 py-5 text-[15px] text-slate-200 leading-[1.9] space-y-4"
                     :dir="langView === 'ar' ? 'rtl' : 'ltr'">
                    <template x-for="(p, pi) in t1Paragraphs()" :key="langView + '-' + pi">
                        <p x-html="t1ParagraphHtml(pi)" class="whitespace-pre-line"></p>
                    </template>
                </div>
            </article>

            {{-- Desktop questions column (mobile shows sheet instead) --}}
            <div class="hidden lg:block space-y-2">
                {{-- Beispiel 0 — locked worked example, mirrors the official Modelltest --}}
                <template x-if="parts.teil1.beispiel">
                    <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/[0.04] p-3" :dir="langView === 'ar' ? 'rtl' : 'ltr'">
                        <div class="flex items-start gap-2 mb-2">
                            <span class="px-2 h-5 flex items-center rounded-md bg-emerald-500/15 text-emerald-300 text-[10px] font-black tracking-widest uppercase mt-0.5">Beispiel</span>
                            <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-md text-xs font-bold bg-emerald-500/15 text-emerald-300 mt-0.5" x-text="parts.teil1.beispiel.id"></span>
                            <p class="text-sm text-white leading-snug flex-1" x-text="t1Prompt(parts.teil1.beispiel)"></p>
                        </div>
                        <div class="flex gap-2 mb-2" dir="ltr">
                            <template x-for="opt in [{l:'R',label:'Richtig'}, {l:'F',label:'Falsch'}]" :key="opt.l">
                                <div class="flex-1 h-10 rounded-lg border text-sm font-bold flex items-center justify-center"
                                     :class="parts.teil1.beispiel.answer === opt.l
                                         ? 'border-emerald-500/50 bg-emerald-500/15 text-emerald-200'
                                         : 'border-white/[0.08] text-slate-500 line-through opacity-50'">
                                    <span x-text="opt.label"></span>
                                </div>
                            </template>
                        </div>
                        {{-- Beispiel explanation: always shown (it's a worked example) --}}
                        <template x-if="parts.teil1.beispiel.explanation">
                            <div class="mt-2 px-3 py-2 rounded-lg bg-emerald-500/[0.06] border border-emerald-500/15 text-[12.5px] leading-relaxed text-emerald-100/90" dir="rtl"
                                 x-text="parts.teil1.beispiel.explanation"></div>
                        </template>
                    </div>
                </template>

                <template x-for="q in parts.teil1.questions" :key="q.id">
                    <div class="rounded-xl border bg-[#111216] p-3"
                         :class="submitted ? (answers[q.id] === q.answer ? 'border-green-500/40' : 'border-red-500/40') : (answers[q.id] !== undefined ? 'border-amber-500/40' : 'border-white/[0.08]')"
                         :dir="langView === 'ar' ? 'rtl' : 'ltr'">
                        <div class="flex items-start gap-2 mb-2">
                            <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-md text-xs font-bold mt-0.5"
                                  :class="submitted ? (answers[q.id] === q.answer ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400') : (answers[q.id] !== undefined ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400')"
                                  x-text="q.id"></span>
                            <p class="text-sm text-white leading-snug flex-1" x-text="t1Prompt(q)"></p>
                        </div>
                        <div class="flex gap-2" dir="ltr">
                            <template x-for="opt in [{l:'R',label:'Richtig'}, {l:'F',label:'Falsch'}]" :key="opt.l">
                                <button @click="!submitted && pickAnswer(q.id, opt.l)"
                                        :disabled="submitted"
                                        class="flex-1 h-10 rounded-lg border text-sm font-bold transition-all"
                                        :class="submitted
                                            ? (q.answer === opt.l
                                                ? 'border-green-500/50 bg-green-500/10 text-green-300'
                                                : (answers[q.id] === opt.l ? 'border-red-500/50 bg-red-500/10 text-red-300' : 'border-white/[0.08] text-slate-600'))
                                            : (answers[q.id] === opt.l ? 'border-amber-500 bg-amber-500/10 text-amber-300' : 'border-white/[0.08] text-slate-300 hover:border-white/30 active:scale-95')">
                                    <span x-text="opt.label"></span>
                                </button>
                            </template>
                        </div>

                        {{-- Explanation block (shown after submit) --}}
                        <template x-if="submitted && q.explanation">
                            <div class="mt-2 px-3 py-2 rounded-lg text-[12.5px] leading-relaxed border"
                                 :class="answers[q.id] === q.answer
                                     ? 'bg-green-500/[0.05] border-green-500/15 text-green-100/90'
                                     : 'bg-amber-500/[0.06] border-amber-500/20 text-amber-100/90'"
                                 dir="rtl"
                                 x-text="q.explanation"></div>
                        </template>
                    </div>
                </template>

                <template x-if="!submitted">
                    <button @click="submitAnswers()"
                            :disabled="Object.keys(answers).length < total"
                            class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed mt-2">
                        <span x-text="'Abgabe (' + Object.keys(answers).length + '/' + total + ')'"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- ─────────────── MOBILE: sticky bottom bar (Aufgaben / score) ─────────────── --}}
        <div x-show="!t1SheetOpen" x-cloak
             class="fixed bottom-0 left-0 right-0 lg:hidden z-40 px-4 py-3 bg-[#08090C]/95 backdrop-blur border-t border-white/[0.08]">
            <template x-if="!submitted">
                <button @click="t1SheetOpen = true"
                        class="w-full py-3 rounded-xl bg-amber-600 active:bg-amber-500 text-white text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                    <span x-text="'Aufgaben (' + Object.keys(answers).length + '/' + total + ')'"></span>
                </button>
            </template>
            <template x-if="submitted">
                <div class="flex items-center justify-between gap-3" dir="rtl">
                    <button @click="t1SheetOpen = true"
                            class="px-4 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        مراجعة
                    </button>
                    <div class="text-sm font-bold flex-1 text-center"
                         :class="score === total ? 'text-green-400' : score >= total/2 ? 'text-yellow-400' : 'text-red-400'"
                         x-text="score + '/' + total + ' إجابة صحيحة'"></div>
                    <button @click="reset()" class="px-4 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium">إعادة المحاولة</button>
                </div>
            </template>
        </div>

        {{-- ─────────────── MOBILE: bottom sheet backdrop ─────────────── --}}
        <div x-show="t1SheetOpen" x-cloak
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="t1SheetOpen = false"
             class="fixed inset-0 z-[75] lg:hidden bg-black/70"></div>

        {{-- ─────────────── MOBILE: bottom sheet panel (Beispiel + Aufgaben R/F) ─────────────── --}}
        <div x-show="t1SheetOpen" x-cloak
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed bottom-0 left-0 right-0 z-[80] lg:hidden bg-[#0B0C10] rounded-t-2xl border-t border-white/10 flex flex-col touch-pan-y"
             style="max-height:88vh; max-height:88dvh">

            {{-- Sticky header: handle + title + close + question chips --}}
            <div class="shrink-0 bg-[#0B0C10] border-b border-white/[0.06] pt-3 px-3">
                <div class="w-10 h-1 rounded-full bg-white/20 mx-auto mb-3"></div>
                <div class="flex items-center justify-between mb-2 px-1" dir="rtl">
                    <div>
                        <div class="text-sm font-bold text-white">الأسئلة</div>
                        <div class="text-[10px] text-slate-500 mt-0.5" x-text="Object.keys(answers).length + '/' + total + ' تم الإجابة'"></div>
                    </div>
                    <button @click="t1SheetOpen = false" class="shrink-0 w-9 h-9 flex items-center justify-center rounded-full bg-white/10 active:bg-white/20 text-white border border-white/15" aria-label="إغلاق">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                {{-- Question chips for in-sheet navigation --}}
                <div class="flex items-center justify-between gap-1.5 pb-2" dir="ltr">
                    <template x-for="q in parts.teil1.questions" :key="q.id">
                        <button @click="scrollToT1Question(q.id)"
                                class="flex-1 flex flex-col items-center gap-1 py-1.5 rounded-xl border transition-all active:scale-95"
                                :class="submitted
                                    ? (answers[q.id] === q.answer ? 'border-green-500/40 bg-green-500/5' : 'border-red-500/40 bg-red-500/5')
                                    : answers[q.id] !== undefined ? 'border-amber-500/40 bg-amber-500/5' : 'border-white/[0.06] bg-transparent'">
                            <span class="text-xs font-bold leading-none"
                                  :class="submitted
                                      ? (answers[q.id] === q.answer ? 'text-green-400' : 'text-red-400')
                                      : answers[q.id] !== undefined ? 'text-amber-300' : 'text-slate-600'"
                                  x-text="q.id"></span>
                            <span class="w-1.5 h-1.5 rounded-full"
                                  :class="submitted
                                      ? (answers[q.id] === q.answer ? 'bg-green-500' : 'bg-red-400')
                                      : answers[q.id] !== undefined ? 'bg-amber-500' : 'bg-slate-700'"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Scrollable question cards (Beispiel + 1..6) --}}
            <div class="flex-1 overflow-y-auto p-3 space-y-3" style="overscroll-behavior:contain">
                {{-- Beispiel 0 (locked) --}}
                <template x-if="parts.teil1.beispiel">
                    <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/[0.04] overflow-hidden" :dir="langView === 'ar' ? 'rtl' : 'ltr'">
                        <div class="px-4 py-3 border-b border-emerald-500/10 flex items-start gap-3">
                            <span class="px-2 h-5 flex items-center rounded-md bg-emerald-500/15 text-emerald-300 text-[10px] font-black tracking-widest uppercase mt-0.5">Beispiel</span>
                            <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-300 text-xs font-bold mt-0.5" x-text="parts.teil1.beispiel.id"></span>
                            <p class="text-sm font-bold text-white leading-snug flex-1" x-text="t1Prompt(parts.teil1.beispiel)"></p>
                        </div>
                        <div class="p-3 flex gap-2" dir="ltr">
                            <template x-for="opt in [{l:'R',label:'Richtig'}, {l:'F',label:'Falsch'}]" :key="opt.l">
                                <div class="flex-1 h-11 rounded-xl border text-sm font-bold flex items-center justify-center"
                                     :class="parts.teil1.beispiel.answer === opt.l
                                         ? 'border-emerald-500/50 bg-emerald-500/15 text-emerald-200'
                                         : 'border-white/[0.06] text-slate-500 line-through opacity-50'">
                                    <span x-text="opt.label"></span>
                                </div>
                            </template>
                        </div>
                        <template x-if="parts.teil1.beispiel.explanation">
                            <div class="mx-3 mb-3 px-3 py-2 rounded-lg bg-emerald-500/[0.06] border border-emerald-500/15 text-[12.5px] leading-relaxed text-emerald-100/90" dir="rtl"
                                 x-text="parts.teil1.beispiel.explanation"></div>
                        </template>
                    </div>
                </template>

                <template x-for="q in parts.teil1.questions" :key="q.id">
                    <div :id="'mob-t1-q-' + q.id"
                         class="rounded-2xl border bg-[#111216] overflow-hidden transition-all scroll-mt-2"
                         :class="submitted
                             ? (answers[q.id] === q.answer ? 'border-green-500/40' : 'border-red-500/40')
                             : answers[q.id] !== undefined ? 'border-white/20' : 'border-white/[0.08]'"
                         :dir="langView === 'ar' ? 'rtl' : 'ltr'">
                        <div class="px-4 py-3 border-b border-white/[0.05] flex items-start gap-3">
                            <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold mt-0.5"
                                  :class="submitted
                                      ? (answers[q.id] === q.answer ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400')
                                      : answers[q.id] !== undefined ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400'"
                                  x-text="q.id"></span>
                            <p class="text-sm font-bold text-white leading-snug flex-1" x-text="t1Prompt(q)"></p>
                        </div>
                        <div class="p-3 flex gap-2" dir="ltr">
                            <template x-for="opt in [{l:'R',label:'Richtig'}, {l:'F',label:'Falsch'}]" :key="opt.l">
                                <button @click="!submitted && pickAnswer(q.id, opt.l)"
                                        :disabled="submitted"
                                        class="flex-1 h-11 rounded-xl border text-sm font-bold transition-all active:scale-[0.97] flex items-center justify-center gap-2"
                                        :class="submitted
                                            ? (q.answer === opt.l
                                                ? 'border-green-500/50 bg-green-500/10 text-green-200'
                                                : (answers[q.id] === opt.l ? 'border-red-500/50 bg-red-500/10 text-red-200' : 'border-white/[0.06] text-slate-600'))
                                            : (answers[q.id] === opt.l ? 'border-amber-500 bg-amber-500/10 text-white shadow-lg shadow-amber-500/5' : 'border-white/[0.08] bg-[#0B0C10] text-slate-300 active:bg-white/5')">
                                    <span x-text="opt.label"></span>
                                    <template x-if="submitted && q.answer === opt.l">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    </template>
                                    <template x-if="submitted && answers[q.id] === opt.l && q.answer !== opt.l">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    </template>
                                </button>
                            </template>
                        </div>

                        {{-- Explanation block (shown after submit) --}}
                        <template x-if="submitted && q.explanation">
                            <div class="mx-3 mb-3 px-3 py-2 rounded-lg text-[12.5px] leading-relaxed border"
                                 :class="answers[q.id] === q.answer
                                     ? 'bg-green-500/[0.05] border-green-500/15 text-green-100/90'
                                     : 'bg-amber-500/[0.06] border-amber-500/20 text-amber-100/90'"
                                 dir="rtl"
                                 x-text="q.explanation"></div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Sticky footer: Abgabe / score+reset --}}
            <div class="shrink-0 border-t border-white/[0.06] p-3 bg-[#0B0C10]">
                <template x-if="!submitted">
                    <button @click="submitAnswers(); t1SheetOpen = false"
                            :disabled="Object.keys(answers).length < total"
                            class="w-full py-3 rounded-xl bg-amber-600 active:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span x-text="'Abgabe (' + Object.keys(answers).length + '/' + total + ')'"></span>
                    </button>
                </template>
                <template x-if="submitted">
                    <div class="flex items-center gap-2" dir="rtl">
                        <div class="flex-1 text-sm font-bold"
                             :class="score === total ? 'text-green-400' : score >= total/2 ? 'text-yellow-400' : 'text-red-400'"
                             x-text="score + '/' + total + ' إجابة صحيحة'"></div>
                        <button @click="reset()" class="px-4 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium">إعادة المحاولة</button>
                        <button @click="t1SheetOpen = false" class="px-4 py-2.5 rounded-xl bg-white/5 text-xs text-white font-medium">إغلاق</button>
                    </div>
                </template>
            </div>
        </div>
        </div>
        </template>
    </section>

    {{-- ── TEIL 2 — 2 press texts + MC (a/b/c) ─────────────────────── --}}
    <section x-show="activePart === 'teil2' && parts.teil2" x-cloak>
        <template x-if="parts.teil2">
        <div class="grid lg:grid-cols-[1fr_420px] gap-6 items-start">
            <div class="space-y-4">
                <template x-for="(t, ti) in parts.teil2.texts" :key="ti">
                    <article class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
                        <div class="px-5 py-3 border-b border-white/[0.05] flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500" x-text="t.label"></span>
                            <span class="text-[10px] text-slate-600" x-text="'Aufgaben ' + t.from + '–' + t.to"></span>
                        </div>
                        <h2 class="px-5 pt-4 pb-1 text-lg font-black tracking-tight text-white" x-text="t.title"></h2>
                        <div class="px-5 py-4 text-[15px] text-slate-200 leading-[1.8] whitespace-pre-line" x-text="t.text"></div>
                    </article>
                </template>
            </div>

            <div class="space-y-2">
                <template x-for="q in parts.teil2.questions" :key="q.id">
                    <div class="rounded-xl border bg-[#111216] p-3"
                         :class="submitted ? (answers[q.id] === q.answer ? 'border-green-500/40' : 'border-red-500/40') : (answers[q.id] !== undefined ? 'border-amber-500/40' : 'border-white/[0.08]')">
                        <div class="flex items-start gap-2 mb-2">
                            <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-md text-xs font-bold mt-0.5"
                                  :class="submitted ? (answers[q.id] === q.answer ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400') : (answers[q.id] !== undefined ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400')"
                                  x-text="q.id"></span>
                            <div class="flex-1">
                                <p class="text-sm text-white leading-snug" x-text="q.prompt"></p>
                                <span class="inline-block mt-1 text-[10px] text-slate-500" x-text="q.text_label"></span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <template x-for="ch in q.choices" :key="ch.label">
                                <button @click="!submitted && pickAnswer(q.id, ch.label)"
                                        :disabled="submitted"
                                        class="w-full text-left flex items-start gap-2 px-3 py-2 rounded-lg border text-sm transition-all"
                                        :class="submitted
                                            ? (q.answer === ch.label
                                                ? 'border-green-500/50 bg-green-500/10 text-green-200'
                                                : (answers[q.id] === ch.label ? 'border-red-500/50 bg-red-500/10 text-red-200' : 'border-white/[0.08] text-slate-500'))
                                            : (answers[q.id] === ch.label ? 'border-amber-500 bg-amber-500/10 text-amber-200' : 'border-white/[0.08] text-slate-300 hover:border-white/30')">
                                    <span class="shrink-0 w-6 h-6 flex items-center justify-center rounded text-[11px] font-black uppercase"
                                          :class="answers[q.id] === ch.label || (submitted && q.answer === ch.label) ? 'bg-amber-600/30 text-amber-200' : 'bg-white/5 text-slate-500'"
                                          x-text="ch.label"></span>
                                    <span class="text-[13px] leading-snug flex-1" x-text="ch.text"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="!submitted">
                    <button @click="submitAnswers()"
                            :disabled="Object.keys(answers).length < total"
                            class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed mt-2">
                        <span x-text="'Abgabe (' + Object.keys(answers).length + '/' + total + ')'"></span>
                    </button>
                </template>
            </div>
        </div>
        </template>
    </section>

    {{-- ── TEIL 3 — Match situations to ads (A–J or 0) ─────────────── --}}
    <section x-show="activePart === 'teil3' && parts.teil3" x-cloak>
        <template x-if="parts.teil3">
        <div>
            {{-- Theme + Beispiel notice --}}
            <div class="mb-4 p-4 rounded-xl border border-amber-500/20 bg-amber-500/5" dir="ltr">
                <div class="text-[10px] font-black uppercase tracking-[0.25em] text-amber-400 mb-1">Thema</div>
                <p class="text-sm text-white leading-snug" x-text="parts.teil3.theme"></p>
                <template x-if="parts.teil3.beispiel">
                    <p class="text-[11px] text-slate-500 mt-2">
                        Beispiel: Anzeige <span class="font-black text-amber-400" x-text="parts.teil3.beispiel.anzeige"></span> ist schon vergeben.
                    </p>
                </template>
            </div>

            <div class="grid lg:grid-cols-[1fr_420px] gap-6 items-start">
                {{-- LEFT: Ads blob (scrollable) --}}
                <article class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden lg:max-h-[calc(100vh-12rem)] flex flex-col">
                    <div class="shrink-0 px-5 py-3 border-b border-white/[0.05] flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Anzeigen A – J</span>
                        <span class="text-[10px] text-slate-600">OCR text</span>
                    </div>
                    <div class="flex-1 overflow-y-auto px-5 py-4 text-[13.5px] text-slate-200 leading-[1.75] whitespace-pre-line" x-text="parts.teil3.advertisements_text"></div>
                </article>

                {{-- RIGHT: Situations + chip selector --}}
                <div class="space-y-2">
                    <template x-for="s in parts.teil3.situations" :key="s.id">
                        <div class="rounded-xl border bg-[#111216] p-3"
                             :class="submitted
                                 ? (answers[s.id] === s.answer ? 'border-green-500/40' : 'border-red-500/40')
                                 : (answers[s.id] !== undefined ? 'border-amber-500/40' : 'border-white/[0.08]')">
                            <div class="flex items-start gap-2 mb-2">
                                <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-md text-xs font-bold mt-0.5"
                                      :class="submitted ? (answers[s.id] === s.answer ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400') : (answers[s.id] !== undefined ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400')"
                                      x-text="s.id"></span>
                                <p class="text-sm text-white leading-snug flex-1" x-text="s.prompt"></p>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="letter in [...parts.teil3.ads_letters, '0']" :key="letter">
                                    <button @click="!submitted && pickAdLetter(s.id, letter)"
                                            :disabled="submitted || (parts.teil3.beispiel && letter === parts.teil3.beispiel.anzeige)"
                                            class="w-9 h-9 rounded-md text-xs font-black uppercase transition-all"
                                            :class="(parts.teil3.beispiel && letter === parts.teil3.beispiel.anzeige)
                                                ? 'bg-slate-500/10 text-slate-700 cursor-not-allowed line-through'
                                                : submitted
                                                    ? (s.answer === letter
                                                        ? 'bg-green-500/20 text-green-300 border border-green-500/40'
                                                        : (answers[s.id] === letter ? 'bg-red-500/20 text-red-300 border border-red-500/40' : 'bg-white/5 text-slate-600'))
                                                    : (answers[s.id] === letter
                                                        ? 'bg-amber-600 text-white scale-110'
                                                        : (adAssignedTo(letter) !== null && adAssignedTo(letter) !== s.id
                                                            ? 'bg-slate-500/10 text-slate-500 hover:text-amber-200'
                                                            : 'bg-white/5 text-amber-300 hover:bg-white/10'))"
                                            x-text="letter === '0' ? '0' : letter"></button>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="!submitted">
                        <button @click="submitAnswers()"
                                :disabled="Object.keys(answers).length < total"
                                class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed mt-2">
                            <span x-text="'Abgabe (' + Object.keys(answers).length + '/' + total + ')'"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
        </template>
    </section>

    {{-- ── TEIL 4 — Für / Gegen ────────────────────────────────────── --}}
    <section x-show="activePart === 'teil4' && parts.teil4" x-cloak>
        <template x-if="parts.teil4">
        <div>
            <div class="mb-4 p-4 rounded-xl border border-amber-500/20 bg-amber-500/5" dir="ltr">
                <div class="text-[10px] font-black uppercase tracking-[0.25em] text-amber-400 mb-1">Thema</div>
                <p class="text-sm text-white leading-snug" x-text="parts.teil4.topic"></p>
            </div>

            <div class="space-y-3 max-w-3xl mx-auto">
                <template x-for="c in parts.teil4.comments" :key="c.id">
                    <div class="rounded-xl border bg-[#111216] p-4"
                         :class="submitted ? (answers[c.id] === c.answer ? 'border-green-500/40' : 'border-red-500/40') : (answers[c.id] !== undefined ? 'border-amber-500/40' : 'border-white/[0.08]')">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="shrink-0 w-8 h-8 flex items-center justify-center rounded-md text-xs font-bold mt-0.5"
                                  :class="submitted ? (answers[c.id] === c.answer ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400') : (answers[c.id] !== undefined ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400')"
                                  x-text="c.id"></span>
                            <div class="flex-1">
                                <p class="text-[14.5px] text-white leading-[1.7]" x-text="c.prompt"></p>
                                <template x-if="c.author">
                                    <p class="text-[11px] text-slate-500 mt-2"
                                       x-text="(c.author.name ?? '') + (c.author.age ? ', ' + c.author.age : '') + (c.author.city ? ' · ' + c.author.city : '')"></p>
                                </template>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <template x-for="opt in [{l:'J',label:'Dafür'}, {l:'N',label:'Dagegen'}]" :key="opt.l">
                                <button @click="!submitted && pickAnswer(c.id, opt.l)"
                                        :disabled="submitted"
                                        class="flex-1 h-10 rounded-lg border text-sm font-bold transition-all"
                                        :class="submitted
                                            ? (c.answer === opt.l
                                                ? 'border-green-500/50 bg-green-500/10 text-green-300'
                                                : (answers[c.id] === opt.l ? 'border-red-500/50 bg-red-500/10 text-red-300' : 'border-white/[0.08] text-slate-600'))
                                            : (answers[c.id] === opt.l ? 'border-amber-500 bg-amber-500/10 text-amber-300' : 'border-white/[0.08] text-slate-300 hover:border-white/30')">
                                    <span x-text="opt.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="!submitted">
                    <button @click="submitAnswers()"
                            :disabled="Object.keys(answers).length < total"
                            class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed mt-2">
                        <span x-text="'Abgabe (' + Object.keys(answers).length + '/' + total + ')'"></span>
                    </button>
                </template>
            </div>
        </div>
        </template>
    </section>

    {{-- ── TEIL 5 — Reading + MC ───────────────────────────────────── --}}
    <section x-show="activePart === 'teil5' && parts.teil5" x-cloak>
        <template x-if="parts.teil5">
        <div class="grid lg:grid-cols-[1fr_400px] gap-6 items-start">
            <article class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
                <div class="px-5 py-3 border-b border-white/[0.05] flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Text</span>
                </div>
                <template x-if="parts.teil5.situation">
                    <p class="px-5 pt-4 text-xs text-slate-500 italic" x-text="parts.teil5.situation"></p>
                </template>
                <div class="px-5 py-5 text-[15px] text-slate-200 leading-[1.85] whitespace-pre-line" x-text="parts.teil5.reading_text"></div>
            </article>

            <div class="space-y-2">
                <template x-for="q in parts.teil5.questions" :key="q.id">
                    <div class="rounded-xl border bg-[#111216] p-3"
                         :class="submitted ? (answers[q.id] === q.answer ? 'border-green-500/40' : 'border-red-500/40') : (answers[q.id] !== undefined ? 'border-amber-500/40' : 'border-white/[0.08]')">
                        <div class="flex items-start gap-2 mb-2">
                            <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-md text-xs font-bold mt-0.5"
                                  :class="submitted ? (answers[q.id] === q.answer ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400') : (answers[q.id] !== undefined ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400')"
                                  x-text="q.id"></span>
                            <p class="text-sm text-white leading-snug flex-1" x-text="q.prompt"></p>
                        </div>
                        <div class="space-y-1.5">
                            <template x-for="ch in q.choices" :key="ch.label">
                                <button @click="!submitted && pickAnswer(q.id, ch.label)"
                                        :disabled="submitted"
                                        class="w-full text-left flex items-start gap-2 px-3 py-2 rounded-lg border text-sm transition-all"
                                        :class="submitted
                                            ? (q.answer === ch.label
                                                ? 'border-green-500/50 bg-green-500/10 text-green-200'
                                                : (answers[q.id] === ch.label ? 'border-red-500/50 bg-red-500/10 text-red-200' : 'border-white/[0.08] text-slate-500'))
                                            : (answers[q.id] === ch.label ? 'border-amber-500 bg-amber-500/10 text-amber-200' : 'border-white/[0.08] text-slate-300 hover:border-white/30')">
                                    <span class="shrink-0 w-6 h-6 flex items-center justify-center rounded text-[11px] font-black uppercase"
                                          :class="answers[q.id] === ch.label || (submitted && q.answer === ch.label) ? 'bg-amber-600/30 text-amber-200' : 'bg-white/5 text-slate-500'"
                                          x-text="ch.label"></span>
                                    <span class="text-[13px] leading-snug flex-1" x-text="ch.text"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="!submitted">
                    <button @click="submitAnswers()"
                            :disabled="Object.keys(answers).length < total"
                            class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed mt-2">
                        <span x-text="'Abgabe (' + Object.keys(answers).length + '/' + total + ')'"></span>
                    </button>
                </template>
            </div>
        </div>
        </template>
    </section>

    {{-- No part selected --}}
    <div x-show="!activePart" x-cloak class="text-center py-16 text-slate-500" dir="rtl">
        <p>اختر جزءاً (Teil) من الأعلى للبدء.</p>
    </div>
</div>

@push('scripts')
<script>
function goetheB1LesenTopic(parts, initialPart, timerEnabled, topicSlug) {
    return {
        parts,
        topicSlug: topicSlug || '',
        activePart: initialPart || (parts.teil1 ? 'teil1' : (parts.teil2 ? 'teil2' : null)),
        answers: {},
        submitted: false,
        score: 0,
        total: 0,

        // Teil 1 mobile bottom sheet (questions panel)
        t1SheetOpen: false,
        // Teil 1 language view — 'de' (German, default) | 'ar' (Arabic translation)
        langView: 'de',
        // Teil 3 mobile sheet state (currently desktop-only chip selector — kept for future)
        t3SheetOpen: false,

        // ── Teil 1 helpers: translation toggle + evidence highlight ──────────
        t1HasTranslation() {
            const t1 = this.parts?.teil1;
            return Array.isArray(t1?.passage_paragraphs_ar) && t1.passage_paragraphs_ar.length > 0;
        },

        t1Paragraphs() {
            const t1 = this.parts?.teil1;
            if (!t1) return [];
            if (this.langView === 'ar' && Array.isArray(t1.passage_paragraphs_ar) && t1.passage_paragraphs_ar.length) {
                return t1.passage_paragraphs_ar;
            }
            return t1.passage_paragraphs || (t1.passage ? t1.passage.split(/\n{2,}/) : []);
        },

        t1AllEvidence() {
            const t1 = this.parts?.teil1;
            if (!t1 || !this.submitted) return [];
            const out = [];
            if (Array.isArray(t1.beispiel?.evidence)) out.push(...t1.beispiel.evidence);
            for (const q of (t1.questions || [])) {
                if (Array.isArray(q.evidence)) out.push(...q.evidence);
            }
            return out.filter(s => typeof s === 'string' && s.length > 0);
        },

        t1ParagraphHtml(idx) {
            const text = this.t1Paragraphs()[idx] || '';
            // Highlight only in German view AND after submit. Arabic translation paragraphs
            // don't contain the German evidence substrings, so highlighting them is a no-op anyway.
            if (this.langView !== 'de' || !this.submitted) return this._escape(text);
            return this._highlight(text, this.t1AllEvidence());
        },

        t1Prompt(q) {
            if (this.langView === 'ar' && q?.prompt_ar) return q.prompt_ar;
            return q?.prompt || '';
        },

        _escape(s) {
            const div = document.createElement('div');
            div.textContent = String(s ?? '');
            return div.innerHTML;
        },

        _highlight(text, evidence) {
            if (!text) return '';
            if (!evidence || !evidence.length) return this._escape(text);
            const matches = [];
            for (const ev of evidence) {
                if (!ev) continue;
                let pos = 0;
                while (pos < text.length) {
                    const i = text.indexOf(ev, pos);
                    if (i === -1) break;
                    matches.push({ start: i, end: i + ev.length });
                    pos = i + 1;
                }
            }
            if (!matches.length) return this._escape(text);
            matches.sort((a, b) => a.start - b.start);
            const merged = [];
            for (const m of matches) {
                const last = merged[merged.length - 1];
                if (last && last.end >= m.start) last.end = Math.max(last.end, m.end);
                else merged.push({ ...m });
            }
            let html = '', cursor = 0;
            for (const m of merged) {
                html += this._escape(text.slice(cursor, m.start));
                html += '<mark class="lharba-evidence">' + this._escape(text.slice(m.start, m.end)) + '</mark>';
                cursor = m.end;
            }
            html += this._escape(text.slice(cursor));
            return html;
        },

        scrollToT1Question(qid) {
            this.$nextTick(() => {
                const el = document.getElementById('mob-t1-q-' + qid);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        },

        init() {
            this.computeTotal();
            this._loadState();
        },

        setActivePart(key) {
            if (!this.parts[key]) return;
            // Save current part's progress BEFORE switching (in case user picked answers
            // and is jumping to another part — don't lose what they've done).
            this._saveState();
            this.activePart = key;
            this.answers = {};
            this.submitted = false;
            this.score = 0;
            this.t1SheetOpen = false;
            this.computeTotal();
            this._loadState();
        },

        // ── localStorage persistence so accidental navigation away doesn't wipe answers ──
        _storageKey() {
            return 'lh.gb1l.' + this.topicSlug + '.' + (this.activePart || 'na');
        },

        _loadState() {
            if (!this.topicSlug || !this.activePart) return;
            try {
                const raw = localStorage.getItem(this._storageKey());
                if (!raw) return;
                const state = JSON.parse(raw);
                if (state && typeof state === 'object') {
                    this.answers = (state.answers && typeof state.answers === 'object') ? state.answers : {};
                    this.submitted = !!state.submitted;
                    this.score = Number(state.score) || 0;
                }
            } catch (e) { /* corrupt storage entry — ignore */ }
        },

        _saveState() {
            if (!this.topicSlug || !this.activePart) return;
            try {
                // Don't write empty unsubmitted state — would just spam localStorage.
                if (!this.submitted && Object.keys(this.answers).length === 0) {
                    localStorage.removeItem(this._storageKey());
                    return;
                }
                localStorage.setItem(this._storageKey(), JSON.stringify({
                    answers: this.answers,
                    submitted: this.submitted,
                    score: this.score,
                    savedAt: Date.now(),
                }));
            } catch (e) { /* quota exceeded etc. — ignore */ }
        },

        _clearState() {
            if (!this.topicSlug || !this.activePart) return;
            try { localStorage.removeItem(this._storageKey()); } catch (e) {}
        },

        computeTotal() {
            const p = this.parts[this.activePart];
            if (!p) { this.total = 0; return; }
            if (this.activePart === 'teil3') this.total = (p.situations || []).length;
            else if (this.activePart === 'teil4') this.total = (p.comments || []).length;
            else this.total = (p.questions || []).length;
        },

        pickAnswer(id, value) {
            if (this.submitted) return;
            this.answers[id] = value;
            this.answers = { ...this.answers };
            this._saveState();
        },

        // Teil 3: chip selector — auto-swap on conflict (except '0' which is reusable)
        pickAdLetter(situationId, letter) {
            if (this.submitted) return;
            const beispiel = this.parts.teil3?.beispiel?.anzeige;
            if (beispiel && letter === beispiel) return;
            if (letter !== '0') {
                for (const sid of Object.keys(this.answers)) {
                    if (this.answers[sid] === letter && String(sid) !== String(situationId)) {
                        delete this.answers[sid];
                    }
                }
            }
            this.answers[situationId] = letter;
            this.answers = { ...this.answers };
            this._saveState();
        },

        adAssignedTo(letter) {
            if (letter === '0') return null;
            const sid = Object.keys(this.answers).find(s => this.answers[s] === letter);
            return sid !== undefined ? (isNaN(Number(sid)) ? sid : Number(sid)) : null;
        },

        submitAnswers() {
            const p = this.parts[this.activePart];
            if (!p) return;
            let correct = 0;
            const items = this.activePart === 'teil3'
                ? p.situations
                : (this.activePart === 'teil4' ? p.comments : p.questions);
            for (const item of items) {
                const id = item.id;
                if (this.answers[id] !== undefined && String(this.answers[id]) === String(item.answer)) {
                    correct++;
                }
            }
            this.score = correct;
            this.submitted = true;
            this._saveState();
            // Scroll to top to show the score bar
            try { window.scrollTo({ top: 0, behavior: 'smooth' }); } catch (e) {}
        },

        reset() {
            this.answers = {};
            this.submitted = false;
            this.score = 0;
            this._clearState();
        },

        _lockBodyScroll(locked) {
            try {
                document.body.style.overflow = locked ? 'hidden' : '';
            } catch (e) {}
        },
    };
}
</script>
@endpush

@endsection
