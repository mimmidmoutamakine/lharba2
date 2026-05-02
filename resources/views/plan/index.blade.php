@extends('layouts.app')

@section('title', 'خطتي · Plan | ' . config('app.name'))

@section('content')

<div class="max-w-7xl mx-auto px-4 md:px-6 pt-28 pb-16"
     x-data="planPage()"
     x-init="hydrate()">

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3" dir="rtl">
        <div>
            <div class="inline-flex items-center gap-2 text-amber-400 font-bold mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-4"/><circle cx="12" cy="6" r="4"/><path d="M12 10v12"/></svg>
                <span>خطتي · Mein Plan</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-white">شنو حفظت، شنو خاصك تراجع، ومنين تبدا</h1>
            <p class="text-slate-400 text-sm mt-1">نظرة سريعة على تقدّمك · all data is stored locally on your device</p>
        </div>

        {{-- Level filter — locked to user's approved level (admins can switch) --}}
        @php $lockedLevel = auth()->user()->contentLevel(); @endphp
        @if($lockedLevel)
            <div class="inline-flex items-center gap-2 px-3 h-10 rounded-2xl bg-black/30 border border-white/[0.06]" dir="rtl" title="مقفّل على المستوى ديال الوصول ديالك">
                <span class="px-3 py-1 rounded-xl text-sm font-bold {{ $lockedLevel === 'B2' ? 'bg-gradient-to-br from-orange-500 to-orange-700 text-white shadow-md shadow-orange-500/30' : 'bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-md shadow-amber-500/30' }}">{{ $lockedLevel }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-slate-500"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <a href="{{ route('access.create') }}" class="text-[10px] text-slate-500 hover:text-amber-300 transition-colors">بدّل</a>
            </div>
        @else
            <div class="flex items-center gap-1 p-1 rounded-2xl bg-black/30 border border-white/[0.06] shadow-inner shadow-black/30" dir="rtl">
                @foreach(['' => 'الكل', 'B1' => 'B1', 'B2' => 'B2'] as $val => $label)
                @php $isActive = ($level ?? '') === $val || ($val === '' && !$level); @endphp
                <a href="{{ route('plan', $val ? ['level' => $val] : []) }}"
                   class="px-4 py-1.5 rounded-xl text-sm font-bold transition-all
                          {{ $isActive
                              ? ($val === 'B2' ? 'bg-gradient-to-br from-orange-500 to-orange-700 text-white shadow-lg shadow-orange-500/30' : ($val === 'B1' ? 'bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-lg shadow-amber-500/30' : 'bg-gradient-to-br from-slate-500 to-slate-700 text-white'))
                              : 'text-slate-400 hover:text-white hover:bg-white/[0.06]' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Overall banner --}}
    <div class="mb-6 p-4 md:p-5 rounded-2xl border border-white/[0.06] bg-gradient-to-br from-white/[0.03] to-transparent">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-1">الإجمالي</div>
                <div class="text-2xl md:text-3xl font-bold text-white tabular-nums" x-text="overall.total"></div>
                <div class="text-xs text-slate-500 mt-0.5">تمرين</div>
            </div>
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.25em] text-emerald-400 mb-1">حفظت</div>
                <div class="text-2xl md:text-3xl font-bold text-emerald-300 tabular-nums" x-text="overall.mastered"></div>
                <div class="text-xs text-slate-500 mt-0.5"><span x-text="overall.masteredPct"></span>%</div>
            </div>
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.25em] text-amber-400 mb-1">خاصك تراجع</div>
                <div class="text-2xl md:text-3xl font-bold text-amber-300 tabular-nums" x-text="overall.revise"></div>
                <div class="text-xs text-slate-500 mt-0.5">للمراجعة</div>
            </div>
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400 mb-1">ما بدأتيش</div>
                <div class="text-2xl md:text-3xl font-bold text-slate-200 tabular-nums" x-text="overall.untouched"></div>
                <div class="text-xs text-slate-500 mt-0.5">جديد</div>
            </div>
        </div>
        <div class="mt-4 h-2 rounded-full bg-white/[0.04] overflow-hidden flex">
            <div class="h-full bg-emerald-500 transition-all duration-500" :style="'width: ' + overall.masteredPct + '%'"></div>
            <div class="h-full bg-amber-500 transition-all duration-500" :style="'width: ' + overall.revisePct + '%'"></div>
        </div>
    </div>

    {{-- Per-bucket cards (5 Lesen + Hören + Schreiben = 7) --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 mb-8">
        @foreach($buckets as $bucketKey => $bucket)
        @php $tone = $bucket['tone']; @endphp
        <a href="{{ $bucket['href'] }}" class="group rounded-2xl border border-white/[0.06] bg-[#111216] overflow-hidden hover:border-{{ $tone }}-500/30 transition-all">
            <div class="px-4 py-3 border-b border-white/[0.05] flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    @switch($bucket['icon'])
                        @case('book')
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-{{ $tone }}-400"><path d="M12 7v14M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                            @break
                        @case('puzzle')
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-{{ $tone }}-400"><path d="M19.439 7.85c-.049.322.059.648.289.878l1.568 1.568c.47.47.706 1.087.706 1.704s-.235 1.233-.706 1.704l-1.611 1.611a.98.98 0 0 1-.837.276c-.47-.07-.802-.48-.968-.925a2.501 2.501 0 1 0-3.214 3.214c.446.166.855.497.925.968a.979.979 0 0 1-.276.837l-1.61 1.61a2.404 2.404 0 0 1-1.705.707 2.402 2.402 0 0 1-1.704-.706l-1.568-1.568a1.026 1.026 0 0 0-.877-.29c-.493.074-.84.504-1.02.968a2.5 2.5 0 1 1-3.237-3.237c.464-.18.894-.527.967-1.02a1.026 1.026 0 0 0-.289-.877l-1.568-1.568A2.402 2.402 0 0 1 1.998 12c0-.617.236-1.234.706-1.704L4.23 8.77c.24-.24.581-.353.917-.303.515.077.877.528 1.073 1.01a2.5 2.5 0 1 0 3.259-3.259c-.482-.196-.933-.558-1.01-1.073-.05-.336.062-.676.303-.917l1.525-1.525A2.402 2.402 0 0 1 12 1.998c.617 0 1.234.236 1.704.706l1.568 1.568c.23.23.556.338.877.29.493-.074.84-.504 1.02-.968a2.5 2.5 0 1 1 3.237 3.237c-.464.18-.894.527-.967 1.02Z"/></svg>
                            @break
                        @case('headphones')
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-{{ $tone }}-400"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                            @break
                        @case('pencil')
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-{{ $tone }}-400"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            @break
                    @endswitch
                    <div class="min-w-0">
                        <div class="font-bold text-white text-[13px] leading-none truncate">{{ $bucket['label_de'] }}</div>
                        <div class="text-[10px] text-slate-500 mt-0.5" dir="rtl">{{ $bucket['label_ar'] }}</div>
                    </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-700 group-hover:text-{{ $tone }}-300 transition-colors shrink-0"><path d="m9 18 6-6-6-6"/></svg>
            </div>

            <div class="px-4 py-3">
                <div class="flex items-baseline justify-between gap-2 mb-1.5">
                    <div class="text-[11px] text-slate-500"><span class="font-bold text-white text-base tabular-nums" x-text="counts['{{ $bucketKey }}'].mastered"></span> / <span class="tabular-nums" x-text="counts['{{ $bucketKey }}'].total"></span></div>
                    <div class="text-[11px] font-bold text-emerald-300 tabular-nums" x-text="counts['{{ $bucketKey }}'].masteredPct + '%'"></div>
                </div>
                <div class="h-1.5 rounded-full bg-white/[0.05] overflow-hidden flex">
                    <div class="h-full bg-emerald-500 transition-all duration-500" :style="'width: ' + counts['{{ $bucketKey }}'].masteredPct + '%'"></div>
                    <div class="h-full bg-amber-500 transition-all duration-500" :style="'width: ' + counts['{{ $bucketKey }}'].revisePct + '%'"></div>
                </div>

                <div class="flex items-center justify-around mt-3 pt-2.5 border-t border-white/[0.05] text-center">
                    <div>
                        <div class="text-sm font-bold text-emerald-300 tabular-nums" x-text="counts['{{ $bucketKey }}'].mastered"></div>
                        <div class="text-[8px] text-slate-500 uppercase tracking-wider">حفظت</div>
                    </div>
                    <div class="w-px h-6 bg-white/[0.05]"></div>
                    <div>
                        <div class="text-sm font-bold text-amber-300 tabular-nums" x-text="counts['{{ $bucketKey }}'].revise"></div>
                        <div class="text-[8px] text-slate-500 uppercase tracking-wider">راجع</div>
                    </div>
                    <div class="w-px h-6 bg-white/[0.05]"></div>
                    <div>
                        <div class="text-sm font-bold text-slate-300 tabular-nums" x-text="counts['{{ $bucketKey }}'].untouched"></div>
                        <div class="text-[8px] text-slate-500 uppercase tracking-wider">جديد</div>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Action lists: revise + start ASAP --}}
    <div class="grid lg:grid-cols-2 gap-4">

        {{-- Revise list --}}
        <div class="rounded-2xl border border-amber-500/20 bg-gradient-to-br from-amber-500/[0.04] to-transparent overflow-hidden">
            <div class="px-5 py-3 border-b border-amber-500/[0.12] flex items-center justify-between">
                <span class="inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-amber-400">عاود راجع</span>
                </span>
                <span class="text-[11px] text-slate-500" dir="rtl"><span class="font-bold text-amber-300" x-text="reviseList.length"></span> عناصر</span>
            </div>
            <div class="divide-y divide-white/[0.04]">
                <template x-for="item in reviseList" :key="item.bucket + '-' + item.key">
                    <a :href="item.href" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-amber-500/[0.04] transition-colors group">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider mb-0.5">
                                <span :class="bucketTone(item.bucket)" x-text="bucketLabel(item.bucket)"></span>
                                <span class="text-slate-700">·</span>
                                <span class="text-slate-500" x-text="item.level"></span>
                            </div>
                            <div class="text-sm text-white truncate" x-text="item.title"></div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600 group-hover:text-amber-300 group-hover:-translate-x-0.5 transition-all"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </template>
                <template x-if="reviseList.length === 0">
                    <div class="px-5 py-8 text-center text-sm text-slate-500" dir="rtl">
                        ما عندك حتى موضوع للمراجعة. مزيان! 🎉
                    </div>
                </template>
            </div>
        </div>

        {{-- Start ASAP list --}}
        <div class="rounded-2xl border border-orange-500/20 bg-gradient-to-br from-orange-500/[0.04] to-transparent overflow-hidden">
            <div class="px-5 py-3 border-b border-orange-500/[0.12] flex items-center justify-between">
                <span class="inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-400"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-orange-400">ابدا من هنا</span>
                </span>
                <span class="text-[11px] text-slate-500" dir="rtl">من اللي ما بدأتيش</span>
            </div>
            <div class="divide-y divide-white/[0.04]">
                <template x-for="item in nextList" :key="item.bucket + '-' + item.key">
                    <a :href="item.href" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-orange-500/[0.04] transition-colors group">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider mb-0.5">
                                <span :class="bucketTone(item.bucket)" x-text="bucketLabel(item.bucket)"></span>
                                <span class="text-slate-700">·</span>
                                <span class="text-slate-500" x-text="item.level"></span>
                            </div>
                            <div class="text-sm text-white truncate" x-text="item.title"></div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-600 group-hover:text-orange-300 group-hover:-translate-x-0.5 transition-all"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </template>
                <template x-if="nextList.length === 0">
                    <div class="px-5 py-8 text-center text-sm text-slate-500" dir="rtl">
                        كملتي كلش! 🏆
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@php
    // Compact bucket map for the JS: { bucketKey: { storage, label_de, tone, items } }
    $bucketJs = [];
    foreach ($buckets as $bk => $b) {
        $bucketJs[$bk] = [
            'storage'  => $b['storage'],
            'label_de' => $b['label_de'],
            'tone'     => $b['tone'],
            'items'    => $b['items'],
        ];
    }
@endphp
<script>
function planPage() {
    const BUCKETS = {!! json_encode($bucketJs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!};

    // Flatten items, tagging each with its bucket + storage namespace
    const allItems = [];
    for (const [bucketKey, bucket] of Object.entries(BUCKETS)) {
        for (const it of bucket.items) {
            allItems.push({ ...it, bucket: bucketKey, storage: bucket.storage });
        }
    }

    return {
        BUCKETS,
        items: allItems,
        statuses: { lesen: {}, hoeren: {}, schreiben: {} },

        hydrate() {
            this.statuses = window.TopicStatus.all();
            window.addEventListener('topic-status-changed', () => {
                this.statuses = window.TopicStatus.all();
            });
        },

        statusOf(item) {
            return (this.statuses[item.storage] || {})[item.key] || null;
        },

        get counts() {
            const out = {};
            for (const [bucketKey, bucket] of Object.entries(this.BUCKETS)) {
                let mastered = 0, revise = 0;
                for (const it of bucket.items) {
                    const v = (this.statuses[bucket.storage] || {})[it.key];
                    if (v === 'mastered') mastered++;
                    else if (v === 'revise') revise++;
                }
                const total = bucket.items.length;
                out[bucketKey] = {
                    total, mastered, revise,
                    untouched:    total - mastered - revise,
                    masteredPct:  total ? Math.round(mastered / total * 100) : 0,
                    revisePct:    total ? Math.round(revise   / total * 100) : 0,
                };
            }
            return out;
        },

        get overall() {
            let total = 0, mastered = 0, revise = 0;
            for (const c of Object.values(this.counts)) {
                total += c.total; mastered += c.mastered; revise += c.revise;
            }
            return {
                total, mastered, revise,
                untouched:   total - mastered - revise,
                masteredPct: total ? Math.round(mastered / total * 100) : 0,
                revisePct:   total ? Math.round(revise   / total * 100) : 0,
            };
        },

        get reviseList() {
            return this.items.filter(i => this.statusOf(i) === 'revise');
        },

        get nextList() {
            return this.items
                .filter(i => !this.statusOf(i))
                .sort((a, b) => {
                    if (a.level !== b.level) return a.level === 'B2' ? -1 : 1;
                    return 0;
                })
                .slice(0, 12);
        },

        bucketLabel(b) { return this.BUCKETS[b]?.label_de || b; },
        bucketTone(b)  {
            const tone = this.BUCKETS[b]?.tone || 'slate';
            return ({
                amber:   'text-amber-400',
                orange:  'text-orange-400',
                emerald: 'text-emerald-400',
            })[tone] || 'text-slate-400';
        },
    };
}
</script>
@endpush

@endsection
