@extends('admin.layout')
@section('title', 'استيراد Hören')
@section('page-title', 'استيراد Hören')

@section('content')
@php $result = session('import_result'); @endphp

@if($result)
    @if(empty($result['errors']))
        <div class="mb-6 p-4 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-200 text-sm" dir="rtl">
            <div class="font-bold mb-2">تم الاستيراد بنجاح ✓</div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-center">
                <div><div class="text-2xl font-black text-white">{{ $result['modules'] }}</div><div class="text-[11px] text-emerald-300">وحدة (Module)</div></div>
                <div><div class="text-2xl font-black text-white">{{ $result['codes'] }}</div><div class="text-[11px] text-emerald-300">رمز (Code)</div></div>
                <div><div class="text-2xl font-black text-white">{{ $result['exams'] }}</div><div class="text-[11px] text-emerald-300">امتحان</div></div>
                <div><div class="text-2xl font-black text-white">{{ $result['statements'] }}</div><div class="text-[11px] text-emerald-300">جملة R/F</div></div>
                <div><div class="text-2xl font-black text-white">{{ $result['skipped'] }}</div><div class="text-[11px] text-emerald-300">متخطّى</div></div>
            </div>
        </div>
    @else
        <div class="mb-6 p-4 rounded-2xl border border-red-500/40 bg-red-500/10 text-red-200 text-sm" dir="rtl">
            <div class="font-bold mb-2">صاب مشاكل ف الاستيراد:</div>
            <ul class="list-disc pr-5 space-y-1 text-xs">
                @foreach(array_slice($result['errors'], 0, 20) as $err)
                    <li dir="ltr" class="text-left">{{ $err }}</li>
                @endforeach
                @if(count($result['errors']) > 20)
                    <li class="opacity-70">... و {{ count($result['errors']) - 20 }} أخرى</li>
                @endif
            </ul>
        </div>
    @endif
@endif

<form method="POST" action="{{ route('admin.hoeren.import.handle') }}" enctype="multipart/form-data"
      x-data="{ source: 'json_file' }"
      class="rounded-2xl border border-white/[0.08] bg-[#111216] p-5 md:p-6 space-y-5" dir="rtl">
    @csrf

    <div>
        <h2 class="font-bold text-white text-lg mb-1">استيراد ملف Hören</h2>
        <p class="text-xs text-slate-500">
            خذ ملف JSON ديال Hören (بالشكل ديال <code class="text-amber-300 font-mono">hoeren_modules_live_*.json</code>) وحطه هنا.
            الاستيراد آمن: كاتعاود تشغيله بدون ما تخسر الملفات الصوتية المرفوعة.
        </p>
    </div>

    {{-- Source selector --}}
    <div class="grid grid-cols-2 gap-2 p-1 rounded-xl bg-black/30 border border-white/10">
        <button type="button" @click="source = 'json_file'"
                :class="source === 'json_file' ? 'bg-amber-600 text-white' : 'text-slate-400 hover:text-white'"
                class="px-3 py-2 rounded-lg text-sm font-bold transition-all">رفع ملف</button>
        <button type="button" @click="source = 'json_text'"
                :class="source === 'json_text' ? 'bg-amber-600 text-white' : 'text-slate-400 hover:text-white'"
                class="px-3 py-2 rounded-lg text-sm font-bold transition-all">لصق JSON</button>
    </div>
    <input type="hidden" name="source" x-model="source">

    {{-- File input --}}
    <div x-show="source === 'json_file'">
        <label class="block text-xs font-bold text-slate-300 mb-2">ملف JSON (حتى 20 MB)</label>
        <input type="file" name="file" accept="application/json,.json"
               class="block w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-amber-600 file:text-white hover:file:bg-amber-500">
    </div>

    {{-- Text area --}}
    <div x-show="source === 'json_text'" x-cloak>
        <label class="block text-xs font-bold text-slate-300 mb-2">لصق JSON</label>
        <textarea name="json_text" rows="14"
                  class="w-full px-3 py-2 rounded-xl bg-black/40 border border-white/10 text-white text-xs font-mono placeholder:text-slate-600 focus:outline-none focus:border-amber-500"
                  placeholder='{"entries": [...]}'
                  dir="ltr"></textarea>
    </div>

    @if($errors->any())
    <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-200 text-xs">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
    @endif

    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('admin.hoeren.index') }}" class="text-xs text-slate-500 hover:text-white">← رجوع للوحدات</a>
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white font-bold text-sm shadow-md shadow-amber-500/30 hover:shadow-amber-500/50 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            استورد
        </button>
    </div>
</form>

<div class="mt-6 p-4 rounded-xl border border-blue-500/20 bg-blue-500/[0.04] text-xs text-slate-400" dir="rtl">
    <div class="font-bold text-blue-300 mb-1">معلومات تقنية:</div>
    <ul class="space-y-1 list-disc pr-5">
        <li>الاستيراد كاياخد كل ال<code class="font-mono text-amber-300">entries[]</code> ديال JSON و كايقسمهم على (level, teil).</li>
        <li>كل entry فيها <code class="font-mono text-amber-300">category: "codes"</code> أو <code class="font-mono text-amber-300">null</code> كاتولّي rows ف جدول الأكواد.</li>
        <li>كل entry فيها <code class="font-mono text-amber-300">category: "situations"</code> كاتولّي امتحانات (group by <code class="font-mono text-amber-300">groupTitle</code>) + جمل R/F.</li>
        <li>الملفات الصوتية المرفوعة كاتبقى محفوظة بـ slug ديال الامتحان — ما كاتمحاش حتى ملي كاتعاود الاستيراد.</li>
    </ul>
</div>
@endsection
