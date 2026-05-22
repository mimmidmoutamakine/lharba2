@extends('layouts.app')

@section('title', 'Hören · Teil ' . $teilNum . ' · الإمتحانات | ' . config('app.name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 md:px-6 pt-28 pb-16"
     x-data="hoerenIndex()" x-init="init()">

    @include('hoeren._header')

    <div class="flex items-center gap-2 mb-4 text-xs text-slate-500" dir="rtl">
        <span class="font-bold text-white text-base tabular-nums">{{ $exams->total() ?? $exams->count() }}</span>
        <span>امتحان</span>
        <span class="px-2 py-0.5 rounded-md bg-amber-500/15 border border-amber-500/30 text-amber-300 font-bold">Teil {{ $teilNum }}</span>
    </div>

    {{-- Exam cards. Outer is a <div> (not <a>) so the راجع/حفظت buttons inside
         don't fight with navigation. The "ابدأ" button is the actual link. --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
        @forelse($exams as $exam)
        @php
            $href      = route('hoeren.exam', ['teil' => $teilKey, 'exam' => $exam->slug]);
            $stmtCount = $exam->statements_count;
            $hasAudio  = ! empty($exam->audio_path);
            // Stable status key: teil + slug. Persists across re-imports (slug is
            // derived from title), and unique within the user's selected level.
            $cardKey   = 't' . $teilNum . '-' . $exam->slug;
        @endphp
        <div class="group relative p-3.5 rounded-2xl border bg-[#111216] flex flex-col gap-2.5 transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-xl hover:bg-[#13141A]"
             :class="statusBorder('{{ $cardKey }}')"
             dir="ltr">
            <div class="flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                    <span>Hören · T{{ $teilNum }}</span>
                </span>
                @if($hasAudio)
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 inline-flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    صوت
                </span>
                @else
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-700/50 border border-slate-700 text-slate-500">بلا صوت</span>
                @endif
            </div>

            <h3 class="font-bold text-white text-base leading-snug" dir="auto">{{ $exam->title }}</h3>

            {{-- Status buttons row — small, mirror Lesen card design --}}
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

            <div class="flex items-center justify-end gap-3 mt-auto pt-2 border-t border-white/[0.05]">
                <a href="{{ $href }}"
                   class="inline-flex items-center gap-1.5 px-3.5 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white text-[12px] font-bold shadow-md shadow-amber-500/30 hover:shadow-amber-500/40 active:scale-95 transition-all">
                    ابدأ
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            </div>
        </div>
        @empty
        <div class="sm:col-span-2 lg:col-span-3 text-center py-12 text-slate-500 text-sm" dir="rtl">
            ما كاينش امتحانات فهاد الجزء حاليا.
        </div>
        @endforelse
    </div>

    @if(method_exists($exams, 'hasPages') && $exams->hasPages())
    <div class="mt-6">{{ $exams->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
function hoerenIndex() {
    return {
        // Per-card mastered/revise status. Backed by window.TopicStatus (shared
        // localStorage), so it stays in sync with خطتي and across browser tabs.
        statuses: {},

        init() {
            this.statuses = (window.TopicStatus?.all().hoeren) || {};
            window.addEventListener('topic-status-changed', (e) => {
                if (e.detail?.skill === 'hoeren') {
                    this.statuses = window.TopicStatus.all().hoeren || {};
                }
            });
        },

        toggleStatus(key, value) {
            window.TopicStatus.toggle('hoeren', key, value);
        },

        statusBorder(key) {
            const v = this.statuses[key];
            if (v === 'mastered') return 'border-emerald-500/40 hover:border-emerald-400/60 hover:shadow-emerald-500/10';
            if (v === 'revise')   return 'border-amber-500/40 hover:border-amber-400/60 hover:shadow-amber-500/10';
            return 'border-white/[0.08] hover:border-amber-500/30 hover:shadow-amber-500/10';
        },
    };
}
</script>
@endpush
@endsection
