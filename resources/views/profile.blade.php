@extends('layouts.app')
@section('title', 'بروفايلي | ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto px-4 md:px-6 pt-28 pb-16" dir="rtl">

    {{-- User card --}}
    <div class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden mb-4">
        <div class="px-6 py-5 flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-lg font-black">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-white truncate">{{ $user->name }}</h1>
                    @if($user->is_admin)
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-500/20 border border-amber-500/40 text-amber-200">ADMIN</span>
                    @endif
                </div>
                <div class="text-sm text-slate-500" dir="ltr">{{ $user->email }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-xs text-slate-500 hover:text-red-300 transition-colors">خروج</button>
            </form>
        </div>
    </div>

    {{-- Current access --}}
    <div class="rounded-2xl border border-emerald-500/20 bg-gradient-to-br from-emerald-500/[0.04] to-transparent overflow-hidden mb-4">
        <div class="px-5 py-3 border-b border-emerald-500/[0.12] flex items-center justify-between">
            <span class="inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="text-emerald-400"><polyline points="20 6 9 17 4 12"/></svg>
                <span class="text-[10px] font-black uppercase tracking-[0.25em] text-emerald-400">الوصول الحالي</span>
            </span>
        </div>
        <div class="px-5 py-4">
            @if($current)
            <div class="flex items-center flex-wrap gap-2 mb-1.5">
                <span class="px-2.5 py-1 rounded-md bg-white/[0.04] border border-white/[0.06] text-slate-200 font-bold text-sm">{{ $current->languageLabel() }}</span>
                <span class="px-2.5 py-1 rounded-md bg-amber-500/10 border border-amber-500/30 text-amber-300 font-bold text-sm">{{ $current->exam }}</span>
                <span class="px-2.5 py-1 rounded-md bg-orange-500/10 border border-orange-500/30 text-orange-300 font-bold text-sm">{{ $current->level }}</span>
            </div>
            <div class="text-[11px] text-slate-500">مصادق {{ $current->decided_at?->diffForHumans() }} @if($current->approver)بواسطة {{ $current->approver->name }}@endif</div>
            @else
            <div class="text-sm text-slate-500">ما عندك حتى وصول مصادق عليه.</div>
            @endif
        </div>
    </div>

    {{-- Change exam --}}
    <div class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden mb-4">
        <div class="px-5 py-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-white">تبديل الاختبار</h2>
                @if($pending)
                <p class="text-xs text-amber-300/80 mt-1">عندك طلب فالانتظار: {{ $pending->languageLabel() }} · {{ $pending->exam }} · {{ $pending->level }}</p>
                @else
                <p class="text-xs text-slate-500 mt-1">قدم طلب جديد إلى بغيتي تبدل لاختبار آخر</p>
                @endif
            </div>
            @if(!$pending)
            <a href="{{ route('access.create') }}" class="px-4 h-9 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white text-sm font-bold inline-flex items-center hover:shadow-lg active:scale-95 transition-all">طلب جديد</a>
            @else
            <a href="{{ route('access.pending') }}" class="px-4 h-9 rounded-xl border border-amber-500/40 bg-amber-500/10 text-amber-200 text-sm font-bold inline-flex items-center">شوف الطلب</a>
            @endif
        </div>
    </div>

    {{-- History --}}
    @if($history->count())
    <div class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
        <div class="px-5 py-3 border-b border-white/[0.05]">
            <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">تاريخ الطلبات</span>
        </div>
        <div class="divide-y divide-white/[0.04]">
            @foreach($history as $req)
            <div class="px-5 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center flex-wrap gap-2 text-xs">
                    <span class="text-slate-400">{{ $req->languageLabel() }}</span>
                    <span class="text-slate-600">·</span>
                    <span class="text-slate-300 font-bold">{{ $req->exam }}</span>
                    <span class="text-slate-600">·</span>
                    <span class="text-slate-300 font-bold">{{ $req->level }}</span>
                    <span class="text-slate-600">·</span>
                    <span class="text-slate-500">{{ $req->created_at->diffForHumans() }}</span>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold border
                             {{ $req->isApproved() ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' :
                                ($req->isDenied() ? 'bg-red-500/15 border-red-500/30 text-red-300' : 'bg-amber-500/15 border-amber-500/30 text-amber-300') }}">
                    @if($req->isApproved()) مصادق @elseif($req->isDenied()) مرفوض @else في الانتظار @endif
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
