@extends('layouts.app')

@section('title', 'Telc B2 Mündlich · Gemeinsam etwas planen | ' . config('app.name'))
@section('description', 'Telc B2 Mündlich Teil 3 — Gemeinsam etwas planen. Strukturen einmal trainieren, dann nur Vokabular wechseln.')

@section('content')

<div class="max-w-7xl mx-auto px-4 md:px-6 pt-28 pb-16" dir="rtl"
     x-data="{ q: '' }">

    {{-- Header --}}
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 text-amber-400 font-bold mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
            <span>Telc B2 · Mündlich · Teil 3</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Gemeinsam etwas planen</h1>
        <p class="text-slate-400 max-w-2xl">
            بدل ما تحفظ ديالوجات، تعلّم البنيات اللغوية مرة واحدة وغيّر الكلمات حسب الموضوع. كل موضوع كاين فيه الكلمات اللي يحتاجها.
        </p>
    </div>

    {{-- Two CTAs: structures (train once) vs topics (apply per task) --}}
    <div class="grid sm:grid-cols-2 gap-4 mb-10">
        @if($hasStructures)
        <a href="{{ route('mundlich.b2-planning.structures') }}"
           class="group p-5 rounded-2xl border border-amber-500/30 bg-gradient-to-br from-amber-500/10 to-amber-500/[0.02] hover:border-amber-500/60 hover:shadow-lg hover:shadow-amber-500/10 transition-all">
            <div class="flex items-start gap-4">
                <div class="shrink-0 w-12 h-12 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/></svg>
                </div>
                <div class="flex-1 text-right">
                    <div class="text-xs font-black uppercase tracking-widest text-amber-400 mb-1">الخطوة 1</div>
                    <div class="text-lg font-bold text-white mb-1">Strukturen</div>
                    <div class="text-sm text-slate-400">تعلّم البنيات اللغوية الموحَّدة (تستعملها مع كل المواضيع).</div>
                </div>
            </div>
        </a>
        @else
        <div class="p-5 rounded-2xl border border-amber-500/20 bg-amber-500/5 opacity-70">
            <div class="flex items-start gap-4">
                <div class="shrink-0 w-12 h-12 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                </div>
                <div class="flex-1 text-right">
                    <div class="text-xs font-black uppercase tracking-widest text-amber-400 mb-1">الخطوة 1</div>
                    <div class="text-lg font-bold text-white mb-1">Strukturen</div>
                    <div class="text-sm text-slate-500">قريبا — لم يحمَّل ملف البنيات بعد.</div>
                </div>
            </div>
        </div>
        @endif

        <div class="p-5 rounded-2xl border border-white/[0.08] bg-[#111216]">
            <div class="flex items-start gap-4">
                <div class="shrink-0 w-12 h-12 rounded-xl bg-white/[0.05] text-slate-300 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="7.5 4.21 12 6.81 16.5 4.21"/><polyline points="7.5 19.79 7.5 14.6 3 12"/><polyline points="21 12 16.5 14.6 16.5 19.79"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </div>
                <div class="flex-1 text-right">
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-1">الخطوة 2</div>
                    <div class="text-lg font-bold text-white mb-1">{{ $topics->count() }} موضوع</div>
                    <div class="text-sm text-slate-400">اختر موضوعاً واملأ البنيات بالمفردات الخاصة به.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="mb-6">
        <div class="relative max-w-md">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-600"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" x-model="q" placeholder="ابحث عن موضوع…"
                   class="w-full bg-[#0B0C10] border border-white/10 rounded-xl py-2.5 pl-10 pr-4 text-sm text-slate-200 placeholder:text-slate-600 focus:outline-none focus:border-amber-500/50">
        </div>
    </div>

    {{-- Topic grid --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @forelse($topics as $topic)
        <a href="{{ route('mundlich.b2-planning.topic', $topic->slug) }}"
           x-show="!q || '{{ addslashes(mb_strtolower($topic->label)) }}'.includes(q.toLowerCase()) || '{{ addslashes(mb_strtolower($topic->slug)) }}'.includes(q.toLowerCase())"
           class="group p-4 rounded-2xl border border-white/[0.08] bg-[#111216] hover:border-amber-500/40 hover:bg-[#13141A] transition-all" dir="ltr">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-600">{{ $topic->topic_type ?? 'topic' }}</div>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-white/[0.04] text-slate-500">{{ count($topic->aspekte ?? []) }} aspekte</span>
            </div>
            <div class="text-base font-bold text-white leading-snug mb-2 group-hover:text-amber-300 transition-colors">{{ $topic->label }}</div>
            @if($topic->topic_text)
            <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">{{ $topic->topic_text }}</p>
            @endif
        </a>
        @empty
        <div class="col-span-full py-16 text-center text-slate-500" dir="rtl">
            <div class="text-5xl mb-3">🎤</div>
            <div class="text-sm">لا توجد مواضيع منشورة بعد</div>
        </div>
        @endforelse
    </div>

</div>

@endsection
