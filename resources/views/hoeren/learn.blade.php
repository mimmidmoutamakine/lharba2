@extends('layouts.app')

@section('title', 'Hören · Teil ' . $teilNum . ' · التعلم | ' . config('app.name'))

@section('content')
<div class="max-w-5xl mx-auto px-4 md:px-6 pt-28 pb-16">

    @include('hoeren._header')

    {{-- Notice from teacher --}}
    <div class="mb-6 p-4 rounded-2xl border border-amber-500/25 bg-amber-500/[0.06] text-sm leading-relaxed text-amber-100" dir="rtl">
        <div class="flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5 text-amber-400"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <p>
                حاليا الأكواد ماصالحين لوالو إذا رجعو غانعلموكم، خدم بالجمل...
                إذا كنتي مزروب فيلتري غير اللي <span class="font-bold text-amber-300">richtig</span> وطبع وبدا تحفظهم غير هوما كل نهار باش تكون عارف غير الغيشتيش
                ونهار الإمتحان اللي جاتك جديدة ديرها <span class="font-bold text-amber-300">falsh</span>.
            </p>
        </div>
    </div>

    {{-- PDF download buttons --}}
    <div class="mb-6 p-4 rounded-2xl border border-white/[0.06] bg-[#111216]" dir="rtl">
        <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-3">تحميل PDF (Richtig فقط)</div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            @foreach(['teil1' => 'Teil 1', 'teil2' => 'Teil 2', 'teil3' => 'Teil 3'] as $tk => $label)
            <a href="{{ route('hoeren.pdf', ['teil' => $tk]) }}"
               class="inline-flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border border-white/10 bg-white/[0.02] text-sm font-bold text-slate-200 hover:text-white hover:bg-white/[0.06] hover:border-amber-500/30 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                PDF · {{ $label }}
            </a>
            @endforeach
            <a href="{{ route('hoeren.pdf.all') }}"
               class="inline-flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border border-amber-500/40 bg-amber-500/15 text-sm font-bold text-amber-200 hover:bg-amber-500/25 hover:text-white transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                PDF · الكل
            </a>
        </div>
    </div>

    {{-- Codes list --}}
    <div class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden"
         x-data="{ q: '' }">
        <div class="px-4 md:px-5 py-3 border-b border-white/[0.05] flex items-center justify-between gap-3" dir="rtl">
            <div class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">الأكواد و الجمل ({{ $codes->count() }})</div>
            <input type="search" x-model="q" placeholder="بحث (ألماني أو دارجة)..."
                   class="w-full md:max-w-xs px-3 py-1.5 rounded-lg bg-black/30 border border-white/10 text-xs text-white placeholder:text-slate-600 focus:outline-none focus:border-amber-500" dir="rtl">
        </div>

        @forelse($codes as $code)
        <div class="px-4 md:px-5 py-4 border-b border-white/[0.03] last:border-0 flex flex-wrap items-start gap-4"
             :class="(q && !{{ \Illuminate\Support\Js::from(($code->code ?? '') . ' ' . ($code->topic_title ?? '') . ' ' . ($code->story_ar ?? '')) }}.toLowerCase().includes(q.toLowerCase())) ? 'hidden' : ''">
            <div class="shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500/15 to-orange-600/15 border border-amber-500/30 flex items-center justify-center">
                <span class="text-amber-300 font-black text-base">{{ $code->code }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-white font-bold text-base leading-snug" dir="ltr">{{ $code->topic_title }}</div>
                @if($code->story_ar)
                <div class="text-sm text-slate-400 leading-relaxed mt-1.5" dir="rtl">{{ $code->story_ar }}</div>
                @else
                <div class="text-xs text-slate-600 italic mt-1.5" dir="rtl">— ما كاينش جملة دارجة لهاد الكود —</div>
                @endif
            </div>
        </div>
        @empty
        <div class="px-5 py-12 text-center text-sm text-slate-500" dir="rtl">
            ما كاينش أكواد فهاد الجزء حاليا.
        </div>
        @endforelse
    </div>

</div>
@endsection
