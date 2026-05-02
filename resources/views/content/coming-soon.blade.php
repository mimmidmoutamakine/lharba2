@extends('layouts.app')
@section('title', 'قريبا | ' . config('app.name'))

@section('content')
<div class="max-w-xl mx-auto px-4 md:px-6 pt-32 pb-16" dir="rtl">
    <div class="rounded-2xl border border-amber-500/30 bg-gradient-to-br from-amber-500/[0.06] to-transparent overflow-hidden text-center p-8">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-500/20 border-2 border-amber-400 flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-amber-300"><path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-4"/><circle cx="12" cy="6" r="4"/><path d="M12 10v12"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">المحتوى قريبا</h1>
        <p class="text-slate-400 text-sm leading-relaxed mb-2">
            مازال ما عندناش محتوى ل
            @if($access)
            <span class="text-amber-300 font-bold">{{ $access->languageLabel() }}</span>
            ·
            <span class="text-amber-300 font-bold">{{ $access->exam }}</span>
            ·
            <span class="text-amber-300 font-bold">{{ $access->level }}</span>
            @endif
            .
        </p>
        <p class="text-slate-500 text-xs mb-6">غادي نوصلوك بمجرد ما يكون جاهز. فهاد الوقت تقدر تبدل لاختبار آخر.</p>

        <a href="{{ route('access.create') }}" class="inline-flex items-center gap-1.5 px-5 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white text-sm font-bold hover:shadow-lg active:scale-95 transition-all">
            بدّل الاختبار
        </a>
        <div class="mt-4">
            <a href="{{ route('profile') }}" class="text-xs text-slate-500 hover:text-white">رجوع للبروفايل</a>
        </div>
    </div>
</div>
@endsection
