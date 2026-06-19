@extends('layouts.app')

@section('title', 'Telc B2 Mündlich · Teil 2 (Präsentation) | ' . config('app.name'))
@section('description', 'Telc B2 Mündlich Teil 2 — تحضير بطبقات: العُدّة الشاملة، عائلات المواضيع، والموضوع. تعلّم قطع Lego قابلة لإعادة الاستعمال بدل حفظ 44 جواب.')

@section('content')

<div class="max-w-7xl mx-auto px-4 md:px-6 pt-28 pb-16" dir="rtl" x-data="{ q: '' }">

    {{-- Header --}}
    <div class="mb-6">
        <div class="inline-flex items-center gap-2 text-amber-400 font-bold mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
            <span>Telc B2 · Mündlich · Teil 2 (Präsentation)</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">اختار الموضوع وحضّر العرض</h1>
        <p class="text-slate-400 max-w-3xl leading-relaxed">
            بدل ما تحفظ 44 جواب، تعلّم قطع <span class="text-amber-300 font-bold">Lego</span> قابلة لإعادة الاستعمال: <span class="text-amber-300">العُدّة الشاملة</span> كتنفع فكل المواضيع، <span class="text-sky-300">عائلات المواضيع</span> كتفلتري لي يتشابهو، وكل <span class="text-white">موضوع</span> فيه التفاصيل ديالو.
        </p>
    </div>

    {{-- Small universal banner (Layer 1 access) --}}
    @if($hasUniversal)
    <a href="{{ route('mundlich.b2-sprechen.universal') }}"
       class="group flex items-center gap-3 mb-8 px-4 py-3 rounded-xl border border-amber-500/30 bg-gradient-to-l from-amber-500/10 to-amber-500/[0.02] hover:border-amber-500/60 hover:from-amber-500/15 transition-all">
        <div class="shrink-0 w-9 h-9 rounded-lg bg-amber-500/15 text-amber-300 flex items-center justify-center group-hover:scale-110 transition-transform">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-widest text-amber-400/80">الطبقة 1</span>
                <span class="font-bold text-white">العُدّة الشاملة</span>
            </div>
            <div class="text-xs text-slate-400 truncate">حجج، بنيات العرض، وبلوكات الطوارئ — صالحة لأي موضوع.</div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-amber-400 group-hover:-translate-x-1 transition-transform"><path d="m15 18-6-6 6-6"/></svg>
    </a>
    @endif

    {{-- Cluster filter (عائلات المواضيع) — server-side, like the Lesen Teil filter --}}
    @if($clusters->count())
    <div class="mb-5">
        <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-2">العائلة (Cluster)</div>
        <div class="flex flex-wrap items-center gap-1.5 p-1 rounded-2xl bg-black/30 border border-white/[0.06]">
            <a href="{{ route('mundlich.b2-sprechen.index') }}"
               @class([
                    'px-3.5 py-1.5 rounded-xl text-[12px] md:text-sm font-semibold transition-all',
                    'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow' => ! $activeCluster,
                    'text-slate-400 hover:text-white' => $activeCluster,
               ])>الكل</a>
            @foreach($clusters as $cluster)
            <a href="{{ route('mundlich.b2-sprechen.index', ['cluster' => $cluster->cluster_key]) }}"
               @class([
                    'px-3.5 py-1.5 rounded-xl text-[12px] md:text-sm font-semibold whitespace-nowrap transition-all',
                    'bg-gradient-to-br from-sky-500 to-sky-700 text-white shadow' => $activeCluster && $activeCluster->cluster_key === $cluster->cluster_key,
                    'text-slate-400 hover:text-white' => ! $activeCluster || $activeCluster->cluster_key !== $cluster->cluster_key,
               ])>{{ $cluster->title }}</a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Active cluster: shared arguments + vocabulary (collapsible) --}}
    @if($activeCluster && (!empty($activeCluster->selected_universal_arguments) || !empty($activeCluster->cluster_vocabulary) || !empty($universalArguments)))
    <div class="mb-6 rounded-2xl border border-sky-500/20 bg-gradient-to-br from-sky-500/[0.06] to-transparent" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="w-full flex items-center justify-between gap-3 px-4 py-3">
            <div class="flex items-center gap-2 text-right">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="text-sky-300"><circle cx="6" cy="6" r="3"/><circle cx="18" cy="6" r="3"/><circle cx="12" cy="18" r="3"/><path d="M9 6h6M7.5 8.5 11 15.5M16.5 8.5 13 15.5"/></svg>
                <span class="font-bold text-white text-sm">حجج ومفردات عائلة « {{ $activeCluster->title }} »</span>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" :class="open && 'rotate-180'" class="text-slate-500 transition-transform shrink-0"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div x-show="open" x-collapse x-cloak>
            <div class="px-4 pb-4 pt-1 space-y-4 border-t border-white/[0.04]">
                @if(!empty($activeCluster->selected_universal_arguments))
                <div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-amber-400/70 mb-1.5">جمل مختارة</div>
                    <ul class="space-y-1" dir="ltr">
                        @foreach($activeCluster->selected_universal_arguments as $s)
                        <li class="flex gap-2 text-sm text-slate-300 leading-relaxed"><span class="text-amber-500/60 shrink-0">▸</span><span>{{ $s }}</span></li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if(!empty($activeCluster->cluster_vocabulary))
                <div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-sky-300/70 mb-1.5">مفردات العائلة</div>
                    <div class="flex flex-wrap gap-1.5" dir="ltr">
                        @foreach($activeCluster->cluster_vocabulary as $chip)
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-sky-500/10 text-sky-200 border border-sky-500/20">{{ $chip }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                @if(!empty($universalArguments))
                <div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-amber-400/70 mb-1.5">الحجج الشاملة المرتبطة</div>
                    <div class="flex flex-wrap gap-1.5" dir="ltr">
                        @foreach($universalArguments as $cat)
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-500/10 text-amber-200 border border-amber-500/20">{{ $cat['title'] ?? $cat['id'] }}</span>
                        @endforeach
                    </div>
                    <a href="{{ route('mundlich.b2-sprechen.universal') }}" class="inline-block mt-2 text-xs text-amber-400 hover:text-amber-300 transition-colors">العُدّة الشاملة الكاملة ←</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Count + search --}}
    <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <span class="font-bold text-white text-base tabular-nums">{{ $topics->count() }}</span>
            <span>موضوع</span>
            @if($activeCluster)
            <span class="px-2 py-0.5 rounded-md bg-sky-500/15 border border-sky-500/30 text-sky-300 font-bold">{{ $activeCluster->title }}</span>
            @endif
        </div>
        <div class="relative w-full max-w-xs">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-600"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" x-model="q" placeholder="ابحث عن موضوع…"
                   class="w-full bg-[#0B0C10] border border-white/10 rounded-xl py-2 pl-9 pr-3 text-sm text-slate-200 placeholder:text-slate-600 focus:outline-none focus:border-amber-500/50">
        </div>
    </div>

    {{-- Topic cards --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @forelse($topics as $topic)
        <a href="{{ route('mundlich.b2-sprechen.topic', $topic->slug) }}"
           x-show="!q || '{{ addslashes(mb_strtolower($topic->title)) }}'.includes(q.toLowerCase())"
           class="group p-4 rounded-2xl border border-white/[0.08] bg-[#111216] hover:border-amber-500/40 hover:bg-[#13141A] transition-all" dir="ltr">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-600">#{{ $topic->order }}</div>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-white/[0.04] text-slate-500">{{ count($topic->argumentsDafuer()) + count($topic->argumentsDagegen()) }} args</span>
            </div>
            <div class="text-base font-bold text-white leading-snug mb-2 group-hover:text-amber-300 transition-colors">{{ $topic->title }}</div>
            @if($topic->title_ar)
            <p class="text-xs text-slate-500" dir="rtl">{{ $topic->title_ar }}</p>
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
