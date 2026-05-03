@extends('layouts.app')
@section('title', $topic->title . ' | Lesen | ' . config('app.name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 pt-28 md:pt-32 pb-8"
     x-data="lesenTopic({{ json_encode([
         'teil1'           => $topic->teil1,
         'teil2'           => $topic->teil2,
         'teil3'           => $topic->teil3,
         'sprachbausteine1'=> $topic->sprachbausteine1,
         'sprachbausteine2'=> $topic->sprachbausteine2,
     ]) }}, {{ json_encode($activePart ?? null) }}, {{ ($timerEnabled ?? false) ? 'true' : 'false' }})"
     x-effect="_lockBodyScroll(t3SheetOpen || sheetOpen || qSheetOpen || sb1SheetOpen || sb2SheetOpen)"
     @keydown.escape.window="
        if (t3SheetOpen)  t3SheetOpen  = false;
        if (sheetOpen)    sheetOpen    = false;
        if (qSheetOpen)   qSheetOpen   = false;
        if (sb1SheetOpen) { sb1SheetOpen = false; activeBlank = null; }
        if (sb2SheetOpen) { sb2SheetOpen = false; activeBlank = null; }
     "
>
    @php $partLabels = ['teil1'=>'Teil 1','teil2'=>'Teil 2','teil3'=>'Teil 3','sprachbausteine1'=>'Sprachbausteine 1','sprachbausteine2'=>'Sprachbausteine 2']; @endphp

    {{-- Focus-mode floating control: discreet pill in the top-right corner --}}
    <div class="fixed top-[72px] md:top-[88px] right-3 md:right-4 z-30 flex items-center gap-1.5"
         dir="ltr">

        <a href="{{ route('lesen.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-full bg-[#111216]/85 backdrop-blur border border-white/10 text-slate-400 hover:text-white hover:bg-[#111216] active:scale-95 transition-all shadow-lg shadow-black/30"
           title="رجوع للمواضيع">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>

        {{-- Countdown timer (visible only when ?timer=1 and an active part has a duration) --}}
        <template x-if="timerEnabled && secondsLeft > 0 && !submitted">
            <div class="flex items-center gap-1.5 h-9 px-3 rounded-full bg-[#111216]/85 backdrop-blur border font-mono font-bold tabular-nums text-sm shadow-lg shadow-black/30 transition-all"
                 :class="secondsLeft < 60 ? 'border-red-500/60 text-red-300 animate-pulse' : secondsLeft < 300 ? 'border-amber-500/50 text-amber-200' : 'border-white/10 text-slate-200'"
                 :title="formatTime(secondsLeft) + ' متبقية'">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2"/><path d="M5 3 2 6"/><path d="m22 6-3-3"/></svg>
                <span x-text="formatTime(secondsLeft)"></span>
            </div>
        </template>

        {{-- Time-up indicator (visible briefly after auto-submit) --}}
        <template x-if="timerEnabled && secondsLeft === 0 && submitted">
            <div class="flex items-center gap-1.5 h-9 px-3 rounded-full bg-red-500/15 backdrop-blur border border-red-500/40 text-red-300 font-mono font-bold text-xs shadow-lg shadow-black/30">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                انتهى الوقت
            </div>
        </template>

        <div class="relative" @click.outside="partMenuOpen = false">
            <button @click="partMenuOpen = !partMenuOpen"
                    class="flex items-center gap-1.5 h-9 px-3 rounded-full bg-[#111216]/85 backdrop-blur border text-xs font-bold text-white active:scale-95 transition-all shadow-lg shadow-black/30"
                    :class="partMenuOpen ? 'border-amber-500/50 bg-[#111216]' : 'border-white/10 hover:bg-[#111216]'">
                <span x-text="partLabel()"></span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-black {{ $topic->level === 'B2' ? 'bg-orange-500/25 text-orange-200' : 'bg-amber-500/25 text-amber-200' }}">{{ $topic->level }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" :class="partMenuOpen?'rotate-180':''" class="transition-transform text-slate-500"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            <div x-show="partMenuOpen" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute top-full mt-2 right-0 min-w-[220px] bg-[#0B0C10] border border-white/10 rounded-2xl shadow-2xl shadow-black/60 p-2 origin-top-right">

                <div class="px-3 pt-2 pb-2 border-b border-white/[0.04] mb-1" dir="rtl">
                    <h3 class="text-sm font-bold text-white truncate" dir="ltr">{{ $topic->title }}</h3>
                    @if($topic->title_ar)
                    <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $topic->title_ar }}</p>
                    @endif
                </div>

                <div class="space-y-0.5">
                    @foreach($partLabels as $key => $label)
                        @if($topic->$key)
                        <button @click="activePart = '{{ $key }}'; reset(); partMenuOpen = false"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-colors"
                                :class="activePart === '{{ $key }}' ? 'bg-amber-500/20 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5'">
                            <span>{{ $label }}</span>
                            <template x-if="activePart === '{{ $key }}'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400"><path d="M20 6 9 17l-5-5"/></svg>
                            </template>
                        </button>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── TEIL 1 ─────────────────────────────────────────────────── --}}
    <div x-show="activePart === 'teil1'" x-cloak>
      {{-- Friendly fallback if data is missing or malformed --}}
      <template x-if="!parts.teil1 || !Array.isArray(parts.teil1.texts) || !Array.isArray(parts.teil1.headlines)">
        <div class="rounded-2xl border border-amber-500/20 bg-amber-500/[0.05] p-6 text-center" dir="rtl">
            <div class="text-amber-300 font-bold mb-2">المحتوى ديال Teil 1 غير متوفر لهاد الموضوع</div>
            <div class="text-sm text-slate-400 mb-4">إما ما تمّش رفع البيانات (texts + headlines + correctAnswers)، أو الشكل ديال JSON غير متوافق.</div>
            <a href="{{ route('lesen.index', ['teil' => 'teil1']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all">رجوع للائحة</a>
            <details class="mt-4 text-left text-[11px] text-slate-600" dir="ltr">
                <summary class="cursor-pointer">debug · raw data</summary>
                <pre class="mt-2 p-3 rounded bg-black/40 overflow-x-auto whitespace-pre-wrap" x-text="JSON.stringify(parts.teil1, null, 2).slice(0, 500)"></pre>
            </details>
        </div>
      </template>

      <template x-if="parts.teil1 && Array.isArray(parts.teil1.texts) && Array.isArray(parts.teil1.headlines)">
      <div>

        {{-- Instructions --}}
        {{-- Score bar (after submit) --}}
        <template x-if="submitted">
            <div class="mb-6 p-4 rounded-xl flex items-center justify-between gap-4"
                 :class="score === total ? 'bg-green-500/10 border border-green-500/20' : score >= total/2 ? 'bg-yellow-500/10 border border-yellow-500/20' : 'bg-red-500/10 border border-red-500/20'">
                <div dir="rtl">
                    <div class="font-bold text-white text-lg" x-text="score === total ? '🎉 ممتاز! كل الأجوبة صحيحة' : score + '/' + total + ' إجابة صحيحة'"></div>
                    <div class="text-sm text-slate-400 mt-0.5" x-text="score === total ? 'أحسنت! انتقل للجزء التالي.' : 'راجع الأجوبة الخاطئة بالأسفل.'"></div>
                </div>
                <button @click="reset()" class="shrink-0 px-4 py-2 rounded-xl border border-white/10 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-all" dir="rtl">إعادة المحاولة</button>
            </div>
        </template>

        {{-- DESKTOP: two-column layout — both columns scroll independently --}}
        <div class="hidden lg:grid grid-cols-[1fr_360px] gap-6 items-start lg:h-[calc(100vh-10rem)]">

            {{-- LEFT: Texts (own scroll) --}}
            <div class="h-full overflow-y-auto pr-2 space-y-4">
                <template x-for="txt in parts.teil1.texts" :key="txt.id">
                    <div class="rounded-2xl border transition-all duration-200 overflow-hidden cursor-pointer select-none"
                         :class="[
                             selectedText === txt.id
                                 ? 'border-amber-500 shadow-lg shadow-amber-500/10 bg-amber-500/5'
                                 : selectedHeadline && !answers[txt.id]
                                     ? 'border-orange-500/40 bg-orange-500/5 hover:border-orange-400 hover:bg-orange-500/10'
                                     : submitted
                                         ? (answers[txt.id] === correctAnswers[txt.id] ? 'border-green-500/40 bg-[#111216]' : 'border-red-500/40 bg-[#111216]')
                                         : answers[txt.id] ? 'border-white/20 bg-[#111216]' : 'border-white/[0.08] bg-[#111216] hover:border-white/20',
                         ]"
                         @click="!submitted && selectText(txt.id)"
                         @dragover.prevent
                         @drop.prevent="dropOnText($event, txt.id)"
                         :draggable="false"
                    >
                        {{-- Card header --}}
                        <div class="flex items-center justify-between px-4 py-3 border-b border-white/[0.05]">
                            <div class="flex items-center gap-2">
                                {{-- Text number --}}
                                <span class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold"
                                      :class="selectedText === txt.id ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400'"
                                      x-text="txt.id"></span>
                                <span class="text-xs text-slate-500">نص</span>
                            </div>
                            <div class="flex items-center gap-2">
                                {{-- Answer badge --}}
                                <template x-if="answers[txt.id]">
                                    <div class="flex items-center gap-1.5">
                                        <template x-if="submitted">
                                            <svg x-show="answers[txt.id] === correctAnswers[txt.id]" xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                        </template>
                                        <template x-if="submitted">
                                            <svg x-show="answers[txt.id] !== correctAnswers[txt.id]" xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                        </template>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-medium border max-w-[220px]"
                                              :class="submitted
                                                  ? (answers[txt.id] === correctAnswers[txt.id] ? 'bg-green-500/10 border-green-500/30 text-green-400' : 'bg-red-500/10 border-red-500/30 text-red-400')
                                                  : 'bg-amber-500/15 border-amber-500/30 text-amber-300'">
                                            <span class="font-bold uppercase shrink-0" x-text="answers[txt.id]"></span>
                                            <span class="text-current opacity-70 truncate" x-text="getHeadlineText(answers[txt.id])"></span>
                                        </span>
                                        <template x-if="submitted && answers[txt.id] !== correctAnswers[txt.id]">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-medium border bg-green-500/10 border-green-500/30 text-green-400 max-w-[220px]">
                                                <span class="font-bold uppercase shrink-0" x-text="'✓ ' + correctAnswers[txt.id]"></span>
                                                <span class="opacity-70 truncate" x-text="getHeadlineText(correctAnswers[txt.id])"></span>
                                            </span>
                                        </template>
                                        <template x-if="!submitted">
                                            <button @click.stop="unassign(txt.id)" class="text-slate-600 hover:text-red-400 transition-colors ml-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                            </button>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!answers[txt.id]">
                                    <span class="text-xs text-slate-600 italic">لم يُختر بعد</span>
                                </template>
                            </div>
                        </div>

                        {{-- German text --}}
                        <div class="px-4 py-4 text-sm text-slate-300 leading-relaxed" x-text="txt.content"></div>

                        {{-- Arabic summary --}}
                        <template x-if="txt.summary">
                        <div x-data="{ open: false }" class="border-t border-white/[0.05]">
                            <button @click.stop="open = !open" class="w-full px-4 py-2 flex items-center gap-1 text-xs text-slate-600 hover:text-amber-400 transition-colors" dir="rtl">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open?'rotate-180':''" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                                <span x-text="open ? 'إخفاء الترجمة' : 'عرض الترجمة'"></span>
                            </button>
                            <div x-show="open" x-collapse class="px-4 pb-4 text-sm text-slate-400 leading-relaxed whitespace-pre-line" dir="rtl" x-text="txt.summary"></div>
                        </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- RIGHT: Headlines (always-visible column, header + scroll list + submit) --}}
            <div class="h-full flex flex-col gap-2"
                 @dragover.prevent
                 @drop.prevent="dropOnPanel($event)">
                <div class="shrink-0 text-xs text-slate-500 uppercase tracking-wider px-1" dir="rtl">العناوين — اختر واحداً</div>
                <div class="flex-1 overflow-y-auto pr-1 space-y-2">
                    <template x-for="h in parts.teil1.headlines" :key="h.id">
                        <div class="flex items-start gap-3 px-4 py-3 rounded-xl border transition-all duration-150"
                             :class="headlineClass(h.id)"
                             :draggable="!submitted && !isHeadlineUsed(h.id)"
                             @dragstart="dragHeadline($event, h.id)"
                             @dragend="dragging = null"
                             @click="assignFromPanel(h.id)">
                            <span class="shrink-0 w-6 h-6 flex items-center justify-center rounded-md text-xs font-bold uppercase"
                                  :class="isHeadlineUsed(h.id) ? 'bg-white/5 text-slate-600' : 'bg-amber-500/15 text-amber-400'"
                                  x-text="h.id"></span>
                            <span class="text-sm leading-snug flex-1"
                                  :class="isHeadlineUsed(h.id) ? 'text-slate-600 line-through' : 'text-slate-300'"
                                  x-text="h.text"></span>
                            <template x-if="isHeadlineUsed(h.id)">
                                <span class="shrink-0 text-xs text-slate-700 font-mono" x-text="'→'+getAssignedText(h.id)"></span>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Submit button (always pinned at the bottom of the column) --}}
                <div class="shrink-0 pt-1">
                    <template x-if="!submitted">
                        <button @click="submitAnswers()"
                                :disabled="Object.keys(answers).length < parts.teil1.texts.length"
                                class="btn-shine w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            <span x-text="Object.keys(answers).length + '/' + parts.teil1.texts.length + ' — تصحيح الإجابات'"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- MOBILE ──────────────────────────────────────────────── --}}
        <div class="lg:hidden pb-28 pt-14">

            {{-- View toggle label --}}
            <div class="flex justify-center mb-4">
                <div class="inline-flex items-center gap-1 bg-white/5 border border-white/10 rounded-full p-1 text-xs font-medium">
                    <button @click="mobileView = 'texts'"
                            :class="mobileView === 'texts' ? 'bg-amber-600 text-white' : 'text-slate-400'"
                            class="px-4 py-1.5 rounded-full transition-all">النصوص</button>
                    <button @click="mobileView = 'titles'"
                            :class="mobileView === 'titles' ? 'bg-amber-600 text-white' : 'text-slate-400'"
                            class="px-4 py-1.5 rounded-full transition-all">العناوين</button>
                </div>
            </div>

            {{-- TEXTS VIEW --}}
            <div x-show="mobileView === 'texts'">

            <div class="space-y-3 pt-2">
                <template x-for="txt in parts.teil1.texts" :key="txt.id">
                    <div :id="'mob-text-' + txt.id"
                         class="rounded-2xl border bg-[#111216] overflow-hidden transition-all"
                         :class="submitted
                             ? (answers[txt.id] === correctAnswers[txt.id] ? 'border-green-500/40' : 'border-red-500/40')
                             : answers[txt.id] ? 'border-white/20' : 'border-white/[0.08]'">

                        {{-- Tappable header → open sheet --}}
                        <button @click="!submitted && openSheet(txt.id)"
                                class="w-full flex items-center justify-between px-4 py-3 border-b border-white/[0.05] text-left"
                                :class="!submitted ? 'active:bg-white/5' : 'cursor-default'">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold"
                                      :class="sheetOpen && sheetTargetText === txt.id ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400'"
                                      x-text="txt.id"></span>
                                <span class="text-xs text-slate-500">نص</span>
                            </div>
                            <div class="flex items-center gap-2 min-w-0">
                                {{-- Assigned badge --}}
                                <template x-if="answers[txt.id] && !submitted">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-xs font-medium border bg-amber-500/15 border-amber-500/30 text-amber-300 max-w-[160px]">
                                        <span class="font-bold uppercase shrink-0" x-text="answers[txt.id]"></span>
                                        <span class="truncate opacity-70" x-text="getHeadlineText(answers[txt.id])"></span>
                                    </span>
                                </template>
                                {{-- Submitted: wrong --}}
                                <template x-if="submitted && answers[txt.id] !== correctAnswers[txt.id]">
                                    <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium border bg-red-500/10 border-red-500/30 text-red-400 max-w-[130px]">
                                            <span class="font-bold uppercase shrink-0" x-text="answers[txt.id]"></span>
                                            <span class="truncate opacity-70" x-text="getHeadlineText(answers[txt.id])"></span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium border bg-green-500/10 border-green-500/30 text-green-400 max-w-[130px]">
                                            <span class="font-bold uppercase shrink-0" x-text="'✓ ' + correctAnswers[txt.id]"></span>
                                            <span class="truncate opacity-70" x-text="getHeadlineText(correctAnswers[txt.id])"></span>
                                        </span>
                                    </div>
                                </template>
                                {{-- Submitted: correct --}}
                                <template x-if="submitted && answers[txt.id] === correctAnswers[txt.id]">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-xs font-medium border bg-green-500/10 border-green-500/30 text-green-400 max-w-[160px]">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M20 6 9 17l-5-5"/></svg>
                                        <span class="font-bold uppercase shrink-0" x-text="answers[txt.id]"></span>
                                        <span class="truncate opacity-70" x-text="getHeadlineText(answers[txt.id])"></span>
                                    </span>
                                </template>
                                {{-- No answer yet --}}
                                <template x-if="!answers[txt.id] && !submitted">
                                    <span class="text-xs text-slate-600 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                        اختر عنواناً
                                    </span>
                                </template>
                            </div>
                        </button>

                        {{-- German text --}}
                        <div class="px-4 py-4 text-sm text-slate-300 leading-relaxed" x-text="txt.content"></div>

                        {{-- Arabic summary --}}
                        <template x-if="txt.summary">
                        <div x-data="{open:false}" class="border-t border-white/[0.05]">
                            <button @click="open=!open" class="w-full px-4 py-2 flex items-center gap-1 text-xs text-slate-600 active:text-amber-400 transition-colors" dir="rtl">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open?'rotate-180':''" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                                <span x-text="open?'إخفاء الترجمة':'عرض الترجمة'"></span>
                            </button>
                            <div x-show="open" x-collapse class="px-4 pb-4 text-sm text-slate-400 leading-relaxed whitespace-pre-line" dir="rtl" x-text="txt.summary"></div>
                        </div>
                        </template>
                    </div>
                </template>
            </div>
            </div>{{-- end texts view --}}

            {{-- TITLES VIEW --}}
            <div x-show="mobileView === 'titles'" class="space-y-2">
                <p class="text-xs text-slate-500 mb-3 text-center" dir="rtl">اضغط على عنوان لتحديده، ثم انتقل للنصوص</p>
                <template x-for="h in parts.teil1.headlines" :key="h.id">
                    <button @click="selectHeadlineAndSwitch(h.id)"
                            :disabled="submitted"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border text-right transition-all"
                            :class="isHeadlineUsed(h.id)
                                ? 'border-white/[0.05] opacity-40 cursor-default'
                                : selectedHeadline === h.id
                                    ? 'border-amber-500 bg-amber-500/10'
                                    : 'border-white/[0.08] bg-[#111216] active:bg-white/5'">
                        <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold uppercase"
                              :class="selectedHeadline === h.id ? 'bg-amber-600 text-white' : 'bg-white/5 text-amber-400'"
                              x-text="h.id"></span>
                        <span class="text-sm text-slate-300 text-left flex-1 leading-snug" x-text="h.text"></span>
                        <template x-if="isHeadlineUsed(h.id)">
                            <span class="text-xs text-slate-600 shrink-0" x-text="'→ '+getAssignedText(h.id)"></span>
                        </template>
                    </button>
                </template>
            </div>
        </div>

        {{-- MOBILE: Bottom sheet backdrop --}}
        <div x-show="sheetOpen"
             x-cloak
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sheetOpen = false"
             class="fixed inset-0 z-[75] lg:hidden bg-black/70"></div>

        {{-- MOBILE: Bottom sheet panel --}}
        <div x-show="sheetOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed bottom-0 left-0 right-0 z-[60] lg:hidden bg-[#111216] rounded-t-2xl border-t border-white/10 overflow-hidden touch-pan-y"
             style="max-height:75vh">
            <div style="max-height:75vh; overflow-y:auto; overscroll-behavior:contain">
                {{-- Handle + title --}}
                <div class="sticky top-0 bg-[#111216] pt-3 pb-2 px-4 border-b border-white/[0.06]">
                    <div class="w-10 h-1 rounded-full bg-white/20 mx-auto mb-3"></div>
                    <div class="flex items-center justify-between" dir="rtl">
                        <span class="text-sm font-bold text-white">
                            اختر عنواناً للنص <span class="text-amber-400" x-text="sheetTargetText"></span>
                        </span>
                        <button @click="sheetOpen = false" class="text-slate-500 p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                {{-- Headlines list --}}
                <div class="p-3 space-y-2 pb-6">
                    <template x-for="h in parts.teil1.headlines" :key="h.id">
                        <button @click="pickHeadlineFromSheet(h.id)"
                                class="w-full flex items-center gap-3 px-4 py-3.5 rounded-xl border text-left transition-all active:scale-[0.98]"
                                :class="answers[sheetTargetText] === h.id
                                    ? 'border-amber-500 bg-amber-500/10'
                                    : isHeadlineUsed(h.id) && answers[sheetTargetText] !== h.id
                                        ? 'border-white/[0.04] opacity-35'
                                        : 'border-white/[0.08] bg-[#0B0C10] active:bg-white/5'">
                            <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold uppercase"
                                  :class="answers[sheetTargetText] === h.id ? 'bg-amber-600 text-white' : 'bg-white/5 text-amber-400'"
                                  x-text="h.id"></span>
                            <span class="text-sm text-slate-300 leading-snug flex-1" x-text="h.text"></span>
                            <template x-if="answers[sheetTargetText] === h.id">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="text-amber-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            </template>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- MOBILE: Fixed text navigator (shown in texts view only) --}}
        <div x-show="mobileView === 'texts'"
             x-cloak
             class="fixed top-16 left-0 right-0 z-40 lg:hidden bg-[#08090C]/95 backdrop-blur-md border-b border-white/[0.08] px-4 py-2">
            <div class="flex items-center justify-between gap-1.5">
                <template x-for="txt in parts.teil1.texts" :key="txt.id">
                    <button @click="navigateTo(txt.id)"
                            class="flex-1 flex flex-col items-center gap-1 py-1.5 rounded-xl border transition-all active:scale-95"
                            :class="submitted
                                ? (answers[txt.id] === correctAnswers[txt.id] ? 'border-green-500/40 bg-green-500/5' : 'border-red-500/40 bg-red-500/5')
                                : answers[txt.id] ? 'border-amber-500/40 bg-amber-500/5' : 'border-white/[0.06] bg-transparent'">
                        <span class="text-xs font-bold leading-none"
                              :class="submitted
                                  ? (answers[txt.id] === correctAnswers[txt.id] ? 'text-green-400' : 'text-red-400')
                                  : answers[txt.id] ? 'text-amber-300' : 'text-slate-600'"
                              x-text="txt.id"></span>
                        <span class="w-1.5 h-1.5 rounded-full"
                              :class="submitted
                                  ? (answers[txt.id] === correctAnswers[txt.id] ? 'bg-green-500' : 'bg-red-400')
                                  : answers[txt.id] ? 'bg-amber-500' : 'bg-slate-700'"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- MOBILE: Sticky bottom bar --}}
        <div class="fixed bottom-0 left-0 right-0 lg:hidden z-40 px-4 py-3 bg-[#08090C]/95 backdrop-blur border-t border-white/[0.08]">
            <template x-if="!submitted">
                <div class="flex gap-3">
                    <button @click="mobileView = mobileView === 'texts' ? 'titles' : 'texts'"
                            class="flex-1 py-3 rounded-xl border border-white/10 text-sm font-medium text-slate-300 flex items-center justify-center gap-2 transition-all active:bg-white/5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                        <span x-text="mobileView === 'texts' ? 'العناوين' : 'النصوص'"></span>
                    </button>
                    <button @click="submitAnswers()"
                            :disabled="Object.keys(answers).length < parts.teil1.texts.length"
                            class="btn-shine flex-[2] py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span x-text="'Abgabe (' + Object.keys(answers).length + '/' + parts.teil1.texts.length + ')'"></span>
                    </button>
                </div>
            </template>
            <template x-if="submitted">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-bold" :class="score === total ? 'text-green-400' : score >= total/2 ? 'text-yellow-400' : 'text-red-400'"
                         dir="rtl" x-text="score + '/' + total + ' إجابة صحيحة'"></div>
                    <button @click="reset()" class="px-5 py-2.5 rounded-xl border border-white/10 text-sm text-slate-300 font-medium" dir="rtl">إعادة المحاولة</button>
                </div>
            </template>
        </div>

      </div>
      </template>
    </div>

    {{-- ── TEIL 2 ─────────────────────────────────────────────────── --}}
    <div x-show="activePart === 'teil2'" x-cloak>
      <template x-if="parts.teil2">
      <div>

        {{-- Score bar (after submit) --}}
        <template x-if="submitted">
            <div class="mb-6 p-4 rounded-xl flex items-center justify-between gap-4"
                 :class="score === total ? 'bg-green-500/10 border border-green-500/20' : score >= total/2 ? 'bg-yellow-500/10 border border-yellow-500/20' : 'bg-red-500/10 border border-red-500/20'">
                <div dir="rtl">
                    <div class="font-bold text-white text-lg" x-text="score === total ? '🎉 ممتاز! كل الأجوبة صحيحة' : score + '/' + total + ' إجابة صحيحة'"></div>
                    <div class="text-sm text-slate-400 mt-0.5" x-text="score === total ? 'أحسنت! انتقل للجزء التالي.' : 'راجع الأجوبة الخاطئة بالأسفل.'"></div>
                </div>
                <button @click="reset()" class="shrink-0 px-4 py-2 rounded-xl border border-white/10 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-all" dir="rtl">إعادة المحاولة</button>
            </div>
        </template>

        {{-- DESKTOP: two-column layout --}}
        <div class="hidden lg:grid grid-cols-[1fr_440px] gap-6 items-start lg:h-[calc(100vh-10rem)]">

            {{-- LEFT: Long reading text (own scroll) --}}
            <div class="h-full overflow-y-auto pr-2">
                <article class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
                    <div class="px-6 py-3 border-b border-white/[0.05] flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Leseverstehen Teil 2</span>
                        <span class="text-xs text-slate-600" x-text="(parts.teil2.questions?.length ?? 0) + ' Fragen'"></span>
                    </div>
                    <template x-if="parts.teil2.textTitle">
                        <h2 class="px-6 pt-5 pb-1 text-xl md:text-2xl font-black tracking-tight text-white" x-text="parts.teil2.textTitle"></h2>
                    </template>
                    <div class="px-6 py-6 text-[15px] text-slate-200 leading-[1.85] whitespace-pre-line"
                         x-text="parts.teil2.textContent"></div>

                    {{-- Optional Arabic summary --}}
                    <template x-if="parts.teil2.summary">
                    <div x-data="{ open: false }" class="border-t border-white/[0.05]">
                        <button @click="open = !open" class="w-full px-6 py-3 flex items-center gap-2 text-xs text-slate-500 hover:text-amber-400 transition-colors" dir="rtl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open?'rotate-180':''" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                            <span x-text="open ? 'إخفاء الترجمة' : 'عرض الترجمة'"></span>
                        </button>
                        <div x-show="open" x-collapse class="px-6 pb-5 text-sm text-slate-400 leading-relaxed whitespace-pre-line" dir="rtl" x-text="parts.teil2.summary"></div>
                    </div>
                    </template>
                </article>
            </div>

            {{-- RIGHT: Questions (always visible — header + scroll list + submit) --}}
            <div class="h-full flex flex-col gap-3">
                <div class="shrink-0 text-xs text-slate-500 uppercase tracking-wider px-1" dir="rtl">الأسئلة — اختر إجابة واحدة لكل سؤال</div>
                <div class="flex-1 overflow-y-auto pr-1 space-y-5">

                <template x-for="(q, qi) in parts.teil2.questions" :key="qi">
                    <div class="rounded-2xl border bg-[#111216] overflow-hidden transition-all"
                         :class="submitted
                             ? (answers[qi] === correctAnswers[qi] ? 'border-green-500/40' : 'border-red-500/40')
                             : answers[qi] !== undefined ? 'border-amber-500/40' : 'border-white/[0.08]'">

                        {{-- Question header --}}
                        <div class="px-4 py-3 border-b border-white/[0.05] flex items-start gap-3">
                            <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold"
                                  :class="submitted
                                      ? (answers[qi] === correctAnswers[qi] ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400')
                                      : answers[qi] !== undefined ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400'"
                                  x-text="q.id ?? (qi + 6)"></span>
                            <p class="text-sm font-bold text-white leading-snug" x-text="q.text"></p>
                        </div>

                        {{-- Options --}}
                        <div class="p-3 space-y-2">
                            <template x-for="(opt, oi) in q.options" :key="oi">
                                <button @click="selectOption(qi, oi)"
                                        :disabled="submitted"
                                        class="w-full text-left px-4 py-3 rounded-xl border text-sm transition-all flex items-start gap-3 group/opt"
                                        :class="submitted
                                            ? (oi === correctAnswers[qi]
                                                ? 'border-green-500/50 bg-green-500/10 text-green-100'
                                                : oi === answers[qi]
                                                    ? 'border-red-500/50 bg-red-500/10 text-red-100'
                                                    : 'border-white/[0.05] bg-transparent text-slate-500 cursor-default')
                                            : answers[qi] === oi
                                                ? 'border-amber-500 bg-amber-500/10 text-white'
                                                : 'border-white/[0.08] bg-[#0B0C10] text-slate-300 hover:border-white/20 hover:bg-white/[0.04] cursor-pointer'">
                                    <span class="shrink-0 w-6 h-6 flex items-center justify-center rounded-md text-[11px] font-black uppercase mt-0.5"
                                          :class="submitted
                                              ? (oi === correctAnswers[qi]
                                                  ? 'bg-green-500/20 text-green-300'
                                                  : oi === answers[qi] ? 'bg-red-500/20 text-red-300' : 'bg-white/5 text-slate-600')
                                              : answers[qi] === oi ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-500 group-hover/opt:bg-amber-500/15 group-hover/opt:text-amber-300'"
                                          x-text="['a','b','c'][oi]"></span>
                                    <span class="leading-snug flex-1" x-text="opt"></span>
                                    <template x-if="submitted && oi === correctAnswers[qi]">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" class="text-green-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    </template>
                                    <template x-if="submitted && oi === answers[qi] && oi !== correctAnswers[qi]">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" class="text-red-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
                </div>{{-- /scroll list --}}

                {{-- Submit button (always pinned at the bottom of the column) --}}
                <div class="shrink-0">
                    <template x-if="!submitted">
                        <button @click="submitAnswers()"
                                :disabled="Object.keys(answers).length < (parts.teil2.questions?.length ?? 0)"
                                class="btn-shine w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            <span x-text="Object.keys(answers).length + '/' + (parts.teil2.questions?.length ?? 0) + ' — تصحيح الإجابات'"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- MOBILE ──────────────────────────────────────────────── --}}
        {{-- Text is the only main view; questions live in a bottom sheet --}}
        <div class="lg:hidden pb-28 pt-14">
            <article class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
                <div class="px-4 py-2.5 border-b border-white/[0.05]">
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Leseverstehen Teil 2</span>
                </div>
                <template x-if="parts.teil2.textTitle">
                    <h2 :id="'mob-para-0'" class="px-4 pt-4 pb-1 text-lg font-black tracking-tight text-white scroll-mt-32" x-text="parts.teil2.textTitle"></h2>
                </template>
                {{-- Paragraphs as discrete scroll targets --}}
                <div class="px-4 py-5 space-y-2">
                    <template x-for="(p, pi) in paragraphs" :key="pi">
                        <p :id="'mob-para-' + (pi + 1)"
                           class="text-[15px] leading-[1.85] scroll-mt-32 whitespace-pre-line -mx-2 px-3 py-2 border-r-2 rounded-md transition-all duration-500"
                           :class="highlightedPara === (pi + 1)
                               ? 'text-white bg-amber-500/[0.04] border-amber-500/40'
                               : 'text-slate-200 bg-transparent border-transparent'"
                           x-text="p"></p>
                    </template>
                </div>
                <template x-if="parts.teil2.summary">
                <div x-data="{open:false}" class="border-t border-white/[0.05]">
                    <button @click="open=!open" class="w-full px-4 py-2 flex items-center gap-1 text-xs text-slate-600 active:text-amber-400 transition-colors" dir="rtl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open?'rotate-180':''" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                        <span x-text="open?'إخفاء الترجمة':'عرض الترجمة'"></span>
                    </button>
                    <div x-show="open" x-collapse class="px-4 pb-4 text-sm text-slate-400 leading-relaxed whitespace-pre-line" dir="rtl" x-text="parts.teil2.summary"></div>
                </div>
                </template>
            </article>
        </div>{{-- end mobile main container --}}

        {{-- MOBILE: Fixed top navigator — paragraph chips --}}
        <div x-cloak
             class="fixed top-16 left-0 right-0 z-40 lg:hidden bg-[#08090C]/95 backdrop-blur-md border-b border-white/[0.08] px-3 py-2">
            <div class="flex items-center gap-1.5 overflow-x-auto" dir="ltr">
                <span class="shrink-0 text-[9px] font-black uppercase tracking-widest text-slate-600 px-1">¶</span>
                <template x-for="(p, pi) in paragraphs" :key="pi">
                    <button @click="scrollToPara(pi + 1)"
                            class="shrink-0 w-9 h-9 flex items-center justify-center rounded-lg border text-xs font-bold transition-all active:scale-95"
                            :class="highlightedPara === (pi + 1) ? 'border-amber-500 bg-amber-500/15 text-white' : 'border-white/[0.06] text-slate-500 active:bg-white/5'"
                            x-text="pi + 1"></button>
                </template>
            </div>
        </div>

        {{-- MOBILE: Sticky bottom bar (always visible above the text) --}}
        <div class="fixed bottom-0 left-0 right-0 lg:hidden z-40 px-4 py-3 bg-[#08090C]/95 backdrop-blur border-t border-white/[0.08]">
            <template x-if="!submitted">
                <button @click="qSheetOpen = true"
                        class="btn-shine w-full py-3 rounded-xl bg-amber-600 active:bg-amber-500 text-white text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                    <span x-text="'الأسئلة (' + Object.keys(answers).length + '/' + (parts.teil2.questions?.length ?? 0) + ')'"></span>
                </button>
            </template>
            <template x-if="submitted">
                <div class="flex items-center justify-between gap-3">
                    <button @click="qSheetOpen = true"
                            class="px-4 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        مراجعة
                    </button>
                    <div class="text-sm font-bold flex-1 text-center" :class="score === total ? 'text-green-400' : score >= total/2 ? 'text-yellow-400' : 'text-red-400'"
                         dir="rtl" x-text="score + '/' + total + ' إجابة صحيحة'"></div>
                    <button @click="reset()" class="px-4 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium" dir="rtl">إعادة المحاولة</button>
                </div>
            </template>
        </div>

        {{-- MOBILE: Bottom sheet backdrop (questions) --}}
        <div x-show="qSheetOpen"
             x-cloak
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="qSheetOpen = false"
             class="fixed inset-0 z-[75] lg:hidden bg-black/70"></div>

        {{-- MOBILE: Bottom sheet panel (Fragen) --}}
        <div x-show="qSheetOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed bottom-0 left-0 right-0 z-[60] lg:hidden bg-[#0B0C10] rounded-t-2xl border-t border-white/10 flex flex-col touch-pan-y"
             style="height:88vh">

            {{-- Sticky header: handle + title + close + question chips --}}
            <div class="shrink-0 bg-[#0B0C10] border-b border-white/[0.06] pt-3 px-3">
                <div class="w-10 h-1 rounded-full bg-white/20 mx-auto mb-3"></div>
                <div class="flex items-center justify-between mb-2 px-1" dir="rtl">
                    <div>
                        <div class="text-sm font-bold text-white">الأسئلة</div>
                        <div class="text-[10px] text-slate-500 mt-0.5" x-text="Object.keys(answers).length + '/' + (parts.teil2.questions?.length ?? 0) + ' تم الإجابة'"></div>
                    </div>
                    <button @click="qSheetOpen = false" class="text-slate-500 active:text-white p-1.5 -m-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                {{-- Question chips for in-sheet navigation --}}
                <div class="flex items-center justify-between gap-1.5 pb-2">
                    <template x-for="(q, qi) in parts.teil2.questions" :key="qi">
                        <button @click="scrollToQuestion(qi)"
                                class="flex-1 flex flex-col items-center gap-1 py-1.5 rounded-xl border transition-all active:scale-95"
                                :class="submitted
                                    ? (answers[qi] === correctAnswers[qi] ? 'border-green-500/40 bg-green-500/5' : 'border-red-500/40 bg-red-500/5')
                                    : answers[qi] !== undefined ? 'border-amber-500/40 bg-amber-500/5' : 'border-white/[0.06] bg-transparent'">
                            <span class="text-xs font-bold leading-none"
                                  :class="submitted
                                      ? (answers[qi] === correctAnswers[qi] ? 'text-green-400' : 'text-red-400')
                                      : answers[qi] !== undefined ? 'text-amber-300' : 'text-slate-600'"
                                  x-text="q.id ?? (qi + 6)"></span>
                            <span class="w-1.5 h-1.5 rounded-full"
                                  :class="submitted
                                      ? (answers[qi] === correctAnswers[qi] ? 'bg-green-500' : 'bg-red-400')
                                      : answers[qi] !== undefined ? 'bg-amber-500' : 'bg-slate-700'"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Scrollable question cards --}}
            <div class="flex-1 overflow-y-auto p-3 space-y-3" style="overscroll-behavior:contain">
                <template x-for="(q, qi) in parts.teil2.questions" :key="qi">
                    <div :id="'mob-q-' + qi"
                         class="rounded-2xl border bg-[#111216] overflow-hidden transition-all scroll-mt-2"
                         :class="submitted
                             ? (answers[qi] === correctAnswers[qi] ? 'border-green-500/40' : 'border-red-500/40')
                             : answers[qi] !== undefined ? 'border-white/20' : 'border-white/[0.08]'">

                        {{-- Header: number + stem --}}
                        <div class="px-4 py-3 border-b border-white/[0.05] flex items-start gap-3">
                            <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold mt-0.5"
                                  :class="submitted
                                      ? (answers[qi] === correctAnswers[qi] ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400')
                                      : answers[qi] !== undefined ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400'"
                                  x-text="q.id ?? (qi + 6)"></span>
                            <p class="text-sm font-bold text-white leading-snug flex-1" x-text="q.text"></p>
                        </div>

                        {{-- Options inline --}}
                        <div class="p-3 space-y-2">
                            <template x-for="oi in [0,1,2]" :key="oi">
                                <button @click="selectOption(qi, oi)"
                                        :disabled="submitted"
                                        class="w-full text-left px-4 py-3 rounded-xl border text-sm transition-all flex items-start gap-3 active:scale-[0.99]"
                                        :class="submitted
                                            ? (oi === correctAnswers[qi]
                                                ? 'border-green-500/50 bg-green-500/10 text-green-100'
                                                : oi === answers[qi]
                                                    ? 'border-red-500/50 bg-red-500/10 text-red-100'
                                                    : 'border-white/[0.04] bg-transparent text-slate-500')
                                            : answers[qi] === oi
                                                ? 'border-amber-500 bg-amber-500/10 text-white shadow-lg shadow-amber-500/5'
                                                : 'border-white/[0.08] bg-[#0B0C10] text-slate-300 active:bg-white/5'">
                                    <span class="shrink-0 w-6 h-6 flex items-center justify-center rounded-md text-[11px] font-black uppercase mt-0.5"
                                          :class="submitted
                                              ? (oi === correctAnswers[qi]
                                                  ? 'bg-green-500/20 text-green-300'
                                                  : oi === answers[qi] ? 'bg-red-500/20 text-red-300' : 'bg-white/5 text-slate-600')
                                              : answers[qi] === oi ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-500'"
                                          x-text="['a','b','c'][oi]"></span>
                                    <span class="leading-snug flex-1" x-text="q.options[oi]"></span>
                                    <template x-if="submitted && oi === correctAnswers[qi]">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="text-green-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    </template>
                                    <template x-if="submitted && oi === answers[qi] && oi !== correctAnswers[qi]">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="text-red-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Sticky footer: Abgabe / close --}}
            <div class="shrink-0 border-t border-white/[0.06] p-3 bg-[#0B0C10]">
                <template x-if="!submitted">
                    <button @click="submitAnswers()"
                            :disabled="Object.keys(answers).length < (parts.teil2.questions?.length ?? 0)"
                            class="btn-shine w-full py-3 rounded-xl bg-amber-600 active:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <span x-text="'Abgabe (' + Object.keys(answers).length + '/' + (parts.teil2.questions?.length ?? 0) + ')'"></span>
                    </button>
                </template>
                <template x-if="submitted">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 text-sm font-bold" :class="score === total ? 'text-green-400' : score >= total/2 ? 'text-yellow-400' : 'text-red-400'"
                             dir="rtl" x-text="score + '/' + total + ' إجابة صحيحة'"></div>
                        <button @click="reset()" class="px-4 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium" dir="rtl">إعادة المحاولة</button>
                        <button @click="qSheetOpen = false" class="px-4 py-2.5 rounded-xl bg-white/5 text-xs text-white font-medium" dir="rtl">إغلاق</button>
                    </div>
                </template>
            </div>
        </div>

      </div>
      </template>
    </div>

    {{-- ── TEIL 3 ─────────────────────────────────────────────────── --}}
    {{--    Zuordnungen: match each situation (11..20) to one of 13 ads (a..l + x) --}}
    <div x-show="activePart === 'teil3'" x-cloak>
      <template x-if="parts.teil3">
      <div>

        {{-- Score bar (after submit) --}}
        <template x-if="submitted">
            <div class="mb-6 p-4 rounded-xl flex items-center justify-between gap-4"
                 :class="score === total ? 'bg-green-500/10 border border-green-500/20' : score >= total/2 ? 'bg-yellow-500/10 border border-yellow-500/20' : 'bg-red-500/10 border border-red-500/20'">
                <div dir="rtl">
                    <div class="font-bold text-white text-lg" x-text="score === total ? '🎉 ممتاز! كل الأجوبة صحيحة' : score + '/' + total + ' إجابة صحيحة'"></div>
                    <div class="text-sm text-slate-400 mt-0.5" x-text="score === total ? 'أحسنت!' : 'راجع الأجوبة الخاطئة بالأسفل.'"></div>
                </div>
                <button @click="reset()" class="shrink-0 px-4 py-2 rounded-xl border border-white/10 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-all" dir="rtl">إعادة المحاولة</button>
            </div>
        </template>

        {{-- DESKTOP: two-column layout — both columns viewport-bound, scroll independently --}}
        <div class="hidden lg:grid grid-cols-[1fr_420px] gap-6 items-start lg:h-[calc(100vh-10rem)]">

            {{-- LEFT: Ads (own scroll) --}}
            <div class="h-full overflow-y-auto pr-2 space-y-3">
                <template x-for="ad in parts.teil3.ads" :key="ad.id">
                    <article :id="'ad-' + ad.id"
                             class="rounded-2xl border bg-[#111216] overflow-hidden transition-all scroll-mt-2"
                             :class="selectedAd === ad.id
                                 ? 'border-orange-500 shadow-lg shadow-orange-500/10'
                                 : isAdPicked(ad.id)
                                     ? 'border-amber-500/30'
                                     : 'border-white/[0.08] hover:border-white/20'">
                        <button @click="selectAd(ad.id)"
                                :draggable="!submitted && ad.id !== 'x'"
                                @dragstart="dragAd($event, ad.id)"
                                @dragend="dragging = null"
                                class="w-full text-left flex items-start gap-3 px-4 py-3 border-b border-white/[0.05]"
                                :class="!submitted ? 'cursor-pointer active:bg-white/5' : 'cursor-default'">
                            <span class="shrink-0 w-9 h-9 flex items-center justify-center rounded-xl text-base font-black uppercase mt-0.5"
                                  :class="ad.id === 'x'
                                      ? 'bg-slate-500/10 text-slate-400 border border-slate-500/30'
                                      : selectedAd === ad.id
                                          ? 'bg-orange-600 text-white'
                                          : isAdPicked(ad.id) ? 'bg-amber-500/20 text-amber-300' : 'bg-white/5 text-amber-400'"
                                  x-text="ad.id"></span>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-white leading-snug" x-text="ad.title || 'Keine passende Anzeige'"></h3>
                                <template x-if="adAssignedSituations(ad.id).length">
                                    <div class="mt-1.5 space-y-1">
                                        <template x-for="sn in adAssignedSituations(ad.id)" :key="sn">
                                            <div class="flex items-start gap-2">
                                                <span class="shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-amber-500/15 text-amber-300 mt-0.5" x-text="'مطابق لـ ' + sn"></span>
                                                <span class="text-[11px] text-slate-400 leading-snug flex-1"
                                                      style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"
                                                      x-text="getSituationText(sn)"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </button>
                        <div class="px-4 py-3 text-sm text-slate-300 leading-[1.75] whitespace-pre-line" x-text="ad.text"></div>
                        {{-- Translation only after submission (so users can't peek at the answer) --}}
                        <template x-if="ad.summary && submitted">
                            <div x-data="{ open: false }" class="border-t border-white/[0.05]">
                                <button @click.stop="open = !open" class="w-full px-4 py-2 flex items-center gap-1 text-xs text-slate-600 hover:text-amber-400 transition-colors" dir="rtl">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open?'rotate-180':''" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                                    <span x-text="open ? 'إخفاء الترجمة' : 'عرض الترجمة'"></span>
                                </button>
                                <div x-show="open" x-collapse class="px-4 pb-4 text-sm text-slate-400 leading-relaxed whitespace-pre-line" dir="rtl" x-text="ad.summary"></div>
                            </div>
                        </template>
                    </article>
                </template>
            </div>

            {{-- RIGHT: Situations (full viewport height, scrolls only as last resort) --}}
            <div class="h-full overflow-y-auto pr-1 flex flex-col gap-1.5"
                 @dragover.prevent>
                <div class="shrink-0 flex items-center justify-between px-1" dir="rtl">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">المواقف</span>
                    <span class="text-[10px] text-slate-600" x-text="Object.keys(answers).length + '/' + (parts.teil3.situations?.length ?? 0)"></span>
                </div>
                <template x-for="s in parts.teil3.situations" :key="s.id">
                    <div dir="ltr"
                         class="relative shrink-0 rounded-xl border bg-[#111216] overflow-hidden transition-all"
                         :class="submitted
                             ? (answers[s.id] === correctAnswers[s.id] ? 'border-green-500/40' : 'border-red-500/40')
                             : selectedSituation === s.id
                                 ? 'border-orange-500'
                                 : answers[s.id] !== undefined ? 'border-amber-500/40' : 'border-white/[0.08]'"
                         @dragover.prevent
                         @drop.prevent="dropOnSituation($event, s.id)">
                        <button @click="!submitted && selectSituation(s.id)"
                                :disabled="submitted"
                                class="w-full flex items-start gap-2 px-2.5 py-2 pe-10 text-left"
                                :class="!submitted ? 'active:bg-white/5' : 'cursor-default'">
                            <span class="shrink-0 w-6 h-6 flex items-center justify-center rounded-md text-[11px] font-bold mt-0.5"
                                  :class="submitted
                                      ? (answers[s.id] === correctAnswers[s.id] ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400')
                                      : selectedSituation === s.id
                                          ? 'bg-orange-600 text-white'
                                          : answers[s.id] !== undefined ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400'"
                                  x-text="s.id"></span>
                            <p class="text-[13px] text-white leading-snug flex-1" x-text="s.text"></p>
                        </button>

                        {{-- Quick X (no-match) toggle, top-right corner --}}
                        <button @click.stop="toggleAdAssignment(s.id, 'x')"
                                :disabled="submitted"
                                class="absolute top-1.5 right-1.5 w-7 h-6 flex items-center justify-center rounded-md border text-[10px] font-black uppercase transition-all"
                                :class="submitted
                                    ? (correctAnswers[s.id] === 'x'
                                        ? 'border-green-500/40 bg-green-500/10 text-green-300'
                                        : answers[s.id] === 'x'
                                            ? 'border-red-500/40 bg-red-500/10 text-red-300'
                                            : 'border-white/[0.04] text-slate-700 cursor-default')
                                    : answers[s.id] === 'x'
                                        ? 'border-slate-300 bg-slate-500/30 text-white'
                                        : 'border-white/10 text-slate-500 hover:text-white hover:bg-white/10'"
                                title="لا يوجد إعلان مناسب">x</button>

                        {{-- Status row (compact, only when relevant) --}}
                        <template x-if="answers[s.id] !== undefined && answers[s.id] !== 'x' && !submitted">
                            <div class="px-2.5 pb-2 -mt-1 flex items-center gap-1.5" dir="ltr">
                                <span class="shrink-0 text-[9px] font-black uppercase px-1 py-0.5 rounded bg-amber-500/15 text-amber-300" x-text="answers[s.id]"></span>
                                <span class="text-[11px] text-amber-300 truncate flex-1" x-text="getAdTitle(answers[s.id])"></span>
                                <button @click.stop="toggleAdAssignment(s.id, answers[s.id])"
                                        class="shrink-0 text-slate-600 hover:text-red-400 transition-colors p-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="submitted && answers[s.id] !== correctAnswers[s.id]">
                            <div class="px-2.5 pb-2 -mt-1 space-y-0.5" dir="ltr">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] font-bold px-1 py-0.5 rounded bg-red-500/15 text-red-300 shrink-0" x-text="answers[s.id] ?? '—'"></span>
                                    <span class="text-[11px] text-red-300/80 truncate" x-text="getAdTitle(answers[s.id])"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] font-bold px-1 py-0.5 rounded bg-green-500/15 text-green-300 shrink-0" x-text="'✓ ' + correctAnswers[s.id]"></span>
                                    <span class="text-[11px] text-green-300/80 truncate" x-text="getAdTitle(correctAnswers[s.id])"></span>
                                </div>
                            </div>
                        </template>
                        <template x-if="submitted && answers[s.id] === correctAnswers[s.id] && answers[s.id] !== 'x'">
                            <div class="px-2.5 pb-2 -mt-1 flex items-center gap-1.5" dir="ltr">
                                <span class="text-[9px] font-bold px-1 py-0.5 rounded bg-green-500/15 text-green-300 shrink-0" x-text="'✓ ' + answers[s.id]"></span>
                                <span class="text-[11px] text-green-300/80 truncate" x-text="getAdTitle(answers[s.id])"></span>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Submit button --}}
                <div class="shrink-0 pt-1">
                    <template x-if="!submitted">
                        <button @click="submitAnswers()"
                                :disabled="Object.keys(answers).length < (parts.teil3.situations?.length ?? 0)"
                                class="btn-shine w-full py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            <span x-text="Object.keys(answers).length + '/' + (parts.teil3.situations?.length ?? 0) + ' — تصحيح الإجابات'"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- MOBILE: Situations as main; tap one to open the ads sheet --}}
        <div class="lg:hidden pb-28 pt-14">
            <div class="space-y-3">
                <template x-for="s in parts.teil3.situations" :key="s.id">
                    <div :id="'mob-sit-' + s.id" dir="ltr"
                         class="relative w-full rounded-2xl border bg-[#111216] overflow-hidden transition-all scroll-mt-32"
                         :class="submitted
                             ? (answers[s.id] === correctAnswers[s.id] ? 'border-green-500/40' : 'border-red-500/40')
                             : answers[s.id] !== undefined ? 'border-amber-500/30' : 'border-white/[0.08]'">
                        <button @click="!submitted && openAdsSheetFor(s.id)"
                                :disabled="submitted"
                                class="w-full text-left active:scale-[0.99] active:bg-white/5">
                            <div class="px-4 py-3 pe-12 flex items-start gap-3">
                                <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-lg text-xs font-bold mt-0.5"
                                      :class="submitted
                                          ? (answers[s.id] === correctAnswers[s.id] ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400')
                                          : answers[s.id] !== undefined ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400'"
                                      x-text="s.id"></span>
                                <p class="text-sm text-white leading-snug flex-1" x-text="s.text"></p>
                            </div>
                        </button>

                        {{-- Quick X (no-match) toggle --}}
                        <button @click.stop="toggleAdAssignment(s.id, 'x')"
                                :disabled="submitted"
                                class="absolute top-2 right-2 w-8 h-7 flex items-center justify-center rounded-md border text-[11px] font-black uppercase transition-all"
                                :class="submitted
                                    ? (correctAnswers[s.id] === 'x'
                                        ? 'border-green-500/40 bg-green-500/10 text-green-300'
                                        : answers[s.id] === 'x'
                                            ? 'border-red-500/40 bg-red-500/10 text-red-300'
                                            : 'border-white/[0.04] text-slate-700')
                                    : answers[s.id] === 'x'
                                        ? 'border-slate-300 bg-slate-500/30 text-white'
                                        : 'border-white/10 text-slate-500 active:bg-white/10'"
                                title="لا يوجد إعلان مناسب">x</button>

                        {{-- Status / picked title --}}
                        <div class="px-4 pb-3 -mt-1">
                            <template x-if="answers[s.id] !== undefined && answers[s.id] !== 'x' && !submitted">
                                <div class="flex items-center gap-2" dir="ltr">
                                    <span class="shrink-0 text-[10px] font-black uppercase px-1.5 py-0.5 rounded-md bg-amber-500/15 text-amber-300" x-text="answers[s.id]"></span>
                                    <span class="text-xs text-amber-300 truncate flex-1" x-text="getAdTitle(answers[s.id])"></span>
                                </div>
                            </template>
                            <template x-if="answers[s.id] === undefined && !submitted">
                                <div class="text-[11px] text-slate-600" dir="rtl">اضغط لاختيار إعلان</div>
                            </template>
                            <template x-if="submitted && answers[s.id] !== correctAnswers[s.id]">
                                <div class="space-y-1" dir="ltr">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold px-1.5 rounded bg-red-500/15 text-red-300 shrink-0" x-text="answers[s.id] ?? '—'"></span>
                                        <span class="text-xs text-red-300/80 truncate flex-1" x-text="getAdTitle(answers[s.id])"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold px-1.5 rounded bg-green-500/15 text-green-300 shrink-0" x-text="'✓ ' + correctAnswers[s.id]"></span>
                                        <span class="text-xs text-green-300/80 truncate flex-1" x-text="getAdTitle(correctAnswers[s.id])"></span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="submitted && answers[s.id] === correctAnswers[s.id] && answers[s.id] !== 'x'">
                                <div class="flex items-center gap-2" dir="ltr">
                                    <span class="text-[10px] font-bold px-1.5 rounded bg-green-500/15 text-green-300 shrink-0" x-text="'✓ ' + answers[s.id]"></span>
                                    <span class="text-xs text-green-300/80 truncate flex-1" x-text="getAdTitle(answers[s.id])"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- MOBILE: Top fixed nav — situation number chips --}}
        <div x-cloak
             class="fixed top-16 left-0 right-0 z-40 lg:hidden bg-[#08090C]/95 backdrop-blur-md border-b border-white/[0.08] px-3 py-2">
            <div class="flex items-center justify-between gap-1.5">
                <template x-for="s in parts.teil3.situations" :key="s.id">
                    <button @click="scrollToSituation(s.id)"
                            class="flex-1 flex flex-col items-center gap-1 py-1.5 rounded-xl border transition-all active:scale-95"
                            :class="submitted
                                ? (answers[s.id] === correctAnswers[s.id] ? 'border-green-500/40 bg-green-500/5' : 'border-red-500/40 bg-red-500/5')
                                : answers[s.id] !== undefined ? 'border-amber-500/40 bg-amber-500/5' : 'border-white/[0.06] bg-transparent'">
                        <span class="text-xs font-bold leading-none"
                              :class="submitted
                                  ? (answers[s.id] === correctAnswers[s.id] ? 'text-green-400' : 'text-red-400')
                                  : answers[s.id] !== undefined ? 'text-amber-300' : 'text-slate-600'"
                              x-text="s.id"></span>
                        <span class="w-1.5 h-1.5 rounded-full"
                              :class="submitted
                                  ? (answers[s.id] === correctAnswers[s.id] ? 'bg-green-500' : 'bg-red-400')
                                  : answers[s.id] !== undefined ? 'bg-amber-500' : 'bg-slate-700'"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- MOBILE: Sticky bottom bar --}}
        <div class="fixed bottom-0 left-0 right-0 lg:hidden z-40 px-4 py-3 bg-[#08090C]/95 backdrop-blur border-t border-white/[0.08]">
            <template x-if="!submitted">
                <button @click="submitAnswers()"
                        :disabled="Object.keys(answers).length < (parts.teil3.situations?.length ?? 0)"
                        class="btn-shine w-full py-3 rounded-xl bg-amber-600 active:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20">
                    <span x-text="'Abgabe (' + Object.keys(answers).length + '/' + (parts.teil3.situations?.length ?? 0) + ')'"></span>
                </button>
            </template>
            <template x-if="submitted">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-bold flex-1 text-center" :class="score === total ? 'text-green-400' : score >= total/2 ? 'text-yellow-400' : 'text-red-400'"
                         dir="rtl" x-text="score + '/' + total + ' إجابة صحيحة'"></div>
                    <button @click="reset()" class="px-5 py-2.5 rounded-xl border border-white/10 text-sm text-slate-300 font-medium" dir="rtl">إعادة المحاولة</button>
                </div>
            </template>
        </div>

        {{-- MOBILE: Ads sheet backdrop --}}
        <div x-show="t3SheetOpen"
             x-cloak
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="t3SheetOpen = false"
             class="fixed inset-0 z-[75] lg:hidden bg-black/70"></div>

        {{-- MOBILE: Ads bottom sheet (compact, expandable cards) --}}
        <div x-show="t3SheetOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed bottom-0 left-0 right-0 z-[60] lg:hidden bg-[#0B0C10] rounded-t-2xl border-t border-white/10 flex flex-col touch-pan-y"
             style="height:88vh">

            {{-- Sticky header: handle + active situation summary + close --}}
            <div class="shrink-0 bg-[#0B0C10] border-b border-white/[0.06] pt-3 px-4 pb-3">
                <div class="w-10 h-1 rounded-full bg-white/20 mx-auto mb-3"></div>
                <div class="flex items-start justify-between gap-3" dir="rtl">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-black uppercase tracking-widest text-amber-400">الموقف <span x-text="t3SheetSituation ?? ''"></span></span>
                            <span class="text-[10px] text-slate-600">— اختر الإعلان المناسب</span>
                        </div>
                        <p class="text-sm text-white leading-snug" dir="ltr"
                           x-text="t3SheetSituation !== null ? (parts.teil3.situations.find(s => s.id === t3SheetSituation)?.text ?? '') : ''"></p>
                    </div>
                    <button @click="t3SheetOpen = false" class="text-slate-500 active:text-white p-1.5 -m-1.5 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Scrollable ads list --}}
            <div class="flex-1 overflow-y-auto p-3 space-y-2" style="overscroll-behavior:contain">
                <template x-for="ad in parts.teil3.ads" :key="ad.id">
                    <div class="rounded-2xl border bg-[#111216] overflow-hidden transition-all"
                         :class="t3SheetSituation !== null && answers[t3SheetSituation] === ad.id
                             ? 'border-amber-500 shadow-md shadow-amber-500/20'
                             : 'border-white/[0.08]'">
                        {{-- Header row: tappable card body to assign + arrow to expand --}}
                        <div class="flex items-stretch">
                            <button @click="pickAdForSheetSituation(ad.id)"
                                    class="flex-1 min-w-0 flex items-start gap-3 px-3 py-3 text-left active:bg-white/5">
                                <span class="shrink-0 w-9 h-9 flex items-center justify-center rounded-xl text-base font-black uppercase mt-0.5"
                                      :class="t3SheetSituation !== null && answers[t3SheetSituation] === ad.id
                                          ? 'bg-amber-600 text-white'
                                          : ad.id === 'x'
                                              ? 'bg-slate-500/10 text-slate-400 border border-slate-500/30'
                                              : 'bg-white/5 text-amber-400'"
                                      x-text="ad.id"></span>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-white leading-snug text-[15px]" x-text="ad.title || 'لا يوجد إعلان مناسب'"></h3>
                                    {{-- 2-line preview when not expanded --}}
                                    <template x-if="t3ExpandedAd !== ad.id && ad.text">
                                        <p class="text-xs text-slate-400 mt-1 leading-relaxed"
                                           style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-overflow:ellipsis"
                                           x-text="ad.text"></p>
                                    </template>
                                </div>
                                <template x-if="t3SheetSituation !== null && answers[t3SheetSituation] === ad.id">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="text-amber-400 shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </template>
                            </button>
                            {{-- Expand chevron — separate hit target, doesn't pick --}}
                            <button @click.stop="toggleAdExpand(ad.id)"
                                    class="shrink-0 w-11 flex items-center justify-center border-r border-white/[0.04] active:bg-white/5"
                                    :class="t3ExpandedAd === ad.id ? 'text-amber-400' : 'text-slate-500'"
                                    :aria-label="t3ExpandedAd === ad.id ? 'إخفاء التفاصيل' : 'عرض التفاصيل'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="t3ExpandedAd === ad.id ? 'rotate-180' : ''" class="transition-transform"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                        </div>

                        {{-- Expanded body: full text always; Arabic translation only after submission --}}
                        <div x-show="t3ExpandedAd === ad.id" x-collapse>
                            <div class="px-4 pt-1 pb-3 text-sm text-slate-200 leading-[1.7] whitespace-pre-line border-t border-white/[0.04]" x-text="ad.text"></div>
                            <template x-if="ad.summary && submitted">
                                <div class="px-4 pb-3 pt-2 text-xs text-slate-400 leading-relaxed whitespace-pre-line border-t border-white/[0.04]" dir="rtl" x-text="ad.summary"></div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Sticky footer: prev / next situation nav --}}
            <div class="shrink-0 border-t border-white/[0.06] p-3 bg-[#0B0C10] flex items-center gap-2">
                <button @click="sheetGotoSituation(-1)"
                        :disabled="!sheetCanGoPrev()"
                        class="flex-1 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium flex items-center justify-center gap-1 active:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    السابق
                </button>
                <button @click="t3SheetOpen = false"
                        class="flex-1 py-2.5 rounded-xl bg-white/5 text-xs text-white font-medium" dir="rtl">إغلاق</button>
                <button @click="sheetGotoSituation(1)"
                        :disabled="!sheetCanGoNext()"
                        class="flex-1 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium flex items-center justify-center gap-1 active:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed">
                    التالي
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>

      </div>
      </template>
    </div>

    {{-- ── SPRACHBAUSTEINE 1 (cloze / Lückenfüllung) ───────────────── --}}
    <div x-show="activePart === 'sprachbausteine1'" x-cloak>
      <template x-if="parts.sprachbausteine1">
      <div>

        {{-- Score bar (after submit) --}}
        <template x-if="submitted">
            <div class="mb-4 p-4 rounded-xl flex items-center justify-between gap-4"
                 :class="score === total ? 'bg-green-500/10 border border-green-500/20' : score >= total/2 ? 'bg-yellow-500/10 border border-yellow-500/20' : 'bg-red-500/10 border border-red-500/20'">
                <div dir="rtl">
                    <div class="font-bold text-white text-lg" x-text="score === total ? '🎉 ممتاز! كل الأجوبة صحيحة' : score + '/' + total + ' إجابة صحيحة'"></div>
                    <div class="text-sm text-slate-400 mt-0.5" x-text="score === total ? 'أحسنت!' : 'راجع الأجوبة الخاطئة في النص.'"></div>
                </div>
                <button @click="reset()" class="shrink-0 px-4 py-2 rounded-xl border border-white/10 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-all" dir="rtl">إعادة المحاولة</button>
            </div>
        </template>

        {{-- DESKTOP: text on the left, options panel on the right --}}
        <div class="hidden lg:grid grid-cols-[1fr_400px] gap-6 items-start lg:h-[calc(100vh-10rem)]">

            {{-- LEFT: text article with inline blanks --}}
            <div class="h-full overflow-y-auto pr-2">
            <article class="rounded-2xl border border-white/[0.08] bg-[#111216]">
                <div class="px-6 py-3 border-b border-white/[0.05] flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Sprachbausteine Teil 1</span>
                    <span class="text-xs text-slate-600" x-text="(parts.sprachbausteine1.blanks?.length ?? 0) + ' Lücken'"></span>
                </div>
                <template x-if="parts.sprachbausteine1.textTitle">
                    <h2 class="px-6 pt-5 pb-2 text-xl md:text-2xl font-black tracking-tight text-white" x-text="parts.sprachbausteine1.textTitle"></h2>
                </template>
                <div class="px-6 py-5 text-[15.5px] text-slate-200 leading-relaxed">
                    <template x-for="(para, pi) in sb1Paragraphs" :key="pi">
                        <p class="mb-3 last:mb-0">
                            <template x-for="(seg, si) in para" :key="si">
                                <span>
                                    <template x-if="typeof seg === 'string'">
                                        <span class="whitespace-pre-line" x-text="seg"></span>
                                    </template>
                                    <template x-if="typeof seg === 'object' && seg !== null">
                                        <span class="relative inline-block align-baseline">
                                            <button @click.stop="openBlank(seg.id)"
                                                    class="inline-flex items-baseline gap-1 px-3 py-0.5 mx-1 rounded text-[14px] font-bold transition-all align-baseline border-b-2 min-w-[5.5em] justify-center"
                                                    :class="submitted
                                                        ? (answers[seg.id] === correctAnswers[seg.id]
                                                            ? 'border-green-500 text-green-200 bg-green-500/[0.07]'
                                                            : 'border-red-500 text-red-300 bg-red-500/[0.07] line-through')
                                                        : activeBlank === seg.id
                                                            ? 'border-orange-400 text-white bg-orange-500/15 shadow-sm shadow-orange-500/30'
                                                            : answers[seg.id] !== undefined
                                                                ? 'border-amber-400 text-amber-100 bg-amber-500/[0.08]'
                                                                : 'border-dashed border-slate-400/60 text-slate-500 hover:text-white hover:border-slate-300 hover:bg-white/[0.04]'">
                                                <sup class="text-[9px] opacity-70 font-black" x-text="seg.id"></sup>
                                                <span x-text="answers[seg.id] ?? '   '"></span>
                                            </button>
                                            <template x-if="submitted && answers[seg.id] !== correctAnswers[seg.id]">
                                                <span class="inline-flex items-baseline gap-1 px-2 py-0.5 mx-0.5 rounded-md border border-green-500/50 bg-green-500/10 text-green-200 text-[14px] font-bold align-baseline">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="self-center"><path d="M20 6 9 17l-5-5"/></svg>
                                                    <span x-text="correctAnswers[seg.id]"></span>
                                                </span>
                                            </template>

                                        </span>
                                    </template>
                                </span>
                            </template>
                        </p>
                    </template>
                </div>
            </article>
            </div>{{-- /left column --}}

            {{-- RIGHT: options panel — one card per blank --}}
            <div class="h-full flex flex-col gap-2">
                <div class="shrink-0 flex items-center justify-between px-1" dir="rtl">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">الفراغات</span>
                    <span class="text-[10px] text-slate-600" x-text="Object.keys(answers).length + '/' + total"></span>
                </div>
                <div class="flex-1 overflow-y-auto pr-1 space-y-1">
                    <template x-for="b in (parts.sprachbausteine1.blanks ?? [])" :key="b.id">
                        <div :id="'sb1-card-' + b.id" dir="ltr"
                             class="rounded-md border bg-[#111216] overflow-hidden transition-all scroll-mt-2"
                             :class="submitted
                                 ? (answers[b.id] === correctAnswers[b.id] ? 'border-green-500/40' : 'border-red-500/40')
                                 : activeBlank === b.id
                                     ? 'border-orange-500'
                                     : answers[b.id] !== undefined ? 'border-amber-500/40' : 'border-white/[0.08]'">
                            <div class="flex items-stretch gap-1.5 px-1.5 py-1.5">
                                <button @click="openBlank(b.id)"
                                        :disabled="submitted"
                                        class="shrink-0 w-9 flex items-center justify-center rounded-md text-[13px] font-black"
                                        :class="submitted
                                            ? (answers[b.id] === correctAnswers[b.id] ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400')
                                            : activeBlank === b.id
                                                ? 'bg-orange-600 text-white'
                                                : answers[b.id] !== undefined ? 'bg-amber-600 text-white' : 'bg-white/5 text-slate-400'"
                                        x-text="b.id"></button>
                                <div class="flex-1 grid grid-cols-3 gap-1.5 min-w-0">
                                    <template x-for="opt in b.options" :key="opt">
                                        <button @click="pickBlank(b.id, opt)"
                                                :disabled="submitted"
                                                class="px-1.5 py-2 rounded-md text-[13px] font-medium transition-all truncate"
                                                :class="submitted
                                                    ? (opt === correctAnswers[b.id]
                                                        ? 'border border-green-500/50 bg-green-500/10 text-green-200'
                                                        : opt === answers[b.id]
                                                            ? 'border border-red-500/50 bg-red-500/10 text-red-200'
                                                            : 'border border-white/[0.04] bg-transparent text-slate-600 cursor-default')
                                                    : answers[b.id] === opt
                                                        ? 'border border-amber-500 bg-amber-500/15 text-white'
                                                        : 'border border-white/[0.08] bg-[#0B0C10] text-slate-300 hover:text-white hover:border-white/20'"
                                                :title="opt"
                                                x-text="opt"></button>
                                    </template>
                                </div>
                            </div>
                            {{-- Explanation: shown for all blanks after submit --}}
                            <template x-if="submitted && b.explanation">
                                <div class="px-3 pb-2.5 pt-1.5 text-[12px] text-slate-400 leading-relaxed border-t border-white/[0.04]" dir="rtl" x-text="b.explanation"></div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="shrink-0">
                    <template x-if="!submitted">
                        <button @click="submitAnswers()"
                                :disabled="Object.keys(answers).length < total"
                                class="btn-shine w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            <span x-text="Object.keys(answers).length + '/' + total + ' — تصحيح الإجابات'"></span>
                        </button>
                    </template>
                    <template x-if="submitted">
                        <button @click="reset()" class="w-full py-3 rounded-xl border border-white/10 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-all" dir="rtl">إعادة المحاولة</button>
                    </template>
                </div>
            </div>
        </div>

        {{-- MOBILE: same article, scrolls naturally --}}
        <div class="lg:hidden pb-24 pt-2">
            <article class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
                <div class="px-4 py-2.5 border-b border-white/[0.05] flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Sprachbausteine Teil 1</span>
                    <span class="text-xs text-slate-600" x-text="Object.keys(answers).length + '/' + total"></span>
                </div>
                <template x-if="parts.sprachbausteine1.textTitle">
                    <h2 class="px-4 pt-4 pb-1 text-lg font-black tracking-tight text-white" x-text="parts.sprachbausteine1.textTitle"></h2>
                </template>
                <div class="px-4 py-4 text-[15px] text-slate-200 leading-relaxed">
                    <template x-for="(para, pi) in sb1Paragraphs" :key="pi">
                        <p class="mb-3 last:mb-0">
                            <template x-for="(seg, si) in para" :key="si">
                                <span>
                                    <template x-if="typeof seg === 'string'">
                                        <span class="whitespace-pre-line" x-text="seg"></span>
                                    </template>
                                    <template x-if="typeof seg === 'object' && seg !== null">
                                        <span class="relative inline-block align-baseline">
                                            <button @click.stop="openBlank(seg.id)"
                                                    class="inline-flex items-baseline gap-1 px-3 py-0.5 mx-1 rounded text-[14px] font-bold transition-all align-baseline border-b-2 min-w-[5.5em] justify-center active:scale-95"
                                                    :class="submitted
                                                        ? (answers[seg.id] === correctAnswers[seg.id]
                                                            ? 'border-green-500 text-green-200 bg-green-500/[0.07]'
                                                            : 'border-red-500 text-red-300 bg-red-500/[0.07] line-through')
                                                        : activeBlank === seg.id
                                                            ? 'border-orange-400 text-white bg-orange-500/15'
                                                            : answers[seg.id] !== undefined
                                                                ? 'border-amber-400 text-amber-100 bg-amber-500/[0.08]'
                                                                : 'border-dashed border-slate-400/60 text-slate-500'">
                                                <sup class="text-[9px] opacity-70 font-black" x-text="seg.id"></sup>
                                                <span x-text="answers[seg.id] ?? '   '"></span>
                                            </button>
                                            <template x-if="submitted && answers[seg.id] !== correctAnswers[seg.id]">
                                                <span class="inline-flex items-baseline gap-1 px-2 py-0.5 mx-0.5 rounded-md border border-green-500/50 bg-green-500/10 text-green-200 text-[14px] font-bold align-baseline">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="self-center"><path d="M20 6 9 17l-5-5"/></svg>
                                                    <span x-text="correctAnswers[seg.id]"></span>
                                                </span>
                                            </template>
                                        </span>
                                    </template>
                                </span>
                            </template>
                        </p>
                    </template>
                </div>

            </article>

            {{-- MOBILE: bottom sheet — picker for the active blank --}}
            <div x-show="sb1SheetOpen"
                 x-cloak
                 x-transition:enter="transition-opacity duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sb1SheetOpen = false; activeBlank = null"
                 class="fixed inset-0 z-[75] lg:hidden bg-black/70"></div>

            <div x-show="sb1SheetOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="fixed bottom-0 left-0 right-0 z-[60] lg:hidden bg-[#0B0C10] rounded-t-2xl border-t border-white/10 flex flex-col touch-pan-y"
                 style="max-height:80vh">

                <div class="shrink-0 bg-[#0B0C10] border-b border-white/[0.06] pt-3 px-4 pb-3">
                    <div class="w-10 h-1 rounded-full bg-white/20 mx-auto mb-3"></div>
                    <div class="flex items-center justify-between" dir="rtl">
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-amber-400">الفراغ <span x-text="activeBlank ?? ''"></span></div>
                            <div class="text-sm font-bold text-white mt-0.5" x-text="submitted ? 'مراجعة الإجابة' : 'اختر الإجابة الصحيحة'"></div>
                        </div>
                        <button @click="sb1SheetOpen = false; activeBlank = null" class="text-slate-500 active:text-white p-1.5 -m-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-3 space-y-2" style="overscroll-behavior:contain">
                    {{-- Before submit: pick from 3 options --}}
                    <template x-if="!submitted && activeBlankSegment()">
                        <div class="space-y-2">
                            <template x-for="opt in activeBlankSegment().options" :key="opt">
                                <button @click="pickBlank(activeBlank, opt); sb1SheetOpen = false"
                                        class="w-full flex items-center justify-between gap-2 px-4 py-3.5 rounded-xl border text-base font-medium transition-all active:scale-[0.98]"
                                        :class="answers[activeBlank] === opt
                                            ? 'border-amber-500 bg-amber-500/15 text-white'
                                            : 'border-white/[0.08] bg-[#111216] text-slate-300 active:bg-white/5'">
                                    <span x-text="opt"></span>
                                    <template x-if="answers[activeBlank] === opt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400"><path d="M20 6 9 17l-5-5"/></svg>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </template>

                    {{-- After submit: review — picked vs correct + explanation --}}
                    <template x-if="submitted && activeBlankSegment()">
                        <div class="space-y-3">
                            <div class="flex items-center gap-2 flex-wrap" dir="ltr">
                                <span class="text-[11px] uppercase tracking-widest text-slate-500">اختيارك</span>
                                <span class="px-2 py-1 rounded-md text-sm font-bold"
                                      :class="answers[activeBlank] === correctAnswers[activeBlank]
                                          ? 'bg-green-500/15 text-green-300 border border-green-500/40'
                                          : 'bg-red-500/15 text-red-300 border border-red-500/40 line-through'"
                                      x-text="answers[activeBlank] ?? '—'"></span>
                                <template x-if="answers[activeBlank] !== correctAnswers[activeBlank]">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-600">→</span>
                                        <span class="px-2 py-1 rounded-md text-sm font-bold bg-green-500/15 text-green-300 border border-green-500/40" x-text="correctAnswers[activeBlank]"></span>
                                    </div>
                                </template>
                            </div>
                            <template x-if="activeBlankSegment().explanation">
                                <div class="text-sm text-slate-300 leading-relaxed bg-white/[0.02] border border-white/[0.06] rounded-xl p-3" dir="rtl" x-text="activeBlankSegment().explanation"></div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Footer: prev / next --}}
                <div class="shrink-0 border-t border-white/[0.06] p-3 flex items-center gap-2">
                    <button @click="sb1GotoBlank(-1)"
                            :disabled="!sb1CanGoPrev()"
                            class="flex-1 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium flex items-center justify-center gap-1 active:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        السابق
                    </button>
                    <button @click="sb1SheetOpen = false; activeBlank = null"
                            class="flex-1 py-2.5 rounded-xl bg-white/5 text-xs text-white font-medium" dir="rtl">إغلاق</button>
                    <button @click="sb1GotoBlank(1)"
                            :disabled="!sb1CanGoNext()"
                            class="flex-1 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium flex items-center justify-center gap-1 active:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed">
                        التالي
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>

            {{-- Mobile sticky bottom bar --}}
            <div class="fixed bottom-0 left-0 right-0 lg:hidden z-40 px-4 py-3 bg-[#08090C]/95 backdrop-blur border-t border-white/[0.08]">
                <template x-if="!submitted">
                    <button @click="submitAnswers()"
                            :disabled="Object.keys(answers).length < total"
                            class="btn-shine w-full py-3 rounded-xl bg-amber-600 active:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20">
                        <span x-text="'Abgabe (' + Object.keys(answers).length + '/' + total + ')'"></span>
                    </button>
                </template>
                <template x-if="submitted">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-bold flex-1 text-center" :class="score === total ? 'text-green-400' : score >= total/2 ? 'text-yellow-400' : 'text-red-400'"
                             dir="rtl" x-text="score + '/' + total + ' إجابة صحيحة'"></div>
                        <button @click="reset()" class="px-5 py-2.5 rounded-xl border border-white/10 text-sm text-slate-300 font-medium" dir="rtl">إعادة المحاولة</button>
                    </div>
                </template>
            </div>
        </div>

      </div>
      </template>
    </div>

    {{-- ── SPRACHBAUSTEINE 2 (word-pool cloze) ─────────────────────── --}}
    <div x-show="activePart === 'sprachbausteine2'" x-cloak>
      <template x-if="parts.sprachbausteine2">
      <div>

        {{-- Score bar (after submit) --}}
        <template x-if="submitted">
            <div class="mb-4 p-4 rounded-xl flex items-center justify-between gap-4"
                 :class="score === total ? 'bg-green-500/10 border border-green-500/20' : score >= total/2 ? 'bg-yellow-500/10 border border-yellow-500/20' : 'bg-red-500/10 border border-red-500/20'">
                <div dir="rtl">
                    <div class="font-bold text-white text-lg" x-text="score === total ? '🎉 ممتاز! كل الأجوبة صحيحة' : score + '/' + total + ' إجابة صحيحة'"></div>
                    <div class="text-sm text-slate-400 mt-0.5" x-text="score === total ? 'أحسنت!' : 'راجع الفراغات الخاطئة في النص.'"></div>
                </div>
                <button @click="reset()" class="shrink-0 px-4 py-2 rounded-xl border border-white/10 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-all" dir="rtl">إعادة المحاولة</button>
            </div>
        </template>

        {{-- DESKTOP: text on the left, word pool on the right --}}
        <div class="hidden lg:grid grid-cols-[1fr_400px] gap-6 items-start lg:h-[calc(100vh-10rem)]">

            {{-- LEFT: text article with inline blanks --}}
            <div class="h-full overflow-y-auto pr-2">
                <article class="rounded-2xl border border-white/[0.08] bg-[#111216]">
                    <div class="px-6 py-3 border-b border-white/[0.05] flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Sprachbausteine Teil 2</span>
                        <span class="text-xs text-slate-600" x-text="(parts.sprachbausteine2.blanks?.length ?? 0) + ' Lücken'"></span>
                    </div>
                    <template x-if="parts.sprachbausteine2.textTitle">
                        <h2 class="px-6 pt-5 pb-2 text-xl md:text-2xl font-black tracking-tight text-white" x-text="parts.sprachbausteine2.textTitle"></h2>
                    </template>
                    <div class="px-6 py-5 text-[15.5px] text-slate-200 leading-relaxed">
                        <template x-for="(para, pi) in sb2Paragraphs" :key="pi">
                            <p class="mb-3 last:mb-0">
                                <template x-for="(seg, si) in para" :key="si">
                                    <span>
                                        <template x-if="typeof seg === 'string'">
                                            <span class="whitespace-pre-line" x-text="seg"></span>
                                        </template>
                                        <template x-if="typeof seg === 'object' && seg !== null">
                                            <span class="inline-block align-baseline">
                                                <button @click.stop="sb2OpenBlank(seg.id)"
                                                        :disabled="submitted"
                                                        class="inline-flex items-baseline gap-1 px-3 py-0.5 mx-1 rounded text-[14px] font-bold transition-all align-baseline border-b-2 min-w-[6em] justify-center"
                                                        :class="submitted
                                                            ? (answers[seg.id] === correctAnswers[seg.id]
                                                                ? 'border-green-500 text-green-200 bg-green-500/[0.07]'
                                                                : 'border-red-500 text-red-300 bg-red-500/[0.07] line-through')
                                                            : activeBlank === seg.id
                                                                ? 'border-orange-400 text-white bg-orange-500/15 shadow-sm shadow-orange-500/30'
                                                                : selectedWord
                                                                    ? 'border-orange-500/40 bg-orange-500/[0.04] text-slate-300 hover:text-white hover:border-orange-400'
                                                                    : answers[seg.id] !== undefined
                                                                        ? 'border-amber-400 text-amber-100 bg-amber-500/[0.08]'
                                                                        : 'border-dashed border-slate-400/60 text-slate-500 hover:text-white hover:border-slate-300 hover:bg-white/[0.04]'">
                                                    <sup class="text-[9px] opacity-70 font-black" x-text="seg.id"></sup>
                                                    <span x-text="answers[seg.id] !== undefined ? getWordById(answers[seg.id]) : '   '"></span>
                                                </button>
                                                <template x-if="submitted && answers[seg.id] !== correctAnswers[seg.id]">
                                                    <span class="inline-flex items-baseline gap-1 px-2 py-0.5 mx-0.5 rounded border-b-2 border-green-500 text-green-200 bg-green-500/[0.07] text-[14px] font-bold align-baseline">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="self-center"><path d="M20 6 9 17l-5-5"/></svg>
                                                        <span x-text="getWordById(correctAnswers[seg.id])"></span>
                                                    </span>
                                                </template>
                                            </span>
                                        </template>
                                    </span>
                                </template>
                            </p>
                        </template>
                    </div>
                </article>
            </div>

            {{-- RIGHT: word pool --}}
            <div class="h-full flex flex-col gap-2">
                <div class="shrink-0 flex items-center justify-between px-1" dir="rtl">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500" x-text="submitted ? 'الشروحات' : 'القاموس — اختر كلمة'"></span>
                    <span class="text-[10px] text-slate-600" x-text="submitted ? (score + '/' + total) : (Object.keys(answers).length + '/' + total)"></span>
                </div>
                <div class="flex-1 overflow-y-auto pr-1">
                    {{-- Before submit: word pool grid --}}
                    <template x-if="!submitted">
                        <div class="grid grid-cols-2 gap-1.5">
                            <template x-for="w in (parts.sprachbausteine2.wordPool ?? [])" :key="w.id">
                                <button @click="sb2SelectWord(w.id)"
                                        class="flex items-center gap-2 px-2.5 py-2 rounded-md border text-[13px] font-medium transition-all"
                                        :class="selectedWord === w.id
                                            ? 'border-orange-500 bg-orange-500/15 text-white shadow-md shadow-orange-500/20'
                                            : isWordUsed(w.id)
                                                ? (activeBlank && answers[activeBlank] === w.id
                                                    ? 'border-amber-500 bg-amber-500/15 text-white'
                                                    : 'border-white/[0.04] bg-transparent text-slate-600 line-through')
                                                : activeBlank
                                                    ? 'border-amber-500/30 bg-amber-500/[0.04] text-slate-200 hover:bg-amber-500/15 hover:border-amber-400 hover:text-white'
                                                    : 'border-white/[0.08] bg-[#0B0C10] text-slate-300 hover:text-white hover:border-white/20'">
                                    <span class="shrink-0 text-[10px] font-black uppercase opacity-60" x-text="w.id"></span>
                                    <span class="text-left flex-1 truncate" x-text="w.word"></span>
                                    <template x-if="isWordUsed(w.id)">
                                        <span class="shrink-0 text-[10px] text-slate-500" x-text="'→' + getAssignedBlank(w.id)"></span>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </template>

                    {{-- After submit: per-blank explanation cards --}}
                    <template x-if="submitted">
                        <div class="space-y-2">
                            <template x-for="b in (parts.sprachbausteine2.blanks ?? [])" :key="b.id">
                                <div :id="'sb2-card-' + b.id" dir="ltr"
                                     class="rounded-md border bg-[#111216] overflow-hidden transition-all scroll-mt-2"
                                     :class="answers[b.id] === correctAnswers[b.id] ? 'border-green-500/40' : 'border-red-500/40'">
                                    <div class="flex items-center gap-2 px-2.5 py-2">
                                        <button @click="activeBlank = b.id"
                                                class="shrink-0 w-7 h-7 flex items-center justify-center rounded-md text-[12px] font-black"
                                                :class="answers[b.id] === correctAnswers[b.id] ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400'"
                                                x-text="b.id"></button>
                                        <template x-if="answers[b.id] !== correctAnswers[b.id]">
                                            <div class="flex items-center gap-1.5 flex-1 min-w-0 text-[12px]">
                                                <span class="px-1.5 py-0.5 rounded bg-red-500/15 text-red-300 font-bold uppercase truncate" x-text="getWordById(answers[b.id])"></span>
                                                <span class="text-slate-600 shrink-0">→</span>
                                                <span class="px-1.5 py-0.5 rounded bg-green-500/15 text-green-300 font-bold uppercase truncate" x-text="getWordById(correctAnswers[b.id])"></span>
                                            </div>
                                        </template>
                                        <template x-if="answers[b.id] === correctAnswers[b.id]">
                                            <div class="flex items-center gap-1.5 flex-1 min-w-0 text-[12px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-green-400 shrink-0"><path d="M20 6 9 17l-5-5"/></svg>
                                                <span class="px-1.5 py-0.5 rounded bg-green-500/15 text-green-300 font-bold uppercase truncate" x-text="getWordById(answers[b.id])"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <template x-if="b.explanation">
                                        <div class="px-3 pb-2.5 pt-1 text-[12px] text-slate-400 leading-relaxed border-t border-white/[0.04]" dir="rtl" x-text="b.explanation"></div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                <div class="shrink-0">
                    <template x-if="!submitted">
                        <button @click="submitAnswers()"
                                :disabled="Object.keys(answers).length < total"
                                class="btn-shine w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                            <span x-text="Object.keys(answers).length + '/' + total + ' — تصحيح الإجابات'"></span>
                        </button>
                    </template>
                    <template x-if="submitted">
                        <button @click="reset()" class="w-full py-3 rounded-xl border border-white/10 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-all" dir="rtl">إعادة المحاولة</button>
                    </template>
                </div>
            </div>
        </div>

        {{-- MOBILE: single article + bottom sheet --}}
        <div class="lg:hidden pb-24 pt-2">
            <article class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
                <div class="px-4 py-2.5 border-b border-white/[0.05] flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Sprachbausteine Teil 2</span>
                    <span class="text-xs text-slate-600" x-text="Object.keys(answers).length + '/' + total"></span>
                </div>
                <template x-if="parts.sprachbausteine2.textTitle">
                    <h2 class="px-4 pt-4 pb-1 text-lg font-black tracking-tight text-white" x-text="parts.sprachbausteine2.textTitle"></h2>
                </template>
                <div class="px-4 py-4 text-[15px] text-slate-200 leading-relaxed">
                    <template x-for="(para, pi) in sb2Paragraphs" :key="pi">
                        <p class="mb-3 last:mb-0">
                            <template x-for="(seg, si) in para" :key="si">
                                <span>
                                    <template x-if="typeof seg === 'string'">
                                        <span class="whitespace-pre-line" x-text="seg"></span>
                                    </template>
                                    <template x-if="typeof seg === 'object' && seg !== null">
                                        <span class="inline-block align-baseline">
                                            <button @click.stop="sb2OpenBlank(seg.id)"
                                                    class="inline-flex items-baseline gap-1 px-3 py-0.5 mx-1 rounded text-[14px] font-bold transition-all align-baseline active:scale-95 border-b-2 min-w-[6em] justify-center"
                                                    :class="submitted
                                                        ? (answers[seg.id] === correctAnswers[seg.id]
                                                            ? 'border-green-500 text-green-200 bg-green-500/[0.07]'
                                                            : 'border-red-500 text-red-300 bg-red-500/[0.07] line-through')
                                                        : activeBlank === seg.id
                                                            ? 'border-orange-400 text-white bg-orange-500/15'
                                                            : answers[seg.id] !== undefined
                                                                ? 'border-amber-400 text-amber-100 bg-amber-500/[0.08]'
                                                                : 'border-dashed border-slate-400/60 text-slate-500'">
                                                <sup class="text-[9px] opacity-70 font-black" x-text="seg.id"></sup>
                                                <span x-text="answers[seg.id] !== undefined ? getWordById(answers[seg.id]) : '   '"></span>
                                            </button>
                                            <template x-if="submitted && answers[seg.id] !== correctAnswers[seg.id]">
                                                <span class="inline-flex items-baseline gap-1 px-2 py-0.5 mx-0.5 rounded border-b-2 border-green-500 text-green-200 bg-green-500/[0.07] text-[14px] font-bold align-baseline">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="self-center"><path d="M20 6 9 17l-5-5"/></svg>
                                                    <span x-text="getWordById(correctAnswers[seg.id])"></span>
                                                </span>
                                            </template>
                                        </span>
                                    </template>
                                </span>
                            </template>
                        </p>
                    </template>
                </div>
            </article>

            {{-- MOBILE: bottom sheet — pick a word from the pool --}}
            <div x-show="sb2SheetOpen"
                 x-cloak
                 x-transition:enter="transition-opacity duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sb2SheetOpen = false; activeBlank = null"
                 class="fixed inset-0 z-[75] lg:hidden bg-black/70"></div>

            <div x-show="sb2SheetOpen"
                 x-cloak
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="fixed bottom-0 left-0 right-0 z-[60] lg:hidden bg-[#0B0C10] rounded-t-2xl border-t border-white/10 flex flex-col touch-pan-y"
                 style="max-height:80vh">

                <div class="shrink-0 bg-[#0B0C10] border-b border-white/[0.06] pt-3 px-4 pb-3">
                    <div class="w-10 h-1 rounded-full bg-white/20 mx-auto mb-3"></div>
                    <div class="flex items-center justify-between" dir="rtl">
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-amber-400">الفراغ <span x-text="activeBlank ?? ''"></span></div>
                            <div class="text-sm font-bold text-white mt-0.5" x-text="submitted ? 'مراجعة الإجابة' : 'اختر كلمة من القاموس'"></div>
                        </div>
                        <button @click="sb2SheetOpen = false; activeBlank = null" class="text-slate-500 active:text-white p-1.5 -m-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-3" style="overscroll-behavior:contain">
                    {{-- Before submit: word pool --}}
                    <template x-if="!submitted">
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="w in (parts.sprachbausteine2.wordPool ?? [])" :key="w.id">
                                <button @click="sb2Assign(activeBlank, w.id); sb2SheetOpen = false"
                                        class="flex items-center gap-2 px-3 py-3 rounded-xl border text-sm font-medium transition-all active:scale-[0.98]"
                                        :class="answers[activeBlank] === w.id
                                            ? 'border-amber-500 bg-amber-500/15 text-white'
                                            : isWordUsed(w.id)
                                                ? 'border-white/[0.04] bg-transparent text-slate-600 line-through'
                                                : 'border-white/[0.08] bg-[#111216] text-slate-300 active:bg-white/5'">
                                    <span class="shrink-0 text-[10px] font-black uppercase opacity-60" x-text="w.id"></span>
                                    <span class="text-left flex-1 truncate" x-text="w.word"></span>
                                </button>
                            </template>
                        </div>
                    </template>

                    {{-- After submit: explanation for the active blank --}}
                    <template x-if="submitted && activeBlank !== null">
                        <div class="space-y-3">
                            {{-- Picked vs correct --}}
                            <div class="flex items-center gap-2 flex-wrap" dir="ltr">
                                <span class="text-[11px] uppercase tracking-widest text-slate-500">اختيارك</span>
                                <span class="px-2 py-1 rounded-md text-sm font-bold uppercase"
                                      :class="answers[activeBlank] === correctAnswers[activeBlank]
                                          ? 'bg-green-500/15 text-green-300 border border-green-500/40'
                                          : 'bg-red-500/15 text-red-300 border border-red-500/40 line-through'"
                                      x-text="getWordById(answers[activeBlank])"></span>
                                <template x-if="answers[activeBlank] !== correctAnswers[activeBlank]">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-600">→</span>
                                        <span class="px-2 py-1 rounded-md text-sm font-bold uppercase bg-green-500/15 text-green-300 border border-green-500/40" x-text="getWordById(correctAnswers[activeBlank])"></span>
                                    </div>
                                </template>
                            </div>
                            {{-- Explanation --}}
                            <template x-if="activeBlankSegment() && activeBlankSegment().explanation">
                                <div class="text-sm text-slate-300 leading-relaxed bg-white/[0.02] border border-white/[0.06] rounded-xl p-3" dir="rtl" x-text="activeBlankSegment().explanation"></div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="shrink-0 border-t border-white/[0.06] p-3 flex items-center gap-2">
                    <button @click="sb2GotoBlank(-1)"
                            :disabled="!sb2CanGoPrev()"
                            class="flex-1 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium flex items-center justify-center gap-1 active:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        السابق
                    </button>
                    <button @click="sb2SheetOpen = false; activeBlank = null"
                            class="flex-1 py-2.5 rounded-xl bg-white/5 text-xs text-white font-medium" dir="rtl">إغلاق</button>
                    <button @click="sb2GotoBlank(1)"
                            :disabled="!sb2CanGoNext()"
                            class="flex-1 py-2.5 rounded-xl border border-white/10 text-xs text-slate-300 font-medium flex items-center justify-center gap-1 active:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed">
                        التالي
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>

            {{-- Mobile sticky bottom bar --}}
            <div class="fixed bottom-0 left-0 right-0 lg:hidden z-40 px-4 py-3 bg-[#08090C]/95 backdrop-blur border-t border-white/[0.08]">
                <template x-if="!submitted">
                    <button @click="submitAnswers()"
                            :disabled="Object.keys(answers).length < total"
                            class="btn-shine w-full py-3 rounded-xl bg-amber-600 active:bg-amber-500 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20">
                        <span x-text="'Abgabe (' + Object.keys(answers).length + '/' + total + ')'"></span>
                    </button>
                </template>
                <template x-if="submitted">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-bold flex-1 text-center" :class="score === total ? 'text-green-400' : score >= total/2 ? 'text-yellow-400' : 'text-red-400'"
                             dir="rtl" x-text="score + '/' + total + ' إجابة صحيحة'"></div>
                        <button @click="reset()" class="px-5 py-2.5 rounded-xl border border-white/10 text-sm text-slate-300 font-medium" dir="rtl">إعادة المحاولة</button>
                    </div>
                </template>
            </div>
        </div>

      </div>
      </template>
    </div>

    {{-- ── Other parts: coming soon ──────────────────────────────── --}}
    <div x-show="activePart !== 'teil1' && activePart !== 'teil2' && activePart !== 'teil3' && activePart !== 'sprachbausteine1' && activePart !== 'sprachbausteine2'" x-cloak>
        <div class="p-12 text-center text-slate-500 rounded-2xl border border-white/[0.08]">
            <div class="text-5xl mb-4">🚧</div>
            <div class="text-base font-medium text-slate-400" dir="rtl">هذا الجزء قيد التطوير</div>
            <div class="text-sm mt-1" dir="rtl">قريباً إن شاء الله</div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function lesenTopic(parts, initialPart, timerEnabled) {
    return {
        parts,
        activePart: (initialPart && parts[initialPart]) ? initialPart : (Object.keys(parts).find(k => parts[k]) ?? 'teil1'),
        // ── Countdown timer (auto-submit when ?timer=1 is in the URL) ─
        timerEnabled: !!timerEnabled,
        secondsLeft: 0,
        _timerInterval: null,
        _durationsByPart: {
            teil1:            25 * 60,
            teil2:            30 * 60,
            teil3:            30 * 60,
            sprachbausteine1: 15 * 60,
            sprachbausteine2: 15 * 60,
        },
        answers: {},
        submitted: false,
        score: 0,
        total: 0,
        correctAnswers: {},
        selectedText: null,
        selectedHeadline: null,
        dragging: null,
        mobileView: 'texts',
        sheetOpen: false,
        sheetTargetText: null,
        // Teil 2 mobile state
        paragraphs: [],
        highlightedPara: 0,
        qSheetOpen: false,
        _paraScrollHandler: null,
        // Teil 3 state
        selectedSituation: null,
        selectedAd: null,
        t3SheetOpen: false,
        t3SheetSituation: null,
        t3ExpandedAd: null,
        // Floating control
        partMenuOpen: false,
        _partNames: { teil1: 'Teil 1', teil2: 'Teil 2', teil3: 'Teil 3', sprachbausteine1: 'Sprachbausteine 1', sprachbausteine2: 'Sprachbausteine 2' },
        partLabel() { return this._partNames[this.activePart] ?? this.activePart; },
        // Sprachbausteine 1 state
        activeBlank: null,
        sb1Paragraphs: [],
        sb1SheetOpen: false,
        // Sprachbausteine 2 state
        selectedWord: null,
        sb2Paragraphs: [],
        sb2SheetOpen: false,
        activeBlankSegment() {
            // Look up across whichever Sprachbausteine teil is active
            const part = this.activePart === 'sprachbausteine2' ? 'sprachbausteine2' : 'sprachbausteine1';
            const segs = this.parts[part]?.segments ?? [];
            return segs.find(s => typeof s === 'object' && s !== null && s.id === this.activeBlank) ?? null;
        },

        init() { this.setupPart(); },

        setupPart() {
            this._stopTimer();
            this.answers          = {};
            this.submitted        = false;
            this.score            = 0;
            this.selectedText     = null;
            this.selectedHeadline = null;
            this.dragging         = null;
            this.sheetOpen        = false;
            this.sheetTargetText  = null;
            this.mobileView       = 'texts';
            this.paragraphs       = [];
            this.highlightedPara  = 0;
            this.qSheetOpen       = false;
            this.selectedSituation = null;
            this.selectedAd       = null;
            this.t3SheetOpen      = false;
            this.t3SheetSituation = null;
            this.t3ExpandedAd     = null;
            this.partMenuOpen     = false;
            this.activeBlank      = null;
            this.sb1Paragraphs    = [];
            this.sb1SheetOpen     = false;
            this.selectedWord     = null;
            this.sb2Paragraphs    = [];
            this.sb2SheetOpen     = false;
            if (this.activePart === 'teil1' && this.parts.teil1) {
                this.correctAnswers = this.parts.teil1.correctAnswers ?? {};
                this.total          = this.parts.teil1.texts?.length ?? 0;
            } else if (this.activePart === 'teil2' && this.parts.teil2) {
                const qs = this.parts.teil2.questions ?? [];
                this.correctAnswers = qs.reduce((acc, q, i) => { acc[i] = q.correct; return acc; }, {});
                this.total          = qs.length;
                this.mobileView     = 'text';
                this.paragraphs     = (this.parts.teil2.textContent || '')
                    .split(/(?:\r?\n){2,}/)
                    .map(p => p.trim())
                    .filter(p => p.length);
                this.$nextTick(() => this.bindParaScrollSpy());
            } else if (this.activePart === 'teil3' && this.parts.teil3) {
                this.correctAnswers = this.parts.teil3.correctAnswers ?? {};
                this.total          = this.parts.teil3.situations?.length ?? 0;
            } else if (this.activePart === 'sprachbausteine1' && this.parts.sprachbausteine1) {
                const segs = this.parts.sprachbausteine1.segments ?? [];
                const blanks = segs.filter(s => typeof s === 'object' && s !== null);

                // Group segments into paragraphs by splitting strings on blank-line breaks (\n\n+).
                // Single \n inside a paragraph stays as a soft line break.
                const paras = [[]];
                segs.forEach(seg => {
                    if (typeof seg === 'string') {
                        const parts = seg.split(/\r?\n\r?\n+/);
                        parts.forEach((p, i) => {
                            if (i > 0) paras.push([]);
                            if (p.length) paras[paras.length - 1].push(p);
                        });
                    } else if (seg !== null) {
                        paras[paras.length - 1].push(seg);
                    }
                });
                this.sb1Paragraphs = paras.filter(p => p.length);
                this.parts.sprachbausteine1.blanks = blanks;
                this.correctAnswers = blanks.reduce((acc, b) => { acc[b.id] = b.correct; return acc; }, {});
                this.total          = blanks.length;
            } else if (this.activePart === 'sprachbausteine2' && this.parts.sprachbausteine2) {
                const segs = this.parts.sprachbausteine2.segments ?? [];
                const blanks = segs.filter(s => typeof s === 'object' && s !== null);

                const paras2 = [[]];
                segs.forEach(seg => {
                    if (typeof seg === 'string') {
                        const parts = seg.split(/\r?\n\r?\n+/);
                        parts.forEach((p, i) => {
                            if (i > 0) paras2.push([]);
                            if (p.length) paras2[paras2.length - 1].push(p);
                        });
                    } else if (seg !== null) {
                        paras2[paras2.length - 1].push(seg);
                    }
                });
                this.sb2Paragraphs = paras2.filter(p => p.length);
                this.parts.sprachbausteine2.blanks = blanks;
                this.correctAnswers = blanks.reduce((acc, b) => { acc[b.id] = b.correct; return acc; }, {});
                this.total          = blanks.length;
            } else {
                this.correctAnswers = {};
                this.total          = 0;
            }
            // Kick off the countdown if the user opted into the timer for this part
            const dur = this._durationsByPart[this.activePart];
            if (this.timerEnabled && dur && this.total > 0) {
                this.secondsLeft = dur;
                this._startTimer();
            } else {
                this.secondsLeft = 0;
            }
        },

        // ── Teil 2: pick option (optIdx is 0/1/2) ───────────────────
        selectOption(qIdx, optIdx) {
            if (this.submitted) return;
            this.answers[qIdx] = optIdx;
            this.answers = { ...this.answers };
        },

        // ── Teil 3: ad ↔ situation matching ─────────────────────────
        toggleAdAssignment(situationId, adId) {
            if (this.submitted) return;
            if (this.answers[situationId] === adId) {
                delete this.answers[situationId];
            } else {
                this.answers[situationId] = adId;
            }
            this.answers = { ...this.answers };
        },

        selectSituation(situationId) {
            if (this.submitted) return;
            // If an ad is already armed, assign it on click
            if (this.selectedAd) {
                this.answers[situationId] = this.selectedAd;
                this.answers = { ...this.answers };
                this.selectedAd = null;
                return;
            }
            this.selectedSituation = this.selectedSituation === situationId ? null : situationId;
        },

        selectAd(adId) {
            if (this.submitted) return;
            if (this.selectedSituation !== null) {
                this.answers[this.selectedSituation] = adId;
                this.answers = { ...this.answers };
                this.selectedSituation = null;
                return;
            }
            this.selectedAd = this.selectedAd === adId ? null : adId;
        },

        dragAd(event, adId) {
            this.dragging = adId;
            event.dataTransfer.setData('adId', adId);
            event.dataTransfer.effectAllowed = 'move';
        },

        dropOnSituation(event, situationId) {
            const adId = event.dataTransfer.getData('adId') || this.dragging;
            if (!adId || this.submitted) return;
            this.answers[situationId] = adId;
            this.answers = { ...this.answers };
            this.dragging = null;
            this.selectedSituation = null;
            this.selectedAd = null;
        },

        getAdTitle(adId) {
            const ad = this.parts.teil3?.ads?.find(a => a.id === adId);
            if (!ad) return adId ?? '—';
            return ad.title || (adId === 'x' ? 'Keine passende Anzeige' : adId);
        },

        getSituationText(sitId) {
            const s = this.parts.teil3?.situations?.find(s => String(s.id) === String(sitId));
            return s?.text ?? '';
        },

        isAdPicked(adId) {
            return Object.values(this.answers).includes(adId);
        },

        adAssignedSituations(adId) {
            return Object.keys(this.answers).filter(sid => this.answers[sid] === adId);
        },

        scrollToAd(adId) {
            const el = document.getElementById('mob-ad-' + adId);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },

        scrollToSituation(sitId) {
            const el = document.getElementById('mob-sit-' + sitId);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },

        // ── Sprachbausteine 1: cloze blank picker ──────────────────
        openBlank(id) {
            this.activeBlank = id;
            // Mobile: open the bottom sheet. Desktop: scroll the right-panel card into view.
            if (window.innerWidth < 1024) {
                this.sb1SheetOpen = true;
            } else {
                this.$nextTick(() => {
                    const el = document.getElementById('sb1-card-' + id);
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            }
        },

        pickBlank(id, value) {
            if (this.submitted) return;
            this.answers[id] = value;
            this.answers     = { ...this.answers };
            // Keep activeBlank set so the visual highlight persists; only close mobile sheet on explicit dismiss
        },

        sb1GotoBlank(delta) {
            const blanks = this.parts.sprachbausteine1?.blanks ?? [];
            const idx = blanks.findIndex(b => b.id === this.activeBlank);
            if (idx < 0) return;
            const next = blanks[idx + delta];
            if (!next) return;
            this.activeBlank = next.id;
        },

        sb1CanGoPrev() {
            const blanks = this.parts.sprachbausteine1?.blanks ?? [];
            return blanks.findIndex(b => b.id === this.activeBlank) > 0;
        },

        sb1CanGoNext() {
            const blanks = this.parts.sprachbausteine1?.blanks ?? [];
            const idx = blanks.findIndex(b => b.id === this.activeBlank);
            return idx >= 0 && idx < blanks.length - 1;
        },

        // ── Sprachbausteine 2: word-pool cloze ─────────────────────
        sb2OpenBlank(id) {
            this.activeBlank = id;
            if (window.innerWidth < 1024) this.sb2SheetOpen = true;
            if (this.submitted) return; // post-submit: just open for review
            // If a word is already selected from the pool, assigning happens immediately
            if (this.selectedWord) {
                this.sb2Assign(id, this.selectedWord);
                this.selectedWord = null;
            }
        },

        sb2SelectWord(wordId) {
            if (this.submitted) return;
            // If a blank is active, assign now
            if (this.activeBlank !== null) {
                this.sb2Assign(this.activeBlank, wordId);
                return;
            }
            // If this word is already used, clicking unassigns it
            if (this.isWordUsed(wordId)) {
                const k = this.getAssignedBlank(wordId);
                if (k !== null) {
                    delete this.answers[k];
                    this.answers = { ...this.answers };
                }
                this.selectedWord = null;
                return;
            }
            // Otherwise toggle this word as the armed selection
            this.selectedWord = this.selectedWord === wordId ? null : wordId;
        },

        sb2Assign(blankId, wordId) {
            if (this.submitted || blankId === null) return;
            // Free this word from any other blank
            Object.keys(this.answers).forEach(k => {
                if (this.answers[k] === wordId && Number(k) !== Number(blankId)) {
                    delete this.answers[k];
                }
            });
            this.answers[blankId] = wordId;
            this.answers = { ...this.answers };
            this.activeBlank = null;
            this.selectedWord = null;
        },

        isWordUsed(wordId) {
            return Object.values(this.answers).includes(wordId);
        },

        getAssignedBlank(wordId) {
            const k = Object.keys(this.answers).find(k => this.answers[k] === wordId);
            return k ?? null;
        },

        getWordById(wordId) {
            const w = this.parts.sprachbausteine2?.wordPool?.find(w => w.id === wordId);
            return w?.word ?? wordId ?? '';
        },

        sb2GotoBlank(delta) {
            const blanks = this.parts.sprachbausteine2?.blanks ?? [];
            const idx = blanks.findIndex(b => b.id === this.activeBlank);
            if (idx < 0) return;
            const next = blanks[idx + delta];
            if (!next) return;
            this.activeBlank = next.id;
        },

        sb2CanGoPrev() {
            const blanks = this.parts.sprachbausteine2?.blanks ?? [];
            return blanks.findIndex(b => b.id === this.activeBlank) > 0;
        },

        sb2CanGoNext() {
            const blanks = this.parts.sprachbausteine2?.blanks ?? [];
            const idx = blanks.findIndex(b => b.id === this.activeBlank);
            return idx >= 0 && idx < blanks.length - 1;
        },

        // ── Timer ──────────────────────────────────────────────────
        _startTimer() {
            this._stopTimer();
            this._timerInterval = setInterval(() => {
                if (this.submitted) { this._stopTimer(); return; }
                this.secondsLeft = Math.max(0, this.secondsLeft - 1);
                if (this.secondsLeft === 0) {
                    this._stopTimer();
                    if (!this.submitted && typeof this.submitAnswers === 'function') {
                        this.submitAnswers();
                    }
                }
            }, 1000);
        },

        _stopTimer() {
            if (this._timerInterval) {
                clearInterval(this._timerInterval);
                this._timerInterval = null;
            }
        },

        formatTime(seconds) {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return m + ':' + (s < 10 ? '0' + s : s);
        },

        // ── Teil 3 mobile: ads sheet (per-situation) ────────────────
        openAdsSheetFor(situationId) {
            if (this.submitted) return;
            this.t3SheetSituation = situationId;
            this.t3ExpandedAd     = null;
            this.t3SheetOpen      = true;
            this._lockBodyScroll(true);
        },

        pickAdForSheetSituation(adId) {
            if (this.t3SheetSituation === null) return;
            this.answers[this.t3SheetSituation] = adId;
            this.answers     = { ...this.answers };
            this.t3SheetOpen  = false;
            this.t3ExpandedAd = null;
            this._lockBodyScroll(false);
        },

        _lockBodyScroll(locked) {
            try {
                document.body.style.overflow = locked ? 'hidden' : '';
                document.body.style.touchAction = locked ? 'none' : '';
            } catch (e) {}
        },

        toggleAdExpand(adId) {
            this.t3ExpandedAd = this.t3ExpandedAd === adId ? null : adId;
        },

        sheetGotoSituation(delta) {
            const sits = this.parts.teil3?.situations ?? [];
            const idx  = sits.findIndex(s => s.id === this.t3SheetSituation);
            if (idx < 0) return;
            const next = sits[idx + delta];
            if (!next) return;
            this.t3SheetSituation = next.id;
            this.t3ExpandedAd     = null;
            this.scrollToSituation(next.id);
        },

        sheetCanGoPrev() {
            const sits = this.parts.teil3?.situations ?? [];
            return sits.findIndex(s => s.id === this.t3SheetSituation) > 0;
        },

        sheetCanGoNext() {
            const sits = this.parts.teil3?.situations ?? [];
            const idx  = sits.findIndex(s => s.id === this.t3SheetSituation);
            return idx >= 0 && idx < sits.length - 1;
        },

        // ── Teil 2 mobile: paragraph + question scrolling ───────────
        scrollToPara(idx) {
            const el = document.getElementById('mob-para-' + idx);
            if (!el) return;
            this.highlightedPara = idx;
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },

        scrollToQuestion(qIdx) {
            const el = document.getElementById('mob-q-' + qIdx);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },

        bindParaScrollSpy() {
            // Drop the previous handler if any
            if (this._paraScrollHandler) {
                window.removeEventListener('scroll', this._paraScrollHandler);
                this._paraScrollHandler = null;
            }
            if (!this.paragraphs.length) return;

            const ids = ['mob-para-0', ...this.paragraphs.map((_, i) => 'mob-para-' + (i + 1))];

            this._paraScrollHandler = () => {
                // Probe the visual centre of the viewport so the highlighted
                // chip matches the paragraph the reader is actually looking at.
                const probe = window.innerHeight * 0.45;
                let active = this.highlightedPara;
                for (let i = ids.length - 1; i >= 0; i--) {
                    const el = document.getElementById(ids[i]);
                    if (!el) continue;
                    if (el.getBoundingClientRect().top - probe <= 0) {
                        active = i; // 0 = title, 1..N = paragraphs
                        break;
                    }
                }
                if (active !== this.highlightedPara) this.highlightedPara = active;
            };
            window.addEventListener('scroll', this._paraScrollHandler, { passive: true });
            this._paraScrollHandler();
        },

        reset() { this.setupPart(); },

        // ── Click interactions ──────────────────────────────────────
        selectText(id) {
            if (this.submitted) return;
            // If a headline is already waiting, assign it immediately
            if (this.selectedHeadline) {
                const prev = this.getAssignedText(this.selectedHeadline);
                if (prev) delete this.answers[prev];
                this.answers[id] = this.selectedHeadline;
                this.answers = { ...this.answers };
                this.selectedHeadline = null;
                return;
            }
            this.selectedText = this.selectedText === id ? null : id;
        },

        assignFromPanel(headlineId) {
            if (this.submitted) return;
            // A text is waiting → assign
            if (this.selectedText) {
                const prev = this.getAssignedText(headlineId);
                if (prev) delete this.answers[prev];
                this.answers[this.selectedText] = headlineId;
                this.answers = { ...this.answers };
                this.selectedText = null;
                return;
            }
            // Headline already used → clicking it unassigns it
            if (this.isHeadlineUsed(headlineId)) {
                const prev = this.getAssignedText(headlineId);
                if (prev) { delete this.answers[prev]; this.answers = { ...this.answers }; }
                this.selectedHeadline = null;
                return;
            }
            // No text selected → select this headline
            this.selectedHeadline = this.selectedHeadline === headlineId ? null : headlineId;
        },

        unassign(textId) {
            delete this.answers[textId];
            this.answers = { ...this.answers };
        },

        // ── Mobile navigation ────────────────────────────────────────
        navigateTo(textId) {
            const el = document.getElementById('mob-text-' + textId);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        // ── Mobile sheet ─────────────────────────────────────────────
        openSheet(textId) {
            this.sheetTargetText = textId;
            this.sheetOpen       = true;
        },

        pickHeadlineFromSheet(headlineId) {
            if (!this.sheetTargetText) return;
            const prev = this.getAssignedText(headlineId);
            if (prev && prev !== this.sheetTargetText) delete this.answers[prev];
            this.answers[this.sheetTargetText] = headlineId;
            this.answers     = { ...this.answers };
            this.sheetOpen   = false;
            this.sheetTargetText = null;
        },

        selectHeadlineAndSwitch(headlineId) {
            if (this.isHeadlineUsed(headlineId)) return;
            this.selectedHeadline = this.selectedHeadline === headlineId ? null : headlineId;
            if (this.selectedHeadline) this.mobileView = 'texts';
        },

        // ── Drag interactions ────────────────────────────────────────
        dragHeadline(event, headlineId) {
            this.dragging = headlineId;
            event.dataTransfer.setData('headlineId', headlineId);
            event.dataTransfer.effectAllowed = 'move';
        },

        dropOnText(event, textId) {
            const headlineId = event.dataTransfer.getData('headlineId') || this.dragging;
            if (!headlineId || this.submitted) return;
            const prev = this.getAssignedText(headlineId);
            if (prev) delete this.answers[prev];
            this.answers[textId]  = headlineId;
            this.answers          = { ...this.answers };
            this.dragging         = null;
            this.selectedText     = null;
            this.selectedHeadline = null;
        },

        dropOnPanel(event) {
            // Drop on panel = unassign from any text
            const headlineId = event.dataTransfer.getData('headlineId') || this.dragging;
            if (!headlineId) return;
            const prev = this.getAssignedText(headlineId);
            if (prev) { delete this.answers[prev]; this.answers = { ...this.answers }; }
            this.dragging = null;
        },

        // ── Helpers ──────────────────────────────────────────────────
        isHeadlineUsed(id) {
            return Object.values(this.answers).includes(id);
        },

        getAssignedText(headlineId) {
            return Object.keys(this.answers).find(k => this.answers[k] === headlineId) ?? null;
        },

        getHeadlineText(id) {
            return this.parts.teil1?.headlines?.find(h => h.id === id)?.text ?? id;
        },

        headlineClass(id) {
            if (this.submitted) {
                return this.isHeadlineUsed(id)
                    ? 'border-white/[0.03] bg-white/[0.02] cursor-default opacity-50'
                    : 'border-white/[0.05] bg-transparent cursor-default';
            }
            if (this.selectedHeadline === id)
                return 'border-orange-500 bg-orange-500/10 cursor-pointer shadow-lg shadow-orange-500/10';
            if (this.isHeadlineUsed(id))
                return 'border-white/[0.05] bg-transparent opacity-50 cursor-pointer';
            if (this.selectedText)
                return 'border-amber-500/30 bg-amber-500/5 hover:bg-amber-500/10 hover:border-amber-400 cursor-pointer';
            return 'border-white/[0.08] bg-[#111216] hover:border-white/20 hover:bg-white/[0.04] cursor-grab';
        },

        submitAnswers() {
            this.submitted = true;
            this.selectedText = null;
            this.score = Object.entries(this.correctAnswers)
                .filter(([k, v]) => this.answers[k] === v).length;
        },
    };
}
</script>
@endpush
