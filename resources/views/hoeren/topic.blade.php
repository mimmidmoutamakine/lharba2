@extends('layouts.app')
@section('title', $topic->title . ' | Hören | ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto px-4 md:px-6 pt-28 md:pt-32 pb-8"
     x-data="hoerenTopic({{ json_encode([
         'slug'           => $topic->slug,
         'level'          => $topic->level,
         'teil'           => $topic->teil,
         'audioUrl'       => $topic->audio_url ? asset($topic->audio_url) : null,
         'correctNumbers' => $topic->correct_numbers ?? [],
         'statements'     => $topic->statements ?? [],
     ]) }})"
>
    {{-- Floating control --}}
    <div class="fixed top-[72px] md:top-[88px] right-3 md:right-4 z-30 flex items-center gap-1.5" dir="ltr">
        <a href="{{ route('hoeren.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-full bg-[#111216]/85 backdrop-blur border border-white/10 text-slate-400 hover:text-white hover:bg-[#111216] active:scale-95 transition-all shadow-lg shadow-black/30"
           title="رجوع للمواضيع">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div class="inline-flex items-center gap-2 h-9 px-3 rounded-full bg-[#111216]/85 backdrop-blur border border-white/10 text-xs font-bold text-white shadow-lg shadow-black/30">
            <span>Teil {{ $topic->teil }}</span>
            <span class="px-1.5 py-0.5 rounded text-[9px] font-black {{ $topic->level === 'B2' ? 'bg-orange-500/25 text-orange-200' : 'bg-amber-500/25 text-amber-200' }}">{{ $topic->level }}</span>
        </div>
    </div>

    {{-- Title --}}
    <div class="mb-5">
        <div class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-[0.25em] text-orange-400 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
            <span>Hören · Teil {{ $topic->teil }}</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-white">{{ $topic->title }}</h1>
        @if($topic->duration)
        <p class="text-sm text-slate-500 mt-1">{{ $topic->duration }}</p>
        @endif
    </div>

    {{-- Score bar (after submit) --}}
    <template x-if="submitted && total > 0">
        <div class="mb-4 p-4 rounded-xl flex items-center justify-between gap-4"
             :class="score === total ? 'bg-green-500/10 border border-green-500/20' : score >= total/2 ? 'bg-yellow-500/10 border border-yellow-500/20' : 'bg-red-500/10 border border-red-500/20'">
            <div dir="rtl">
                <div class="font-bold text-white text-lg" x-text="score === total ? '🎉 ممتاز!' : score + '/' + total + ' إجابة صحيحة'"></div>
                <div class="text-sm text-slate-400 mt-0.5" x-text="score === total ? 'كل الإجابات صحيحة' : 'الأخضر = الإجابة الصحيحة'"></div>
            </div>
            <button @click="reset()" class="shrink-0 px-4 py-2 rounded-xl border border-white/10 text-sm text-slate-300 hover:text-white hover:bg-white/5 transition-all" dir="rtl">إعادة المحاولة</button>
        </div>
    </template>

    {{-- Audio player --}}
    @if($topic->audio_url)
    <div class="mb-5 p-4 rounded-2xl border border-white/[0.08] bg-gradient-to-br from-orange-500/[0.04] to-transparent">
        <audio controls preload="metadata" class="w-full" src="{{ asset($topic->audio_url) }}">
            Ihr Browser unterstützt keine Audiowiedergabe.
        </audio>
        <div class="mt-2 flex items-center gap-2 text-[11px] text-slate-500" dir="rtl">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <span>اِسمع التسجيل أكثر من مرة قبل ما تجاوب.</span>
        </div>
    </div>
    @endif

    @if($topic->teil === 1)
    {{-- Teil 1: 5 R/F statements --}}
    <div class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
        <div class="px-5 py-3 border-b border-white/[0.05] flex items-center justify-between" dir="rtl">
            <span class="text-[11px] font-black uppercase tracking-widest text-orange-400">5 جمل · صح / خطأ</span>
            <span class="text-[11px] text-slate-500" x-text="Object.keys(answers).length + '/' + total"></span>
        </div>

        <div class="p-3 space-y-2">
            <template x-for="(s, i) in statements" :key="i">
                <div class="rounded-xl border bg-[#0B0C10] overflow-hidden transition-all"
                     :class="submitted
                         ? (answers[i] === s.answer ? 'border-green-500/40' : 'border-red-500/40')
                         : answers[i] !== undefined ? 'border-orange-500/40' : 'border-white/[0.08]'">
                    {{-- Statement text --}}
                    <div class="px-4 py-3 flex items-start gap-3">
                        <span class="shrink-0 w-7 h-7 flex items-center justify-center rounded-md text-xs font-black mt-0.5"
                              :class="submitted
                                  ? (answers[i] === s.answer ? 'bg-green-500/15 text-green-400' : 'bg-red-500/15 text-red-400')
                                  : answers[i] !== undefined ? 'bg-orange-500/20 text-orange-200' : 'bg-white/5 text-slate-400'"
                              x-text="i + 1"></span>
                        <p class="text-sm text-white leading-relaxed flex-1" x-html="renderStatement(s)"></p>
                    </div>
                    {{-- Arabic story (mnemonic) — revealed after submit --}}
                    <template x-if="submitted && s.story">
                        <div class="px-4 pb-3 -mt-1">
                            <div class="text-[12px] text-slate-300 leading-relaxed bg-white/[0.03] border border-white/[0.05] rounded-lg p-2.5" dir="rtl" x-text="s.story"></div>
                        </div>
                    </template>

                    {{-- Richtig / Falsch buttons --}}
                    <div class="px-3 pb-3 grid grid-cols-2 gap-2" dir="ltr">
                        <button @click="pick(i, 'richtig')"
                                :disabled="submitted"
                                class="px-3 py-2.5 rounded-xl border text-sm font-bold transition-all flex items-center justify-center gap-2 active:scale-[0.99]"
                                :class="submitted
                                    ? (s.answer === 'richtig'
                                        ? 'border-green-500 bg-green-500/15 text-green-300'
                                        : answers[i] === 'richtig'
                                            ? 'border-red-500 bg-red-500/15 text-red-300 line-through'
                                            : 'border-white/[0.06] text-slate-600 cursor-default')
                                    : answers[i] === 'richtig'
                                        ? 'border-green-500 bg-green-500/15 text-green-300 shadow-md shadow-green-500/20'
                                        : 'border-white/[0.08] bg-[#111216] text-slate-300 hover:border-green-500/40 hover:text-green-300'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            Richtig
                        </button>
                        <button @click="pick(i, 'falsch')"
                                :disabled="submitted"
                                class="px-3 py-2.5 rounded-xl border text-sm font-bold transition-all flex items-center justify-center gap-2 active:scale-[0.99]"
                                :class="submitted
                                    ? (s.answer === 'falsch'
                                        ? 'border-green-500 bg-green-500/15 text-green-300'
                                        : answers[i] === 'falsch'
                                            ? 'border-red-500 bg-red-500/15 text-red-300 line-through'
                                            : 'border-white/[0.06] text-slate-600 cursor-default')
                                    : answers[i] === 'falsch'
                                        ? 'border-red-500 bg-red-500/15 text-red-300 shadow-md shadow-red-500/20'
                                        : 'border-white/[0.08] bg-[#111216] text-slate-300 hover:border-red-500/40 hover:text-red-300'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            Falsch
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div class="px-3 py-3 border-t border-white/[0.05]">
            <template x-if="!submitted">
                <button @click="submit()"
                        :disabled="Object.keys(answers).length < total"
                        class="btn-shine w-full py-3 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                    <span x-text="Object.keys(answers).length + '/' + total + ' — تصحيح'"></span>
                </button>
            </template>
        </div>
    </div>

    @elseif($topic->teil === 3)
    {{-- Teil 3: 5 numbered statements (text not in dataset) --}}
    <div class="rounded-2xl border border-white/[0.08] bg-[#111216] p-5">
        <div class="text-[11px] font-black uppercase tracking-widest text-orange-400 mb-3" dir="rtl">
            اختر الأرقام التي ترى أنها <span class="text-white">صحيحة</span> (حسب الحوار)
        </div>
        <div class="grid grid-cols-5 gap-2 mb-4" dir="ltr">
            @for($n = 1; $n <= 5; $n++)
            <button type="button"
                    @click="togglePick({{ $n }})"
                    :disabled="submitted"
                    class="aspect-square flex items-center justify-center rounded-xl border-2 text-2xl font-black transition-all"
                    :class="submitted
                        ? (correctNumbers.includes({{ $n }})
                            ? (picks.includes({{ $n }}) ? 'border-green-500 bg-green-500/15 text-green-300' : 'border-green-500/40 bg-green-500/[0.05] text-green-400/70')
                            : (picks.includes({{ $n }}) ? 'border-red-500 bg-red-500/15 text-red-300 line-through' : 'border-white/[0.06] text-slate-600'))
                        : picks.includes({{ $n }}) ? 'border-orange-500 bg-orange-500/15 text-white shadow-md shadow-orange-500/20 scale-[1.03]' : 'border-white/[0.08] bg-[#0B0C10] text-slate-300 hover:border-white/30 hover:text-white active:scale-95'">
                {{ $n }}
            </button>
            @endfor
        </div>
        <template x-if="!submitted">
            <button @click="submit()"
                    :disabled="picks.length === 0"
                    class="btn-shine w-full py-3 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 text-white text-sm font-bold transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                تصحيح
            </button>
        </template>
        <template x-if="submitted">
            <div class="text-center text-sm text-slate-400" x-text="formatNumbers(correctNumbers)"></div>
        </template>
    </div>

    @else
    {{-- Teil 2: just listening (dataset doesn't carry questions) --}}
    <div class="rounded-2xl border border-white/[0.08] bg-[#111216] p-5">
        <div class="text-[11px] font-black uppercase tracking-widest text-orange-400 mb-2" dir="rtl">تمرين الاستماع</div>
        <p class="text-sm text-slate-300 leading-relaxed" dir="rtl">
            هذا تسجيل من Hören Teil 2 (Detailverstehen). اِسمع وحاول فهم التفاصيل
            (الأرقام، الأماكن، الأسماء…). الأسئلة التفاعلية ستُضاف لاحقاً.
        </p>
    </div>
    @endif
</div>

@push('scripts')
<script>
function hoerenTopic(opts) {
    return {
        slug:           opts.slug,
        teil:           opts.teil,
        statements:     opts.statements || [],
        correctNumbers: (opts.correctNumbers || []).map(n => Number(n)),
        // Teil 1
        answers:   {},
        // Teil 3
        picks:     [],
        submitted: false,
        score:     0,
        get total() {
            if (this.teil === 1) return this.statements.length;
            if (this.teil === 3) return this.correctNumbers.length;
            return 0;
        },

        pick(i, value) {
            if (this.submitted) return;
            this.answers[i] = value;
            this.answers = { ...this.answers };
        },

        togglePick(n) {
            if (this.submitted) return;
            const i = this.picks.indexOf(n);
            if (i === -1) this.picks.push(n);
            else this.picks.splice(i, 1);
        },

        submit() {
            if (this.teil === 1) {
                if (Object.keys(this.answers).length < this.statements.length) return;
                this.submitted = true;
                this.score = this.statements.filter((s, i) => this.answers[i] === s.answer).length;
            } else if (this.teil === 3) {
                if (this.picks.length === 0) return;
                this.submitted = true;
                const right  = this.correctNumbers.filter(n => this.picks.includes(n)).length;
                const wrong  = this.picks.filter(n => !this.correctNumbers.includes(n)).length;
                this.score = Math.max(0, Math.min(this.total, right - wrong));
            }
        },

        reset() {
            this.answers   = {};
            this.picks     = [];
            this.submitted = false;
            this.score     = 0;
        },

        formatNumbers(arr) {
            return arr.length ? 'الصواب: ' + [...arr].sort((a, b) => a - b).join(', ') : '—';
        },

        // Render the statement with each highlight substring wrapped in a styled <mark>.
        // We escape first so the source text can never inject HTML; the only HTML we
        // ever insert is our own <mark> tags.
        renderStatement(s) {
            const escape = t => String(t || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            let html = escape(s?.text ?? '');
            const seen = new Set();
            (s?.highlights ?? []).forEach(hl => {
                if (!hl || seen.has(hl)) return;
                seen.add(hl);
                const safeHl = escape(hl);
                if (!safeHl) return;
                // Replace every occurrence (split-join is safer than regex w/ special chars)
                html = html.split(safeHl).join('<mark class="hl-key">' + safeHl + '</mark>');
            });
            return html;
        },
    };
}
</script>
@endpush

@endsection
