@extends('layouts.app')

@section('title', 'Hören Practice B1/B2 | ' . config('app.name'))

@section('content')

<div class="max-w-7xl mx-auto px-4 md:px-6 pt-28 pb-16">

    {{-- Page Header --}}
    <div class="mb-6" dir="rtl">
        <div class="inline-flex items-center gap-2 text-orange-400 font-bold mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
            <span>Hören – الاستماع</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">اختار الموضوع وابدأ الاستماع</h1>
        <p class="text-slate-400 text-sm max-w-2xl">Teil 1 → فهم عام لنشرة إخبارية. Teil 3 → 5 جمل صح/خطأ على محادثة.</p>
    </div>

    {{-- Filters card --}}
    @php
        $teilOptions = [
            null => ['الكل', 'الكل'],
            1    => ['T1', 'Teil 1'],
            2    => ['T2', 'Teil 2'],
            3    => ['T3', 'Teil 3'],
        ];
    @endphp
    <div class="mb-6 p-3 md:p-5 rounded-2xl border border-white/[0.06] bg-gradient-to-br from-white/[0.03] to-transparent backdrop-blur-sm shadow-lg shadow-black/20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 md:gap-4">

            {{-- Teil --}}
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-2" dir="rtl">الجزء (Teil)</div>
                <div class="grid grid-cols-4 md:inline-flex md:items-center gap-0.5 p-1 rounded-2xl bg-black/30 border border-white/[0.06] shadow-inner shadow-black/30" dir="rtl">
                    @foreach($teilOptions as $val => [$short, $full])
                    @php $isActive = ($teil ?? null) === $val; @endphp
                    <a href="{{ route('hoeren.index', $val ? ['teil' => $val] + ($level ? ['level' => $level] : []) : ($level ? ['level' => $level] : [])) }}"
                       class="px-3 md:px-4 py-1.5 md:py-2 rounded-xl text-[12px] md:text-sm font-semibold whitespace-nowrap transition-all duration-300 ease-out text-center
                              {{ $isActive
                                  ? 'bg-gradient-to-br from-orange-500 to-amber-600 text-white shadow-lg shadow-orange-500/30 scale-[1.02]'
                                  : 'text-slate-400 hover:text-white hover:bg-white/[0.06] hover:scale-[1.02] active:scale-[0.98]' }}">
                        <span class="md:hidden">{{ $short }}</span>
                        <span class="hidden md:inline">{{ $full }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Level — locked to user's approved level (admins can switch) --}}
            @php $lockedLevel = auth()->user()->contentLevel(); @endphp
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-2" dir="rtl">المستوى</div>
                @if($lockedLevel)
                    <div class="inline-flex items-center gap-2 px-3 h-10 rounded-2xl bg-black/30 border border-white/[0.06]" dir="rtl" title="مقفّل على المستوى ديال الوصول ديالك">
                        <span class="px-3 py-1 rounded-xl text-sm font-bold {{ $lockedLevel === 'B2' ? 'bg-gradient-to-br from-orange-500 to-orange-700 text-white shadow-md shadow-orange-500/30' : 'bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-md shadow-amber-500/30' }}">{{ $lockedLevel }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-slate-500"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <a href="{{ route('access.create') }}" class="text-[10px] text-slate-500 hover:text-amber-300 transition-colors">بدّل</a>
                    </div>
                @else
                    <div class="grid grid-cols-3 md:inline-flex md:items-center gap-0.5 p-1 rounded-2xl bg-black/30 border border-white/[0.06] shadow-inner shadow-black/30" dir="rtl">
                        @foreach(['' => 'الكل', 'B1' => 'B1', 'B2' => 'B2'] as $val => $label)
                        @php
                            $isActive = ($level ?? '') === $val || ($val === '' && !$level);
                            $activeClass = match ($val) {
                                'B2'    => 'bg-gradient-to-br from-orange-500 to-orange-700 text-white shadow-lg shadow-orange-500/30',
                                'B1'    => 'bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-lg shadow-amber-500/30',
                                default => 'bg-gradient-to-br from-slate-500 to-slate-700 text-white shadow-lg shadow-slate-500/20',
                            };
                        @endphp
                        <a href="{{ route('hoeren.index', array_filter(['level' => $val ?: null] + ($teil ? ['teil' => $teil] : []))) }}"
                           class="px-4 md:px-5 py-1.5 md:py-2 rounded-xl text-[13px] md:text-sm font-bold transition-all duration-300 ease-out text-center
                                  {{ $isActive ? $activeClass . ' scale-[1.02]' : 'text-slate-400 hover:text-white hover:bg-white/[0.06] hover:scale-[1.02] active:scale-[0.98]' }}">
                            {{ $label }}
                        </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Topics count + clear --}}
    <div class="flex items-center justify-between mb-4 text-xs text-slate-500" dir="rtl">
        <div class="flex items-center gap-2">
            <span class="font-bold text-white text-base">{{ count($topics) }}</span>
            <span>موضوع</span>
            @if($teil)
            <span class="px-2 py-0.5 rounded-md bg-orange-500/15 border border-orange-500/30 text-orange-300 font-bold">{{ $teilOptions[$teil][1] }}</span>
            @endif
            @if($level)
            <span class="px-2 py-0.5 rounded-md font-bold {{ $level === 'B2' ? 'bg-orange-500/15 border border-orange-500/30 text-orange-300' : 'bg-amber-500/15 border border-amber-500/30 text-amber-300' }}">{{ $level }}</span>
            @endif
        </div>
        @if($teil || $level)
        <a href="{{ route('hoeren.index') }}" class="text-[11px] text-slate-500 hover:text-white transition-colors flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            مسح الفلاتر
        </a>
        @endif
    </div>

    {{-- Topics Grid --}}
    <div x-data="hoerenIndex()" x-init="hydrate()" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
        @forelse($topics as $topic)
        @php
            $cardKey = (string) $topic->id;
            $href    = route('hoeren.topic', $topic->slug);
        @endphp
        <div class="group relative p-3.5 rounded-2xl border bg-[#111216] flex flex-col gap-2.5 transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-xl hover:bg-[#13141A]"
             :class="statusBorder('{{ $cardKey }}')"
             dir="ltr">
            <span class="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-br from-orange-500/0 via-transparent to-amber-500/0 opacity-0 group-hover:opacity-100 group-hover:from-orange-500/[0.04] group-hover:to-amber-500/[0.04] transition-opacity duration-500"></span>

            <div class="relative flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-orange-400 truncate">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                    <span>Hören</span>
                    <span class="text-slate-700">·</span>
                    <span class="text-slate-400">Teil {{ $topic->teil }}</span>
                </span>
                <span class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-black border
                             {{ $topic->level === 'B2' ? 'bg-orange-500/10 border-orange-500/30 text-orange-300' : 'bg-amber-500/10 border-amber-500/30 text-amber-300' }}">
                    {{ $topic->level }}
                </span>
            </div>

            <div class="relative">
                <h3 class="font-bold text-white text-base leading-snug">{{ $topic->title }}</h3>
            </div>

            {{-- Duration + status buttons --}}
            <div class="relative flex items-center justify-between gap-3">
                <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                    {{ $topic->duration ?? '—' }}
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

            {{-- Action row --}}
            <div class="relative flex items-center gap-1.5 mt-auto pt-2 border-t border-white/[0.05]">
                <a href="{{ asset($topic->audio_url) }}" target="_blank" rel="noopener" download
                   class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg border border-white/[0.08] text-slate-500 hover:text-white hover:border-white/20 transition-all"
                   title="فتح الصوت في تبويب جديد">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                </a>
                <div class="flex-1"></div>
                <a href="{{ $href }}"
                   class="inline-flex items-center gap-1.5 px-3.5 h-8 rounded-lg bg-gradient-to-br from-orange-500 to-amber-600 text-white text-[12px] font-bold shadow-md shadow-orange-500/30 hover:shadow-lg hover:shadow-orange-500/40 active:scale-95 transition-all">
                    ابدأ
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            </div>
        </div>
        @empty
        <div class="sm:col-span-2 lg:col-span-3 text-center py-12 text-slate-500 text-sm" dir="rtl">
            <p>لا توجد مواضيع تطابق الفلاتر المختارة.</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
function hoerenIndex() {
    return {
        statuses: {},
        hydrate() {
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
            return 'border-white/[0.08] hover:border-orange-500/30 hover:shadow-orange-500/10';
        },
    };
}
</script>
@endpush

@endsection
