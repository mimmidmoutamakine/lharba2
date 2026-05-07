@extends('admin.layout')
@section('title', 'Dashboard')
@section('page-title', 'لوحة التحكم')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $stats = [
            ['label' => 'مواضيع Lesen (Telc)', 'value' => $lesenCount,         'color' => 'amber',  'href' => route('admin.lesen.index')],
            ['label' => 'مواضيع Hören',        'value' => $hoerenCount,        'color' => 'orange', 'href' => route('admin.hoeren.index')],
            ['label' => 'Goethe B1 Lesen',     'value' => $goetheB1LesenCount, 'color' => 'amber',  'href' => route('admin.goethe-b1.lesen.index')],
        ];
    @endphp
    @foreach($stats as $stat)
    <a href="{{ $stat['href'] }}"
       class="p-5 rounded-2xl border bg-[#111216] border-white/[0.08] hover:border-{{ $stat['color'] }}-500/30 transition-all group">
        <div class="text-3xl font-bold text-white mb-1">{{ $stat['value'] }}</div>
        <div class="text-sm text-slate-500 group-hover:text-slate-400 transition-colors">{{ $stat['label'] }}</div>
    </a>
    @endforeach

    <a href="{{ route('admin.import.show', 'lesen') }}"
       class="p-5 rounded-2xl border bg-amber-600/10 border-amber-500/20 hover:bg-amber-600/20 transition-all col-span-2 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
        </div>
        <div>
            <div class="font-bold text-white text-sm">استيراد مواضيع</div>
            <div class="text-xs text-amber-300/70 mt-0.5">JSON · Excel · CSV</div>
        </div>
    </a>
</div>

{{-- Recent Topics --}}
<div class="grid lg:grid-cols-2 gap-6">
    <div class="rounded-2xl border bg-[#111216] border-white/[0.08] overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.05] flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">آخر مواضيع Lesen</h3>
            <a href="{{ route('admin.lesen.index') }}" class="text-xs text-amber-400 hover:underline">عرض الكل</a>
        </div>
        @forelse($lesenRecent as $topic)
        <div class="px-5 py-3 border-b border-white/[0.03] last:border-0 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="text-sm text-white truncate">{{ $topic->title }}</div>
                <div class="text-xs text-slate-500">{{ $topic->category }} · {{ $topic->created_at->diffForHumans() }}</div>
            </div>
            <span class="shrink-0 px-2 py-0.5 rounded text-[10px] font-bold border {{ $topic->level === 'B2' ? 'bg-orange-500/10 border-orange-500/20 text-orange-400' : 'bg-amber-500/10 border-amber-500/20 text-amber-400' }}">
                {{ $topic->level }}
            </span>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-sm text-slate-600">لا توجد مواضيع بعد</div>
        @endforelse
    </div>

    <div class="rounded-2xl border bg-[#111216] border-white/[0.08] overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.05] flex items-center justify-between">
            <h3 class="font-bold text-white text-sm">آخر مواضيع Hören</h3>
            <a href="{{ route('admin.hoeren.index') }}" class="text-xs text-amber-400 hover:underline">عرض الكل</a>
        </div>
        @forelse($hoerenRecent as $topic)
        <div class="px-5 py-3 border-b border-white/[0.03] last:border-0 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="text-sm text-white truncate">{{ $topic->title }}</div>
                <div class="text-xs text-slate-500">{{ $topic->category }} · {{ $topic->created_at->diffForHumans() }}</div>
            </div>
            <span class="shrink-0 px-2 py-0.5 rounded text-[10px] font-bold border {{ $topic->level === 'B2' ? 'bg-orange-500/10 border-orange-500/20 text-orange-400' : 'bg-amber-500/10 border-amber-500/20 text-amber-400' }}">
                {{ $topic->level }}
            </span>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-sm text-slate-600">لا توجد مواضيع بعد</div>
        @endforelse
    </div>
</div>

@endsection
