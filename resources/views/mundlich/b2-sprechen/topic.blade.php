@extends('layouts.app')

@section('title', $topic->title . ' · Mündlich Teil 2 | ' . config('app.name'))

@section('content')

<div class="max-w-4xl mx-auto px-4 md:px-6 pt-28 pb-16" dir="rtl">

    <a href="{{ route('mundlich.b2-sprechen.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition-colors mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        رجوع لـ Mündlich Teil 2
    </a>

    {{-- Header --}}
    <div class="mb-6">
        <div class="inline-flex items-center gap-2 text-amber-400 font-bold mb-2">
            <span class="text-xs font-black uppercase tracking-widest">الطبقة 3 · Thema #{{ $topic->order }}</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2" dir="ltr">{{ $topic->title }}</h1>
        @if($topic->title_ar)
        <p class="text-slate-400">{{ $topic->title_ar }}</p>
        @endif

        {{-- Cluster chips --}}
        @if($clusters->count())
        <div class="flex flex-wrap items-center gap-2 mt-3">
            <span class="text-xs text-slate-500">العائلة:</span>
            @foreach($clusters as $cluster)
            <a href="{{ route('mundlich.b2-sprechen.index', ['cluster' => $cluster->cluster_key]) }}"
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-500/10 text-sky-200 border border-sky-500/25 hover:border-sky-400/50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"/><circle cx="18" cy="6" r="3"/><circle cx="12" cy="18" r="3"/><path d="M9 6h6M7.5 8.5 11 15.5M16.5 8.5 13 15.5"/></svg>
                {{ $cluster->title }}
            </a>
            @endforeach
            <a href="{{ route('mundlich.b2-sprechen.universal') }}"
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/10 text-amber-200 border border-amber-500/25 hover:border-amber-400/50 transition-colors">العُدّة الشاملة ←</a>
        </div>
        @endif
    </div>

    <div class="space-y-5">

        {{-- Highlight sentences --}}
        @if(!empty($topic->highlight_sentences))
        <section class="rounded-2xl border border-amber-500/25 bg-gradient-to-br from-amber-500/[0.07] to-transparent p-5">
            <div class="flex items-center gap-2 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-amber-400"><path d="m9 11-6 6v3h9l3-3"/><path d="m22 12-4.6 4.6a2 2 0 0 1-2.8 0l-5.2-5.2a2 2 0 0 1 0-2.8L14 4"/></svg>
                <h2 class="font-bold text-white">أهم الجمل من النص</h2>
            </div>
            <ul class="space-y-2" dir="ltr">
                @foreach($topic->highlight_sentences as $s)
                <li class="flex gap-2 text-sm text-slate-200 leading-relaxed">
                    <span class="text-amber-400/70 shrink-0 font-bold">{{ $loop->iteration }}.</span>
                    <span class="bg-amber-400/10 rounded px-1">{{ $s }}</span>
                </li>
                @endforeach
            </ul>
        </section>
        @endif

        {{-- Main ideas --}}
        @if(!empty($topic->main_ideas))
        <section class="rounded-2xl border border-white/[0.08] bg-[#111216] p-5">
            <h2 class="font-bold text-white mb-3">الأفكار الرئيسية <span class="text-slate-500 text-sm font-normal">(للتلخيص)</span></h2>
            <div class="space-y-2">
                @foreach($topic->main_ideas as $idea)
                <div class="flex gap-3 items-start" dir="ltr">
                    <span class="shrink-0 w-6 h-6 rounded-lg bg-amber-500/15 text-amber-300 flex items-center justify-center font-black text-xs">{{ $loop->iteration }}</span>
                    <p class="text-sm text-slate-300 leading-relaxed">{{ $idea }}</p>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Arguments: dafür / dagegen --}}
        @if($topic->argumentsDafuer() || $topic->argumentsDagegen())
        <section class="grid sm:grid-cols-2 gap-3">
            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.04] p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="text-emerald-400"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg>
                    <h3 class="font-bold text-emerald-300">Dafür (مع)</h3>
                </div>
                <div class="flex flex-wrap gap-1.5" dir="ltr">
                    @foreach($topic->argumentsDafuer() as $a)
                    <span class="px-2.5 py-1 rounded-lg text-sm font-medium bg-emerald-500/10 text-emerald-200 border border-emerald-500/20">{{ $a }}</span>
                    @endforeach
                </div>
            </div>
            <div class="rounded-2xl border border-rose-500/20 bg-rose-500/[0.04] p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="text-rose-400"><path d="M17 14V2"/><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z"/></svg>
                    <h3 class="font-bold text-rose-300">Dagegen (ضد)</h3>
                </div>
                <div class="flex flex-wrap gap-1.5" dir="ltr">
                    @foreach($topic->argumentsDagegen() as $a)
                    <span class="px-2.5 py-1 rounded-lg text-sm font-medium bg-rose-500/10 text-rose-200 border border-rose-500/20">{{ $a }}</span>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- Opinion --}}
        @if($topic->opinionPositive() || $topic->opinionNegative() || $topic->opinion_example)
        <section class="rounded-2xl border border-white/[0.08] bg-[#111216] p-5">
            <h2 class="font-bold text-white mb-3">الرأي (Meinung)</h2>
            @if($topic->opinionPositive() || $topic->opinionNegative())
            <div class="flex flex-wrap gap-4 mb-4" dir="ltr">
                @if($topic->opinionPositive())
                <div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-emerald-400/70 mb-1.5">positiv (+)</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($topic->opinionPositive() as $adj)
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-500/10 text-emerald-200 border border-emerald-500/20">{{ $adj }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                @if($topic->opinionNegative())
                <div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-rose-400/70 mb-1.5">negativ (−)</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($topic->opinionNegative() as $adj)
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-500/10 text-rose-200 border border-rose-500/20">{{ $adj }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif
            @if($topic->opinion_example)
            <div class="rounded-xl bg-black/30 border border-white/[0.05] p-3" dir="ltr">
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Beispiel</div>
                <p class="text-sm text-slate-300 leading-relaxed">{{ $topic->opinion_example }}</p>
            </div>
            @endif
        </section>
        @endif

        {{-- Experience --}}
        @if($topic->experience_example)
        <section class="rounded-2xl border border-white/[0.08] bg-[#111216] p-5">
            <h2 class="font-bold text-white mb-3">التجربة (Erfahrung)</h2>
            <div class="rounded-xl bg-black/30 border border-white/[0.05] p-3" dir="ltr">
                <p class="text-sm text-slate-300 leading-relaxed">{{ $topic->experience_example }}</p>
            </div>
        </section>
        @endif

        {{-- Difficult vocabulary --}}
        @if(!empty($topic->difficult_vocabulary))
        <section class="rounded-2xl border border-white/[0.08] bg-[#111216] p-5">
            <h2 class="font-bold text-white mb-3">مفردات صعيبة</h2>
            <div class="flex flex-wrap gap-1.5" dir="ltr">
                @foreach($topic->difficult_vocabulary as $v)
                <span class="px-2.5 py-1 rounded-lg text-sm font-medium bg-violet-500/10 text-violet-200 border border-violet-500/20">{{ $v }}</span>
                @endforeach
            </div>
        </section>
        @endif

    </div>
</div>

@endsection
