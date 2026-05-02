@extends('admin.layout')
@section('title', 'طلبات الوصول')
@section('page-title', 'طلبات الوصول')

@section('content')
@if(session('ok'))
<div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-200 text-sm" dir="rtl">
    {{ session('ok') }}
</div>
@endif

{{-- Tabs --}}
<div class="flex items-center gap-1 mb-4 p-1 rounded-xl bg-black/30 border border-white/[0.06] w-fit">
    @foreach(['pending' => 'في الانتظار', 'approved' => 'مصادق', 'denied' => 'مرفوض'] as $tabKey => $tabLabel)
    <a href="{{ route('admin.access.index', ['tab' => $tabKey]) }}"
       class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all
              {{ $tab === $tabKey ? 'bg-amber-500/20 text-amber-200 border border-amber-500/30' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
        {{ $tabLabel }}
        <span class="ml-1.5 text-[10px] text-slate-500">{{ $counts[$tabKey] }}</span>
    </a>
    @endforeach
</div>

<div class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
    @forelse($requests as $req)
    <div class="px-5 py-4 border-b border-white/[0.04] last:border-0 flex flex-wrap items-center gap-3 justify-between">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 mb-1">
                <div class="font-bold text-white">{{ $req->user->name }}</div>
                <span class="text-xs text-slate-500" dir="ltr">{{ $req->user->email }}</span>
            </div>
            <div class="flex items-center flex-wrap gap-2 text-sm">
                <span class="px-2 py-0.5 rounded-md bg-white/[0.04] border border-white/[0.06] text-slate-200">{{ $req->languageLabel() }}</span>
                <span class="px-2 py-0.5 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-300 font-bold">{{ $req->exam }}</span>
                <span class="px-2 py-0.5 rounded-md bg-orange-500/10 border border-orange-500/30 text-orange-300 font-bold">{{ $req->level }}</span>
                <span class="text-[11px] text-slate-500">{{ $req->created_at->diffForHumans() }}</span>
                @if($req->approver)
                <span class="text-[11px] text-slate-600">· بواسطة {{ $req->approver->name }}</span>
                @endif
            </div>
            @if($req->admin_note)
            <div class="text-[11px] text-slate-500 mt-1.5" dir="rtl">ملاحظة: {{ $req->admin_note }}</div>
            @endif
        </div>

        @if($req->isPending())
        <div class="flex items-center gap-2 shrink-0">
            <form method="POST" action="{{ route('admin.access.approve', $req) }}">
                @csrf
                <button class="inline-flex items-center gap-1.5 px-3 h-8 rounded-lg bg-emerald-500/20 border border-emerald-500/40 text-emerald-200 text-xs font-bold hover:bg-emerald-500/30 active:scale-95 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    صادق
                </button>
            </form>
            <form method="POST" action="{{ route('admin.access.deny', $req) }}">
                @csrf
                <button class="inline-flex items-center gap-1.5 px-3 h-8 rounded-lg bg-red-500/15 border border-red-500/40 text-red-200 text-xs font-bold hover:bg-red-500/25 active:scale-95 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    رفض
                </button>
            </form>
        </div>
        @else
        <span class="px-2 py-1 rounded-md text-[10px] font-bold border
                     {{ $req->isApproved() ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' : 'bg-red-500/15 border-red-500/30 text-red-300' }}">
            @if($req->isApproved()) مصادق @else مرفوض @endif
        </span>
        @endif
    </div>
    @empty
    <div class="px-5 py-12 text-center text-sm text-slate-500" dir="rtl">
        ماكاينش طلبات.
    </div>
    @endforelse

    @if($requests->hasPages())
    <div class="px-5 py-3 border-t border-white/[0.05]">
        {{ $requests->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
