@extends('layouts.app')
@section('title', $topic->title . ' | Schreiben | ' . config('app.name'))

@push('head')
<script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/marked.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js" defer></script>
<style>
.prose-feedback { color: #e2e8f0; font-size: 14.5px; line-height: 1.7; }
.prose-feedback h1 { color: #fff; font-size: 1.35rem; font-weight: 800; margin: 1.25rem 0 0.75rem; }
.prose-feedback h2 { color: #fff; font-size: 1.1rem; font-weight: 800; margin: 1.5rem 0 0.6rem; padding-top: 0.75rem; border-top: 1px solid rgba(255,255,255,0.06); }
.prose-feedback h2:first-child { border-top: 0; padding-top: 0; margin-top: 0; }
.prose-feedback h3 { color: #a7f3d0; font-size: 0.95rem; font-weight: 700; margin: 1rem 0 0.4rem; }
.prose-feedback h4 { color: #fdba74; font-size: 0.9rem; font-weight: 700; margin: 0.75rem 0 0.3rem; }
.prose-feedback p { margin: 0.5rem 0; }
.prose-feedback ul, .prose-feedback ol { margin: 0.5rem 0 0.75rem; padding-inline-start: 1.4rem; }
.prose-feedback ul { list-style: disc; }
.prose-feedback ol { list-style: decimal; }
.prose-feedback li { margin: 0.2rem 0; }
.prose-feedback li > p { margin: 0.2rem 0; }
.prose-feedback strong { color: #fff; font-weight: 700; }
.prose-feedback em { color: #fde68a; font-style: italic; }
.prose-feedback code { background: rgba(255,255,255,0.06); padding: 0.1rem 0.35rem; border-radius: 0.25rem; font-size: 0.85em; color: #fbbf24; }
.prose-feedback hr { border: 0; border-top: 1px solid rgba(255,255,255,0.08); margin: 1.25rem 0; }
.prose-feedback table { width: 100%; border-collapse: collapse; margin: 0.75rem 0 1rem; font-size: 0.9rem; display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.prose-feedback table tbody, .prose-feedback table thead { display: table; width: 100%; min-width: 480px; }
.prose-feedback th, .prose-feedback td { border: 1px solid rgba(255,255,255,0.08); padding: 0.5rem 0.75rem; text-align: start; vertical-align: top; word-break: break-word; }
.prose-feedback th { background: rgba(255,255,255,0.04); color: #fff; font-weight: 700; }
.prose-feedback td { color: #cbd5e1; }
@media (max-width: 640px) {
  .prose-feedback { font-size: 13.5px; }
  .prose-feedback h2 { font-size: 1rem; }
  .prose-feedback h3 { font-size: 0.9rem; }
  .prose-feedback table { font-size: 0.82rem; }
}
.prose-feedback blockquote { border-inline-start: 3px solid rgba(16,185,129,0.5); background: rgba(16,185,129,0.05); padding: 0.5rem 0.9rem; margin: 0.75rem 0; border-radius: 0.4rem; color: #d1fae5; }
/* dir="auto" is added per block by the renderer; this just aligns RTL text properly */
.prose-feedback :is(p, li, td, blockquote, h1, h2, h3, h4)[dir="auto"] { unicode-bidi: plaintext; }
.prose-feedback :is(p, li, h3, h4):dir(rtl) { text-align: right; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 pt-28 md:pt-32 pb-8"
     x-data="schreibenTopic({{ json_encode([
         'id'       => $topic->id,
         'slug'     => $topic->slug,
         'level'    => $topic->level,
         'minutes'  => $topic->minutes,
         'type'     => $topic->type,
         'points'   => is_array($topic->points) ? array_values($topic->points) : [],
     ]) }}, {{ ($timerEnabled ?? false) ? 'true' : 'false' }})"
     x-init="hydrate()"
>
    {{-- Floating focus-mode controls --}}
    <div class="fixed top-[72px] md:top-[88px] right-3 md:right-4 z-30 flex items-center gap-1.5" dir="ltr">
        <a href="{{ route('schreiben.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-full bg-[#111216]/85 backdrop-blur border border-white/10 text-slate-400 hover:text-white hover:bg-[#111216] active:scale-95 transition-all shadow-lg shadow-black/30"
           title="رجوع للمواضيع">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>

        {{-- Countdown pill --}}
        <template x-if="timerEnabled && secondsLeft > 0 && !submitted">
            <div class="flex items-center gap-1.5 h-9 px-3 rounded-full bg-[#111216]/85 backdrop-blur border font-mono font-bold tabular-nums text-sm shadow-lg shadow-black/30 transition-all"
                 :class="secondsLeft < 60 ? 'border-red-500/60 text-red-300 animate-pulse' : secondsLeft < 300 ? 'border-amber-500/50 text-amber-200' : 'border-white/10 text-slate-200'"
                 :title="formatTime(secondsLeft) + ' متبقية'">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="opacity-80"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2"/><path d="M5 3 2 6"/><path d="m22 6-3-3"/></svg>
                <span x-text="formatTime(secondsLeft)"></span>
            </div>
        </template>

        {{-- Card pill: type + level (type hidden on phones to save space) --}}
        <div class="inline-flex items-center gap-2 h-9 px-3 rounded-full bg-[#111216]/85 backdrop-blur border border-white/10 text-xs font-bold text-white shadow-lg shadow-black/30">
            <span class="hidden sm:inline">{{ $topic->type ?? 'Schreiben' }}</span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-black {{ $topic->level === 'B2' ? 'bg-orange-500/25 text-orange-200' : 'bg-emerald-500/25 text-emerald-200' }}">{{ $topic->level }}</span>
        </div>
    </div>

    {{-- Score / submit toast --}}
    <template x-if="submitted">
        <div class="mb-4 p-4 rounded-xl flex items-center justify-between gap-4 bg-emerald-500/10 border border-emerald-500/20">
            <div dir="rtl">
                <div class="font-bold text-white text-lg">تم تسجيل الإجابة</div>
                <div class="text-sm text-slate-400 mt-0.5" x-text="wordCount + ' كلمة · ' + formatTime(elapsed) + ' مدة الكتابة'"></div>
            </div>
            <div class="flex items-center gap-2">
                <button @click="reset()" class="px-4 py-2 rounded-xl border border-white/10 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-all" dir="rtl">إعادة المحاولة</button>
                <a href="{{ route('schreiben.pdf', $topic->slug) }}" class="px-4 py-2 rounded-xl border border-white/10 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-all">PDF</a>
            </div>
        </div>
    </template>

    {{-- AI feedback error --}}
    <template x-if="aiError">
        <div class="mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-200 text-sm" dir="rtl">
            <span x-text="aiError"></span>
        </div>
    </template>

    {{-- Beispiel generation modal --}}
    <template x-if="showGenModal">
        <div class="fixed inset-0 z-[90] flex items-end md:items-center justify-center p-0 md:p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             @keydown.escape.window="closeGenModal()">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="closeGenModal()"></div>

            <div class="relative z-10 w-full md:max-w-2xl max-h-[92vh] flex flex-col bg-[#0d0e12] border border-white/[0.08] rounded-t-3xl md:rounded-2xl shadow-2xl overflow-hidden"
                 x-transition:enter="transition ease-out duration-300 delay-75"
                 x-transition:enter-start="opacity-0 translate-y-6 md:translate-y-0 md:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 md:scale-100">

                {{-- Header --}}
                <div class="shrink-0 px-5 py-4 border-b border-white/[0.05] flex items-center justify-between gap-3 bg-gradient-to-br from-orange-500/[0.08] to-emerald-500/[0.04]">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 shrink-0 rounded-lg bg-orange-500/20 border border-orange-400/30 flex items-center justify-center text-orange-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="M12 18v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="m16.24 16.24 2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="m16.24 7.76 2.83-2.83"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-white font-bold text-sm md:text-base truncate" x-text="genStep === 'result' ? 'Generiertes Beispiel' : 'Beispiel generieren'"></div>
                            <div class="text-[11px] text-slate-500" dir="rtl" x-text="genStep === 'result' ? 'نموذج جاهز للدراسة' : 'اختار النقط وعطي أفكارك'"></div>
                        </div>
                    </div>
                    <button @click="closeGenModal()" class="w-8 h-8 shrink-0 flex items-center justify-center rounded-full text-slate-400 hover:text-white hover:bg-white/5 transition-all" title="إغلاق">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                {{-- Body (scrollable) --}}
                <div class="grow overflow-y-auto">
                    <template x-if="genError">
                        <div class="m-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-200 text-sm" dir="rtl" x-text="genError"></div>
                    </template>

                    {{-- FORM --}}
                    <template x-if="genStep === 'form'">
                        <div class="p-5 space-y-5">
                            {{-- Email-Typ --}}
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-1.5">E-Mail-Typ</label>
                                <select x-model="genEmailType"
                                        class="w-full bg-[#111216] border border-white/[0.08] rounded-xl px-3 h-10 text-sm text-white focus:outline-none focus:border-orange-500/40 focus:ring-1 focus:ring-orange-500/20">
                                    <option value="Beschwerde">Beschwerde</option>
                                    <option value="Bitte um Informationen">Bitte um Informationen</option>
                                    <option value="Anfrage">Anfrage</option>
                                    <option value="Persönlicher Brief">Persönlicher Brief</option>
                                    <option value="Mitteilung">Mitteilung</option>
                                </select>
                            </div>

                            {{-- Leitpunkte selection --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Welche Leitpunkte? · شنو بغيتي تعالج</label>
                                    <span class="text-[11px] font-bold"
                                          :class="genSelectedCount > 3 ? 'text-red-300' : genSelectedCount >= 2 ? 'text-emerald-300' : 'text-slate-500'"
                                          x-text="genSelectedCount + ' / 3'"></span>
                                </div>
                                <div class="space-y-1.5">
                                    <template x-for="(lp, i) in parts.points" :key="i">
                                        <label class="flex items-start gap-2.5 p-3 rounded-xl border border-white/[0.06] bg-[#111216] hover:border-emerald-500/30 cursor-pointer transition-all"
                                               :class="genSelections['LP' + (i+1)] ? 'border-emerald-500/40 bg-emerald-500/[0.04]' : ''">
                                            <input type="checkbox"
                                                   :checked="!!genSelections['LP' + (i+1)]"
                                                   @change="toggleGenSel('LP' + (i+1), 'LP' + (i+1) + ': ' + lp)"
                                                   :disabled="!genSelections['LP' + (i+1)] && genSelectedCount >= 3"
                                                   class="mt-0.5 w-4 h-4 accent-emerald-500 disabled:opacity-40">
                                            <span class="text-sm text-slate-200 leading-snug"><span class="font-bold text-emerald-300" x-text="'LP' + (i+1) + '.'"></span> <span x-text="lp"></span></span>
                                        </label>
                                    </template>
                                    <label class="flex items-start gap-2.5 p-3 rounded-xl border border-white/[0.06] bg-[#111216] hover:border-orange-500/30 cursor-pointer transition-all"
                                           :class="genSelections['OWN'] ? 'border-orange-500/40 bg-orange-500/[0.04]' : ''">
                                        <input type="checkbox"
                                               :checked="!!genSelections['OWN']"
                                               @change="toggleGenSel('OWN', 'Eigener relevanter Aspekt')"
                                               :disabled="!genSelections['OWN'] && genSelectedCount >= 3"
                                               class="mt-0.5 w-4 h-4 accent-orange-500 disabled:opacity-40">
                                        <span class="text-sm text-slate-200 leading-snug"><span class="font-bold text-orange-300">+</span> Eigener relevanter Aspekt <span class="text-slate-500 text-xs">· شي حاجة من راسك مرتبطة بالسياق</span></span>
                                    </label>
                                </div>
                            </div>

                            {{-- Per-selection ideas --}}
                            <template x-if="genSelectedCount > 0">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-1.5">Deine Ideen · أفكارك (بالدارجة)</label>
                                    <p class="text-[11px] text-slate-500 mb-2" dir="rtl">2-3 سيناريوهات واقعية لكل نقطة. خليه فارغ → النموذج كيختار سيناريو واقعي بوحدو.</p>
                                    <div class="space-y-2.5">
                                        <template x-for="key in genSelectedKeysOrdered" :key="key">
                                            <div>
                                                <div class="text-xs font-bold mb-1 leading-snug"
                                                     :class="key === 'OWN' ? 'text-orange-300' : 'text-emerald-300'"
                                                     x-text="genSelections[key]"></div>
                                                <textarea x-model="genIdeas[key]"
                                                          rows="2"
                                                          class="w-full bg-[#111216] border border-white/[0.06] rounded-lg px-3 py-2 text-sm text-slate-100 focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 placeholder:text-slate-700 resize-y"
                                                          placeholder="مثلاً: خلصت 9,99€ فالشهر، الإعلانات كتقطع الفيديو، ماكاينش ترجمة ألمانية…" dir="rtl"></textarea>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- LOADING --}}
                    <template x-if="genStep === 'loading'">
                        <div class="p-12 flex flex-col items-center justify-center text-center">
                            <svg class="animate-spin h-10 w-10 text-orange-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                            <div class="text-white font-bold mb-1">كيتولد النموذج…</div>
                            <div class="text-xs text-slate-500">قد ياخد 5-15 ثانية</div>
                        </div>
                    </template>

                    {{-- RESULT --}}
                    <template x-if="genStep === 'result' && genResult">
                        <div class="px-5 py-4 prose-feedback" x-html="renderedGenMarkdown" dir="ltr"></div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="shrink-0 px-5 py-3 border-t border-white/[0.05] flex items-center justify-between gap-2 bg-[#0a0b0e]">
                    <template x-if="genStep === 'form'">
                        <button @click="closeGenModal()" class="px-4 h-9 rounded-xl border border-white/10 text-xs text-slate-400 hover:text-white hover:bg-white/5 transition-all" dir="rtl">إلغاء</button>
                    </template>
                    <template x-if="genStep === 'result'">
                        <button @click="genStep = 'form'" class="px-4 h-9 rounded-xl border border-white/10 text-xs text-slate-400 hover:text-white hover:bg-white/5 transition-all inline-flex items-center gap-1.5" dir="rtl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                            تعديل
                        </button>
                    </template>

                    <div class="flex-1"></div>

                    <template x-if="genStep === 'form'">
                        <button @click="submitGen()"
                                :disabled="genSelectedCount < 2"
                                class="px-5 h-9 rounded-xl bg-gradient-to-br from-orange-500 to-emerald-500 text-white text-sm font-bold shadow-md hover:shadow-lg active:scale-95 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                            توليد النموذج
                        </button>
                    </template>
                    <template x-if="genStep === 'result'">
                        <div class="flex items-center gap-2">
                            <button @click="printGeneratedEmail()"
                                    class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-white/10 text-slate-400 hover:text-white hover:bg-white/5 transition-all"
                                    title="طبع النموذج">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                            </button>
                            <button @click="copyGenEmailToDraft()"
                                    class="px-5 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-orange-600 text-white text-sm font-bold shadow-md hover:shadow-lg active:scale-95 transition-all inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                <span x-text="copiedToDraft ? 'تم النسخ' : 'نسخ للنص ديالي'"></span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- Score reveal overlay (shows after grading, before correction) --}}
    <template x-if="showRevealOverlay">
        <div class="fixed inset-0 z-[100] flex items-center justify-center"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="dismissReveal()"
             @click.self="dismissReveal()">
            <div class="absolute inset-0 bg-black/85 backdrop-blur-xl"></div>

            <div class="relative z-10 text-center px-6"
                 x-transition:enter="transition ease-out duration-500 delay-100"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-500 mb-4" dir="rtl">نتيجتك</div>

                {{-- Animated halo --}}
                <div class="relative inline-flex items-center justify-center mb-6">
                    <div class="absolute inset-0 rounded-full blur-3xl opacity-60 animate-pulse"
                         :class="revealHaloClass"></div>
                    <div class="relative flex flex-col items-center">
                        <div class="font-black tabular-nums leading-none"
                             :class="revealNumberClass"
                             style="font-size: clamp(6rem, 18vw, 11rem); text-shadow: 0 0 60px currentColor;">
                            <span x-text="animatedScore"></span>
                        </div>
                        <div class="text-slate-400 text-2xl md:text-3xl font-bold mt-2 tracking-wide" x-text="'/ ' + feedback.score_max"></div>
                    </div>
                </div>

                <div class="text-white text-xl md:text-2xl font-bold mb-1"
                     x-show="revealDone"
                     x-transition:enter="transition ease-out duration-400"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-text="feedback.level_label"></div>
                <div class="text-slate-500 text-sm mb-2"
                     x-show="revealDone"
                     x-transition:enter="transition ease-out duration-400 delay-100"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-text="'Roh: ' + feedback.raw_score + '/' + feedback.raw_max"></div>

                <template x-if="revealDone && (feedback.thema_verfehlt || feedback.situierung_verfehlt)">
                    <div class="flex items-center justify-center gap-2 mt-3 mb-1">
                        <template x-if="feedback.thema_verfehlt">
                            <span class="px-3 py-1 rounded-full bg-red-500/20 text-red-200 border border-red-500/40 text-xs font-bold">Thema verfehlt</span>
                        </template>
                        <template x-if="feedback.situierung_verfehlt">
                            <span class="px-3 py-1 rounded-full bg-amber-500/20 text-amber-200 border border-amber-500/40 text-xs font-bold">Situierung verfehlt</span>
                        </template>
                    </div>
                </template>

                <button @click="dismissReveal()"
                        x-show="revealDone"
                        x-transition:enter="transition ease-out duration-400 delay-300"
                        x-transition:enter-start="opacity-0 translate-y-3"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="mt-7 inline-flex items-center gap-2 px-7 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-orange-600 text-white text-sm font-bold shadow-lg shadow-emerald-500/30 hover:shadow-xl active:scale-95 transition-all">
                    <span dir="rtl">شوف التصحيح</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>

                <div class="mt-3 text-[11px] text-slate-600" x-show="revealDone" dir="rtl">ESC باش تسد</div>
            </div>
        </div>
    </template>

    {{-- AI feedback panel --}}
    <template x-if="feedback && !showRevealOverlay">
        <div class="mb-4 rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
            {{-- Header: score badge + meta pills --}}
            <div class="px-5 py-4 border-b border-white/[0.05] flex flex-wrap items-center justify-between gap-4 bg-gradient-to-br from-emerald-500/[0.06] to-orange-500/[0.04]">
                <div class="flex items-center gap-4">
                    <div class="relative w-20 h-20 rounded-2xl flex flex-col items-center justify-center font-black text-white border-2 leading-none"
                         :class="scoreColorClass(feedback.score, feedback.score_max)">
                        <span class="text-2xl" x-text="feedback.score"></span>
                        <span class="text-[10px] font-bold opacity-70 mt-0.5" x-text="'/ ' + feedback.score_max"></span>
                    </div>
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">telc Bewertung</div>
                        <div class="text-white font-bold text-lg" x-text="feedback.level_label || '—'"></div>
                        <div class="text-xs text-slate-400 mt-0.5">
                            <span x-text="'Roh: ' + feedback.raw_score + '/' + feedback.raw_max"></span>
                            <template x-if="feedback.wortzahl">
                                <span class="ml-2" x-text="'· ca. ' + feedback.wortzahl + ' Wörter'"></span>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <template x-if="feedback.thema_verfehlt">
                        <span class="px-2.5 py-1 rounded-full bg-red-500/20 text-red-200 border border-red-500/30 text-[11px] font-bold">Thema verfehlt</span>
                    </template>
                    <template x-if="feedback.situierung_verfehlt">
                        <span class="px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-200 border border-amber-500/30 text-[11px] font-bold">Situierung verfehlt</span>
                    </template>
                    <button @click="showRevealOverlay = true; startScoreAnimation()"
                            class="w-8 h-8 flex items-center justify-center rounded-full text-slate-500 hover:text-emerald-300 hover:bg-emerald-500/10 transition-all"
                            title="إعادة عرض النتيجة">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button x-show="verbesserteVersion"
                            @click="printVerbesserteVersion()"
                            class="w-8 h-8 flex items-center justify-center rounded-full text-slate-500 hover:text-orange-300 hover:bg-orange-500/10 transition-all"
                            title="طبع Verbesserte Version">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    </button>
                    <button @click="feedbackCompact = !feedbackCompact"
                            class="w-8 h-8 flex items-center justify-center rounded-full text-slate-500 hover:text-white hover:bg-white/5 transition-all"
                            :title="feedbackCompact ? 'توسيع' : 'تصغير'">
                        <template x-if="!feedbackCompact">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                        </template>
                        <template x-if="feedbackCompact">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </template>
                    </button>
                    <button @click="feedback = null" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-500 hover:text-white hover:bg-white/5 transition-all" title="إغلاق">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Markdown body (collapsible) --}}
            <div x-show="!feedbackCompact"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="px-5 py-4 prose-feedback" x-html="renderedMarkdown" dir="ltr"></div>
        </div>
    </template>

    <div class="grid lg:grid-cols-2 gap-4 lg:h-[calc(100vh-10rem)] items-start">

        {{-- LEFT (mobile: shown second, collapsible) --}}
        <div class="order-2 lg:order-1 h-full lg:overflow-y-auto lg:pr-2 space-y-3">
            {{-- Mobile-only collapse toggle --}}
            <button type="button"
                    @click="taskOpenMobile = !taskOpenMobile"
                    class="lg:hidden w-full flex items-center justify-between gap-3 px-4 py-3 rounded-2xl border border-white/[0.08] bg-[#111216] text-white text-sm font-bold active:scale-[0.99] transition-all">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Aufgabe + Punkte</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform" :class="taskOpenMobile ? 'rotate-180' : ''"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            <article class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden lg:!block" x-show="taskOpenMobile"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                <div class="px-5 py-3 border-b border-white/[0.05] flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Aufgabe</span>
                    <span class="text-xs text-slate-600">{{ $topic->minutes }} min · ca. {{ $topic->level === 'B2' ? '150' : '120' }} Wörter</span>
                </div>
                <div class="px-5 py-4">
                    <h2 class="text-lg md:text-xl font-bold text-white mb-1 leading-snug">{{ $topic->title }}</h2>
                    @if($topic->title_ar)
                    <p class="text-sm text-slate-400 mb-2" dir="rtl">{{ $topic->title_ar }}</p>
                    @endif
                    <div class="text-[14.5px] text-slate-200 leading-relaxed whitespace-pre-line">{{ $topic->scenario }}</div>
                </div>
            </article>

            @if(!empty($topic->points))
            <article class="rounded-2xl border border-emerald-500/20 bg-gradient-to-br from-emerald-500/[0.04] to-transparent overflow-hidden lg:!block" x-show="taskOpenMobile"
                     x-transition:enter="transition ease-out duration-200 delay-75"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                <div class="px-5 py-3 border-b border-emerald-500/[0.12]">
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-emerald-400">Punkte zu beachten · 4 محاور خاصك تتطرق ليهم</span>
                </div>
                <ol class="px-5 py-3 space-y-1.5 list-decimal list-inside text-sm text-slate-200">
                    @foreach($topic->points as $i => $p)
                    <li class="leading-snug pl-1"><span class="text-slate-300">{{ $p }}</span></li>
                    @endforeach
                </ol>
            </article>
            @endif

            @if($topic->level === 'B2' && !empty($topic->points))
            <button type="button"
                    @click="openGenModal()"
                    class="lg:!flex w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl border border-orange-500/30 bg-gradient-to-br from-orange-500/[0.08] to-emerald-500/[0.06] text-white text-sm font-bold hover:border-orange-400/60 hover:from-orange-500/[0.12] active:scale-[0.99] transition-all"
                    x-show="taskOpenMobile">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-300"><path d="M12 2v4"/><path d="M12 18v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="m16.24 16.24 2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="m16.24 7.76 2.83-2.83"/></svg>
                <span>Beispiel generieren</span>
                <span class="text-[10px] font-bold text-orange-300/80" dir="rtl">· نموذج جاهز للتدرب</span>
            </button>
            @endif
        </div>

        {{-- RIGHT: writing area (mobile: shown first) --}}
        <div class="order-1 lg:order-2 h-full flex flex-col gap-2 min-h-[60vh] lg:min-h-0">
            <div class="shrink-0 flex items-center justify-between px-1">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-500" dir="rtl">إجابتك</span>
                <div class="flex items-center gap-3 text-[11px]">
                    <span class="text-slate-500"><span class="font-bold" :class="wordCount < (parts.level === 'B2' ? 150 : 120) ? 'text-amber-400' : 'text-emerald-400'" x-text="wordCount"></span> Wörter</span>
                    <span class="text-slate-600">·</span>
                    <span class="text-slate-500"><span class="font-bold text-slate-300" x-text="text.length"></span> Zeichen</span>
                </div>
            </div>

            {{-- German special-character toolbar (desktop/tablet only — mobile keyboards have these via long-press) --}}
            <div class="hidden md:flex shrink-0 flex-wrap items-center gap-1" dir="ltr">
                <template x-for="ch in germanChars" :key="ch">
                    <button type="button"
                            @click="insertChar(ch)"
                            :disabled="submitted"
                            class="min-w-[34px] h-8 px-2 rounded-md bg-[#111216] border border-white/[0.08] text-slate-200 text-sm font-semibold hover:bg-white/[0.04] hover:border-emerald-500/40 hover:text-white active:scale-95 transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                            x-text="ch"
                            :title="'إضافة ' + ch"></button>
                </template>
                <span class="ml-1 text-[10px] text-slate-600 hidden sm:inline" dir="rtl">حروف ألمانية</span>
            </div>

            <textarea x-model="text"
                      x-ref="editor"
                      @input.debounce.500ms="persist()"
                      :disabled="submitted"
                      class="flex-1 min-h-[260px] lg:min-h-0 w-full bg-[#111216] border border-white/[0.08] rounded-2xl p-4 md:p-5 text-[15px] text-slate-100 leading-relaxed focus:outline-none focus:border-emerald-500/40 focus:ring-1 focus:ring-emerald-500/20 placeholder:text-slate-700 resize-none disabled:opacity-70"
                      dir="ltr"
                      placeholder="Schreiben Sie hier Ihren {{ $topic->type ?? 'Brief' }}…
Tipp: Beginnen Sie mit einer Anrede, beantworten Sie alle Punkte, schließen Sie mit einer Grußformel."></textarea>

            <div class="shrink-0 flex items-center gap-2">
                <span class="hidden sm:inline text-[11px] text-slate-600" dir="rtl" x-text="hasPersistedDraft ? 'تم الحفظ تلقائياً' : ''"></span>
                <div class="flex-1"></div>
                <button @click="printMyWriting()"
                        :disabled="!text.trim().length"
                        class="w-9 h-9 inline-flex items-center justify-center rounded-xl border border-white/10 text-slate-400 hover:text-white hover:bg-white/5 transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                        title="طبع النص ديالي">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                </button>
                <button @click="reset()"
                        :disabled="!text.length && !submitted"
                        class="px-4 h-9 rounded-xl border border-white/10 text-xs text-slate-400 hover:text-white hover:bg-white/5 transition-all disabled:opacity-40 disabled:cursor-not-allowed" dir="rtl">
                    مسح
                </button>
                <button @click="submitText()"
                        :disabled="grading || submitted || text.trim().length < 30"
                        class="btn-shine px-5 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-orange-600 text-white text-sm font-bold shadow-md shadow-emerald-500/30 hover:shadow-lg active:scale-95 transition-all disabled:opacity-40 disabled:cursor-not-allowed inline-flex items-center gap-2">
                    <template x-if="grading">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                    </template>
                    <span x-show="!submitted && !grading">تسليم وتصحيح</span>
                    <span x-show="grading">كيتصحح…</span>
                    <span x-show="submitted && !grading">تم التسليم</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function schreibenTopic(parts, timerEnabled) {
    return {
        parts,
        text: '',
        submitted: false,
        elapsed: 0,
        hasPersistedDraft: false,
        // AI grading
        grading: false,
        feedback: null,
        aiError: '',
        feedbackCompact: false,
        showRevealOverlay: false,
        animatedScore: 0,
        revealDone: false,
        _scoreAnimRaf: null,
        germanChars: ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü', '„', '“', '–', '€'],
        taskOpenMobile: false,
        // Beispiel generation
        showGenModal: false,
        genStep: 'form',
        genEmailType: '',
        genSelections: {},
        genIdeas: {},
        genResult: null,
        genError: '',
        copiedToDraft: false,
        // Timer
        timerEnabled: !!timerEnabled,
        secondsLeft: 0,
        _timerInterval: null,
        _elapsedInterval: null,

        get wordCount() {
            const trimmed = this.text.trim();
            if (!trimmed) return 0;
            return trimmed.split(/\s+/).length;
        },

        get genSelectedCount() {
            return Object.keys(this.genSelections).length;
        },

        get genSelectedKeysOrdered() {
            const order = ['LP1', 'LP2', 'LP3', 'LP4', 'OWN'];
            return order.filter(k => this.genSelections[k]);
        },

        get renderedGenMarkdown() {
            return this._renderMarkdown(this.genResult?.markdown);
        },

        _renderMarkdown(md) {
            if (!md) return '';
            try {
                const html = window.marked ? window.marked.parse(md) : md;
                // Add dir="auto" so each block picks LTR/RTL from its first strong character
                const withDir = html.replace(/<(p|li|td|th|blockquote|h[1-6])(\s|>)/gi, '<$1 dir="auto"$2');
                return window.DOMPurify ? window.DOMPurify.sanitize(withDir) : withDir;
            } catch (e) {
                return md;
            }
        },

        openGenModal() {
            this.genStep      = 'form';
            this.genError     = '';
            this.genResult    = null;
            this.copiedToDraft = false;
            if (!this.genEmailType) {
                this.genEmailType = this.parts.type || 'Beschwerde';
            }
            this.showGenModal = true;
        },

        closeGenModal() {
            this.showGenModal = false;
            this.copiedToDraft = false;
        },

        toggleGenSel(key, label) {
            if (this.genSelections[key]) {
                const next = { ...this.genSelections };
                delete next[key];
                this.genSelections = next;
                if (key in this.genIdeas) {
                    const nextIdeas = { ...this.genIdeas };
                    delete nextIdeas[key];
                    this.genIdeas = nextIdeas;
                }
            } else {
                if (this.genSelectedCount >= 3) return;
                this.genSelections = { ...this.genSelections, [key]: label };
                if (!(key in this.genIdeas)) this.genIdeas[key] = '';
            }
        },

        async submitGen() {
            if (this.genSelectedCount < 2) return;
            this.genError = '';
            this.genStep = 'loading';
            const selections = this.genSelectedKeysOrdered.map(key => ({
                label: this.genSelections[key],
                ideas: (this.genIdeas[key] || '').trim(),
            }));
            try {
                const res = await fetch('{{ route('schreiben.generate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        topic_id:   this.parts.id,
                        email_type: this.genEmailType,
                        selections,
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.ok) {
                    this.genError = data.error || data.message || 'تعذّر التوليد. حاول مرة أخرى.';
                    this.genStep = 'form';
                    return;
                }
                this.genResult = data.result;
                this.genStep = 'result';
            } catch (e) {
                this.genError = 'مشكل فالاتصال. عاود حاول.';
                this.genStep = 'form';
            }
        },

        get verbesserteVersion() {
            return this._extractMarkdownSection(this.feedback?.markdown, 2);
        },

        _extractMarkdownSection(md, num) {
            if (!md) return '';
            const re = new RegExp(`##\\s*${num}\\.\\s*[^\\n]+\\n+([\\s\\S]*?)(?=\\n##\\s|$)`);
            const m  = md.match(re);
            return m ? m[1].trim() : '';
        },

        printText({ title, subtitle, body }) {
            if (!body || !body.trim()) return;
            const esc = (s) => String(s ?? '')
                .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;').replaceAll("'", '&#39;');
            const wordCount = body.trim().split(/\s+/).filter(Boolean).length;
            const dateStr   = new Date().toLocaleDateString('de-DE', { year: 'numeric', month: 'long', day: 'numeric' });
            const html = `<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>${esc(title)}</title>
<style>
  @page { margin: 1.8cm; }
  * { box-sizing: border-box; }
  body { font-family: 'Cambria', 'Georgia', 'Times New Roman', serif; max-width: 720px; margin: 0 auto; padding: 0; color: #1a1a1a; line-height: 1.7; -webkit-print-color-adjust: exact; }
  .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 22px; }
  h1 { font-size: 17pt; margin: 0 0 4px; font-weight: 700; }
  .meta { color: #555; font-size: 10pt; }
  .body { font-size: 12pt; white-space: pre-wrap; word-break: break-word; }
  .footer { margin-top: 32px; padding-top: 10px; border-top: 1px solid #ccc; color: #777; font-size: 9pt; display: flex; justify-content: space-between; }
</style>
</head>
<body>
  <div class="header">
    <h1>${esc(title)}</h1>
    <div class="meta">${esc(subtitle)}</div>
  </div>
  <div class="body">${esc(body)}</div>
  <div class="footer">
    <span>${esc(dateStr)}</span>
    <span>${wordCount} Wörter</span>
  </div>
  <script>window.addEventListener('load', () => { window.focus(); window.print(); });<\/script>
</body>
</html>`;
            const w = window.open('', '_blank', 'width=820,height=900');
            if (!w) {
                alert('من فضلك اسمح بفتح نوافذ منبثقة باش تطبع.');
                return;
            }
            w.document.open();
            w.document.write(html);
            w.document.close();
        },

        printMyWriting() {
            this.printText({
                title:    @json($topic->title) + ' — Mein Schreiben',
                subtitle: 'telc Deutsch ' + this.parts.level + (this.parts.type ? ' · ' + this.parts.type : ''),
                body:     this.text,
            });
        },

        printVerbesserteVersion() {
            const body = this.verbesserteVersion;
            if (!body) { alert('ماكاينش Verbesserte Version فالتصحيح.'); return; }
            this.printText({
                title:    @json($topic->title) + ' — Verbesserte Version',
                subtitle: 'telc Deutsch ' + this.parts.level + ' · AI-korrigierte Fassung',
                body,
            });
        },

        printGeneratedEmail() {
            const body = (this.genResult?.email || '').trim();
            if (!body) return;
            this.printText({
                title:    @json($topic->title) + ' — Generiertes Beispiel',
                subtitle: 'telc Deutsch B2 · ' + (this.genEmailType || 'Beispiel'),
                body,
            });
        },

        copyGenEmailToDraft() {
            const email = (this.genResult?.email || '').trim();
            if (!email) return;
            const hasContent = this.text.trim().length > 0 && !this.submitted;
            if (hasContent && !confirm('هذا غادي يمسح النص اللي كاتب. واخا؟')) return;
            this.text = email;
            this.persist();
            this.copiedToDraft = true;
            setTimeout(() => {
                this.copiedToDraft = false;
                this.closeGenModal();
                this.$nextTick(() => this.$refs.editor && this.$refs.editor.focus());
            }, 700);
        },

        get renderedMarkdown() {
            return this._renderMarkdown(this.feedback?.markdown);
        },

        insertChar(ch) {
            if (this.submitted) return;
            const ta = this.$refs.editor;
            if (!ta) {
                this.text += ch;
                return;
            }
            const start = ta.selectionStart ?? this.text.length;
            const end   = ta.selectionEnd   ?? this.text.length;
            this.text = this.text.slice(0, start) + ch + this.text.slice(end);
            this.persist();
            this.$nextTick(() => {
                ta.focus();
                const pos = start + ch.length;
                ta.setSelectionRange(pos, pos);
            });
        },

        scoreColorClass(score, max) {
            const ratio = max ? score / max : 0;
            if (ratio >= 0.75) return 'bg-emerald-500/15 border-emerald-400 text-emerald-200';
            if (ratio >= 0.5)  return 'bg-amber-500/15 border-amber-400 text-amber-200';
            if (ratio >= 0.3)  return 'bg-orange-500/15 border-orange-400 text-orange-200';
            return 'bg-red-500/15 border-red-400 text-red-200';
        },

        get revealNumberClass() {
            const r = this.feedback ? (this.feedback.score / (this.feedback.score_max || 1)) : 0;
            if (r >= 0.75) return 'text-emerald-300';
            if (r >= 0.5)  return 'text-amber-300';
            if (r >= 0.3)  return 'text-orange-300';
            return 'text-red-300';
        },

        get revealHaloClass() {
            const r = this.feedback ? (this.feedback.score / (this.feedback.score_max || 1)) : 0;
            if (r >= 0.75) return 'bg-emerald-500/40';
            if (r >= 0.5)  return 'bg-amber-500/40';
            if (r >= 0.3)  return 'bg-orange-500/40';
            return 'bg-red-500/40';
        },

        startScoreAnimation() {
            if (!this.feedback) return;
            if (this._scoreAnimRaf) cancelAnimationFrame(this._scoreAnimRaf);
            this.animatedScore = 0;
            this.revealDone = false;
            const target = this.feedback.score;
            const duration = 1400;
            const start = performance.now();
            const easeOut = (t) => 1 - Math.pow(1 - t, 3);
            const step = (now) => {
                const t = Math.min(1, (now - start) / duration);
                this.animatedScore = Math.round(easeOut(t) * target);
                if (t < 1) {
                    this._scoreAnimRaf = requestAnimationFrame(step);
                } else {
                    this.animatedScore = target;
                    this._scoreAnimRaf = null;
                    this.revealDone = true;
                }
            };
            this._scoreAnimRaf = requestAnimationFrame(step);
        },

        dismissReveal() {
            this.showRevealOverlay = false;
            if (this._scoreAnimRaf) { cancelAnimationFrame(this._scoreAnimRaf); this._scoreAnimRaf = null; }
        },

        hydrate() {
            // Restore draft if any
            try {
                const raw = localStorage.getItem(this._draftKey());
                if (raw) {
                    const draft = JSON.parse(raw);
                    if (draft && typeof draft.text === 'string') {
                        this.text              = draft.text;
                        this.elapsed           = draft.elapsed || 0;
                        this.submitted         = !!draft.submitted;
                        this.hasPersistedDraft = true;
                    }
                }
            } catch (e) {}

            // Timer
            if (this.timerEnabled && !this.submitted) {
                this.secondsLeft = (this.parts.minutes || 30) * 60;
                this._startTimer();
            }
            this._startElapsed();
        },

        _draftKey() {
            return 'schreiben-draft-' + this.parts.slug;
        },

        persist() {
            try {
                localStorage.setItem(this._draftKey(), JSON.stringify({
                    text: this.text,
                    elapsed: this.elapsed,
                    submitted: this.submitted,
                    savedAt: Date.now(),
                }));
                this.hasPersistedDraft = true;
            } catch (e) {}
        },

        async submitText() {
            if (this.text.trim().length < 30 || this.grading) return;
            this.aiError = '';
            this.feedback = null;
            this.grading = true;
            try {
                const res = await fetch('{{ route('schreiben.grade') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        topic_id: this.parts.id,
                        text: this.text,
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.ok) {
                    this.aiError = data.error || data.message || 'تعذّر التصحيح. حاول مرة أخرى.';
                    return;
                }
                this.feedback = data.feedback;
                this.feedbackCompact = false;
                this.submitted = true;
                this._stopTimer();
                this._stopElapsed();
                this.persist();
                // Ninja reveal: show the score first, then the correction
                this.showRevealOverlay = true;
                this.$nextTick(() => this.startScoreAnimation());
            } catch (e) {
                this.aiError = 'مشكل فالاتصال. عاود حاول.';
            } finally {
                this.grading = false;
            }
        },

        reset() {
            this.text              = '';
            this.submitted         = false;
            this.elapsed           = 0;
            this.hasPersistedDraft = false;
            this.feedback          = null;
            this.aiError           = '';
            this.feedbackCompact   = false;
            this.showRevealOverlay = false;
            this.revealDone        = false;
            this.animatedScore     = 0;
            if (this._scoreAnimRaf) { cancelAnimationFrame(this._scoreAnimRaf); this._scoreAnimRaf = null; }
            try { localStorage.removeItem(this._draftKey()); } catch (e) {}
            // Restart timers
            this._stopTimer();
            this._stopElapsed();
            if (this.timerEnabled) {
                this.secondsLeft = (this.parts.minutes || 30) * 60;
                this._startTimer();
            }
            this._startElapsed();
        },

        _startTimer() {
            this._stopTimer();
            this._timerInterval = setInterval(() => {
                if (this.submitted) { this._stopTimer(); return; }
                this.secondsLeft = Math.max(0, this.secondsLeft - 1);
                if (this.secondsLeft === 0) {
                    this._stopTimer();
                    if (!this.submitted) this.submitText();
                }
            }, 1000);
        },

        _stopTimer() {
            if (this._timerInterval) { clearInterval(this._timerInterval); this._timerInterval = null; }
        },

        _startElapsed() {
            this._stopElapsed();
            this._elapsedInterval = setInterval(() => {
                if (this.submitted) { this._stopElapsed(); return; }
                this.elapsed += 1;
            }, 1000);
        },

        _stopElapsed() {
            if (this._elapsedInterval) { clearInterval(this._elapsedInterval); this._elapsedInterval = null; }
        },

        formatTime(seconds) {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return m + ':' + (s < 10 ? '0' + s : s);
        },
    };
}
</script>
@endpush

@endsection
