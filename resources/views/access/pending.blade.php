@extends('layouts.app')
@section('title', 'الطلب فالانتظار | ' . config('app.name'))

@section('content')
<div class="max-w-xl mx-auto px-4 md:px-6 pt-32 pb-16" dir="rtl">
    <div class="rounded-2xl border border-amber-500/30 bg-gradient-to-br from-amber-500/[0.06] to-transparent overflow-hidden text-center p-8">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-500/20 border-2 border-amber-400 flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-300"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">طلبك فالانتظار</h1>
        <p class="text-slate-400 text-sm leading-relaxed mb-6">الأدمن غادي يراجع الطلب ديالك ويصادق عليه. غادي تشوف المحتوى بمجرد ما يدوز.</p>

        @if($pending)
        <div class="inline-block px-4 py-2 rounded-xl bg-[#111216] border border-white/[0.08] text-sm">
            <span class="text-slate-500">طلبك:</span>
            <span class="text-white font-bold">{{ $pending->languageLabel() }} · {{ $pending->exam }} · {{ $pending->level }}</span>
            <div class="text-[11px] text-slate-500 mt-1">قُدّم {{ $pending->created_at->diffForHumans() }}</div>
        </div>
        @endif

        @if($current)
        <div class="mt-6 pt-6 border-t border-white/[0.05]">
            <p class="text-xs text-slate-500 mb-2">الوصول الحالي ديالك:</p>
            <div class="inline-block px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-sm">
                <span class="text-emerald-300 font-bold">{{ $current->languageLabel() }} · {{ $current->exam }} · {{ $current->level }}</span>
            </div>
            <div class="mt-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-sm text-amber-400 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    رجوع للموقع
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
