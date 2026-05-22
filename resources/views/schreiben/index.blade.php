@extends('layouts.app')

@section('title', 'Schreiben Practice B1/B2 | ' . config('app.name'))

@section('content')

<div class="max-w-7xl mx-auto px-4 md:px-6 pt-28 pb-16">

    {{-- Page Header --}}
    <div class="mb-6" dir="rtl">
        <div class="inline-flex items-center gap-2 text-emerald-400 font-bold mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            <span>Schreiben – التعبير الكتابي</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">اختار الموضوع وابدأ الكتابة</h1>
        <p class="text-slate-400 text-sm max-w-2xl">B1 → كتابة <span class="text-white font-semibold">Brief</span> (رسالة شخصية ردّ على رسالة صديق). B2 → كتابة <span class="text-white font-semibold">Beschwerde</span> (رسالة شكوى رسمية).</p>
    </div>

    {{-- Filters card --}}
    <div class="mb-6 p-3 md:p-5 rounded-2xl border border-white/[0.06] bg-gradient-to-br from-white/[0.03] to-transparent backdrop-blur-sm shadow-lg shadow-black/20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 md:gap-4">

            {{-- Level segmented control — locked to user's approved level (admins can switch) --}}
            @php $lockedLevel = auth()->user()->contentLevel(); @endphp
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-2" dir="rtl">المستوى</div>
                @if($lockedLevel)
                    <div class="inline-flex items-center gap-2 px-3 h-10 rounded-2xl bg-black/30 border border-white/[0.06]" dir="rtl" title="مقفّل على المستوى ديال الوصول ديالك">
                        <span class="px-3 py-1 rounded-xl text-sm font-bold {{ $lockedLevel === 'B2' ? 'bg-gradient-to-br from-orange-500 to-orange-700 text-white shadow-md shadow-orange-500/30' : 'bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-md shadow-emerald-500/30' }}">{{ $lockedLevel }}</span>
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
                                'B1'    => 'bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-lg shadow-emerald-500/30',
                                default => 'bg-gradient-to-br from-slate-500 to-slate-700 text-white shadow-lg shadow-slate-500/20',
                            };
                        @endphp
                        <a href="{{ route('schreiben.index', array_filter(['level' => $val ?: null] + ($type ? ['type' => $type] : []))) }}"
                           class="px-4 md:px-5 py-1.5 md:py-2 rounded-xl text-[13px] md:text-sm font-bold transition-all duration-300 ease-out text-center
                                  {{ $isActive ? $activeClass . ' scale-[1.02]' : 'text-slate-400 hover:text-white hover:bg-white/[0.06] hover:scale-[1.02] active:scale-[0.98]' }}">
                            {{ $label }}
                        </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Type filter --}}
            @if(count($types ?? []) > 0)
            <div class="min-w-0">
                <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-2" dir="rtl">النوع</div>
                <div class="flex items-center gap-1 p-1 rounded-2xl bg-black/30 border border-white/[0.06] shadow-inner shadow-black/30 overflow-x-auto" dir="rtl">
                    <a href="{{ route('schreiben.index', $level ? ['level' => $level] : []) }}"
                       class="shrink-0 px-3 py-1.5 rounded-xl text-[12px] font-semibold transition-all duration-300 ease-out
                              {{ !$type ? 'bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-md shadow-emerald-500/30' : 'text-slate-400 hover:text-white hover:bg-white/[0.06]' }}">
                        الكل
                    </a>
                    @foreach($types as $t)
                    <a href="{{ route('schreiben.index', array_filter(['level' => $level, 'type' => $t])) }}"
                       class="shrink-0 px-3 py-1.5 rounded-xl text-[12px] font-semibold transition-all duration-300 ease-out whitespace-nowrap
                              {{ $type === $t ? 'bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-md shadow-emerald-500/30' : 'text-slate-400 hover:text-white hover:bg-white/[0.06]' }}">
                        {{ $t }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Topics count + clear --}}
    <div class="flex items-center justify-between mb-4 text-xs text-slate-500" dir="rtl">
        <div class="flex items-center gap-2">
            <span class="font-bold text-white text-base">{{ count($topics) }}</span>
            <span>موضوع</span>
            @if($level)
            <span class="px-2 py-0.5 rounded-md font-bold {{ $level === 'B2' ? 'bg-orange-500/15 border border-orange-500/30 text-orange-300' : 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300' }}">{{ $level }}</span>
            @endif
            @if($type)
            <span class="px-2 py-0.5 rounded-md bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 font-bold">{{ $type }}</span>
            @endif
        </div>
        @if($level || $type)
        <a href="{{ route('schreiben.index') }}" class="text-[11px] text-slate-500 hover:text-white transition-colors flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            مسح الفلاتر
        </a>
        @endif
    </div>

    {{-- Topics Grid --}}
    <div x-data="schreibenIndex()" x-init="hydrate()" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
        @forelse($topics as $topic)
        @php
            $cardKey = (string) $topic->id;
            $href    = route('schreiben.topic', $topic->slug);
        @endphp
        <div class="group relative p-3.5 rounded-2xl border bg-[#111216] flex flex-col gap-2.5 transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-xl hover:bg-[#13141A]"
             :class="statusBorder('{{ $cardKey }}')"
             dir="ltr">
            <span class="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-br from-emerald-500/0 via-transparent to-orange-500/0 opacity-0 group-hover:opacity-100 group-hover:from-emerald-500/[0.04] group-hover:to-orange-500/[0.04] transition-opacity duration-500"></span>

            <div class="relative flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400 truncate">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    <span>Schreiben</span>
                    @if($topic->type)
                    <span class="text-slate-700">·</span>
                    <span class="text-slate-400">{{ $topic->type }}</span>
                    @endif
                </span>
                <span class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-black border
                             {{ $topic->level === 'B2' ? 'bg-orange-500/10 border-orange-500/30 text-orange-300' : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' }}">
                    {{ $topic->level }}
                </span>
            </div>

            <div class="relative">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="font-bold text-white text-base leading-snug">{{ $topic->title }}</h3>
                    @include('partials.topic-tag.badge', ['tag' => $topic->topicTag])
                </div>
                @if($topic->title_ar)
                <p class="text-xs text-slate-500 mt-0.5" dir="rtl">{{ $topic->title_ar }}</p>
                @endif
            </div>

            {{-- 2-line scenario preview --}}
            <p class="relative text-[12px] text-slate-500 leading-relaxed"
               style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">{{ $topic->scenario }}</p>

            {{-- Duration + status buttons --}}
            <div class="relative flex items-center justify-between gap-3">
                <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    {{ $topic->minutes }} min
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
                <button type="button" @click.prevent="toggleTimer('{{ $cardKey }}')"
                        :class="timer['{{ $cardKey }}'] ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300' : 'border-white/[0.08] text-slate-500 hover:text-white hover:border-white/20'"
                        class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg border transition-all"
                        :title="timer['{{ $cardKey }}'] ? 'مؤقت — تسليم تلقائي' : 'بدون مؤقت'">
                    <template x-if="timer['{{ $cardKey }}']">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2"/><path d="M5 3 2 6"/><path d="m22 6-3-3"/></svg>
                    </template>
                    <template x-if="!timer['{{ $cardKey }}']">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7"/><path d="M12 17a5 5 0 0 1-5-5"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
                    </template>
                </button>

                <a href="{{ route('schreiben.pdf', $topic->slug) }}"
                   class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg border border-white/[0.08] text-slate-500 hover:text-white hover:border-white/20 transition-all"
                   title="تحميل PDF">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                </a>

                <div class="flex-1"></div>

                <a :href="startHref('{{ $href }}', '{{ $cardKey }}')"
                   class="inline-flex items-center gap-1.5 px-3.5 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-orange-600 text-white text-[12px] font-bold shadow-md shadow-emerald-500/30 hover:shadow-lg hover:shadow-emerald-500/40 active:scale-95 transition-all">
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
function schreibenIndex() {
    return {
        statuses: {},
        timer: {},

        hydrate() {
            this.statuses = (window.TopicStatus?.all().schreiben) || {};
            try {
                this.timer = JSON.parse(localStorage.getItem('schreiben-timer') || '{}');
            } catch (e) { this.timer = {}; }
            window.addEventListener('topic-status-changed', (e) => {
                if (e.detail?.skill === 'schreiben') {
                    this.statuses = window.TopicStatus.all().schreiben || {};
                }
            });
        },

        toggleStatus(key, value) {
            window.TopicStatus.toggle('schreiben', key, value);
        },

        statusBorder(key) {
            const v = this.statuses[key];
            if (v === 'mastered') return 'border-emerald-500/40 hover:border-emerald-400/60 hover:shadow-emerald-500/10';
            if (v === 'revise')   return 'border-amber-500/40 hover:border-amber-400/60 hover:shadow-amber-500/10';
            return 'border-white/[0.08] hover:border-emerald-500/30 hover:shadow-emerald-500/10';
        },

        toggleTimer(key) {
            this.timer[key] = !this.timer[key];
            this.timer = { ...this.timer };
            try { localStorage.setItem('schreiben-timer', JSON.stringify(this.timer)); } catch (e) {}
        },

        startHref(baseHref, key) {
            return this.timer[key] ? (baseHref + (baseHref.includes('?') ? '&' : '?') + 'timer=1') : baseHref;
        },
    };
}
</script>
@endpush

@endsection
