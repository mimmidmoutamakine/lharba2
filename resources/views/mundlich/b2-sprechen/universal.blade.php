@extends('layouts.app')

@section('title', 'العُدّة الشاملة · Mündlich Teil 2 | ' . config('app.name'))

@section('content')
@php
    // Friendly labels for the 5 presentation-structure blocks.
    $structLabels = [
        'presentation'      => '١ · تقديم/تلخيص النص',
        'vorteile_nachteile'=> '٢ · الإيجابيات والسلبيات',
        'meinung'           => '٣ · الرأي (Meinung)',
        'erfahrung'         => '٤ · التجربة (Erfahrung)',
        'abschluss'         => '٥ · الخاتمة (Abschluss)',
    ];
    $emergencyLabels = [
        'balanced_opinion' => 'رأي متوازن جاهز',
        'no_experience'    => 'ملكش تجربة؟ استعمل هادي',
        'society_transfer' => 'الربط ببلدك/المجتمع',
        'safe_conclusion'  => 'خاتمة آمنة',
    ];
@endphp

<div class="max-w-5xl mx-auto px-4 md:px-6 pt-28 pb-16" dir="rtl"
     x-data="{ open: null }">

    {{-- Breadcrumb / header --}}
    <a href="{{ route('mundlich.b2-sprechen.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition-colors mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        رجوع لـ Mündlich Teil 2
    </a>

    <div class="mb-8">
        <div class="inline-flex items-center gap-2 text-amber-400 font-bold mb-2">
            <span class="text-xs font-black uppercase tracking-widest">الطبقة 1 · Universal-Baukasten</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">العُدّة الشاملة</h1>
        @if(!empty($meta['philosophy']))
        <p class="text-slate-400 max-w-3xl leading-relaxed">{{ $meta['philosophy'] }}</p>
        @endif
    </div>

    {{-- ── Argument categories ─────────────────────────────────── --}}
    <section class="mb-12">
        <h2 class="text-lg font-bold text-white mb-1">الحجج الشاملة <span class="text-slate-500 text-sm font-normal">({{ count($categories) }})</span></h2>
        <p class="text-sm text-slate-500 mb-4">كل صنف فيه جمل جاهزة + مفردات. إلا نسيتي تفاصيل النص، تقدر تهضر بهاد الحجج العامة.</p>

        <div class="space-y-2.5">
            @foreach($categories as $i => $cat)
            <div class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
                <button type="button" @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                        class="w-full px-5 py-4 flex items-center justify-between gap-3 hover:bg-white/[0.02] transition-colors">
                    <div class="flex items-center gap-3 text-right">
                        <span class="shrink-0 w-9 h-9 rounded-lg bg-amber-500/15 text-amber-300 flex items-center justify-center font-black text-xs">{{ $i + 1 }}</span>
                        <span class="font-bold text-white">{{ $cat['title'] ?? $cat['id'] }}</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" :class="open === {{ $i }} ? 'rotate-180' : ''" class="text-slate-500 transition-transform shrink-0"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="open === {{ $i }}" x-collapse x-cloak>
                    <div class="px-5 pb-5 pt-1 border-t border-white/[0.04] space-y-4">
                        @if(!empty($cat['core_sentences']))
                        <div>
                            <div class="text-[11px] font-black uppercase tracking-widest text-amber-400/70 mb-2">جمل جاهزة</div>
                            <ul class="space-y-1.5">
                                @foreach($cat['core_sentences'] as $s)
                                <li class="flex gap-2 text-sm text-slate-300 leading-relaxed" dir="ltr">
                                    <span class="text-amber-500/60 shrink-0">▸</span><span>{{ $s }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        @if(!empty($cat['vocabulary_chips']))
                        <div>
                            <div class="text-[11px] font-black uppercase tracking-widest text-amber-400/70 mb-2">مفردات (Chips)</div>
                            <div class="flex flex-wrap gap-1.5" dir="ltr">
                                @foreach($cat['vocabulary_chips'] as $chip)
                                <span class="px-2 py-1 rounded-lg text-xs font-medium bg-amber-500/10 text-amber-200 border border-amber-500/20">{{ $chip }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @if(!empty($cat['example']))
                        <div class="rounded-xl bg-black/30 border border-white/[0.05] p-3" dir="ltr">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Beispiel</div>
                            <p class="text-sm text-slate-300 leading-relaxed italic">{{ $cat['example'] }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ── Presentation structures ─────────────────────────────── --}}
    @if(!empty($structures))
    <section class="mb-12">
        <h2 class="text-lg font-bold text-white mb-1">بنيات العرض</h2>
        <p class="text-sm text-slate-500 mb-4">الهيكل ديال العرض من البداية حتى الخاتمة — حفظ الجمل وبدّل الكلمات.</p>

        <div class="space-y-3">
            @foreach($structures as $key => $block)
            <div class="rounded-2xl border border-white/[0.08] bg-[#111216] p-5">
                <div class="text-sm font-black text-amber-300 mb-3">{{ $structLabels[$key] ?? ($block['title'] ?? $key) }}</div>

                @php
                    $patterns = $block['pattern'] ?? $block['patterns'] ?? [];
                    $fixed    = $block['fixed_structure'] ?? null;
                    // fixed_structure may be a string, an array of strings, or { rule, slots:[{template}] }.
                    $fixedRule  = is_array($fixed) ? ($fixed['rule'] ?? null) : (is_string($fixed) ? $fixed : null);
                    $fixedSlots = [];
                    if (is_array($fixed)) {
                        if (!empty($fixed['slots']) && is_array($fixed['slots'])) {
                            $fixedSlots = array_map(fn ($s) => is_array($s) ? ($s['template'] ?? '') : (string) $s, $fixed['slots']);
                        } elseif (!isset($fixed['rule'])) {
                            $fixedSlots = array_map(fn ($s) => is_array($s) ? json_encode($s) : (string) $s, $fixed);
                        }
                        $fixedSlots = array_values(array_filter($fixedSlots));
                    }
                @endphp
                @if($fixedRule || $fixedSlots)
                <div class="mb-3 rounded-xl bg-amber-500/[0.06] border border-amber-500/20 p-3" dir="ltr">
                    <div class="text-[10px] font-black uppercase tracking-widest text-amber-400/70 mb-1">Feste Struktur</div>
                    @if($fixedRule)
                    <p class="text-xs text-amber-200/80 leading-relaxed mb-2">{{ $fixedRule }}</p>
                    @endif
                    @if($fixedSlots)
                    <ol class="space-y-1 list-decimal list-inside">
                        @foreach($fixedSlots as $slot)
                        <li class="text-sm text-slate-200 leading-relaxed">{{ $slot }}</li>
                        @endforeach
                    </ol>
                    @endif
                </div>
                @endif
                @if(!empty($patterns))
                <ul class="space-y-1.5 mb-3" dir="ltr">
                    @foreach((array) $patterns as $p)
                    <li class="flex gap-2 text-sm text-slate-300 leading-relaxed"><span class="text-amber-500/60 shrink-0">▸</span><span>{{ $p }}</span></li>
                    @endforeach
                </ul>
                @endif
                @if(!empty($block['example']))
                <div class="rounded-xl bg-black/30 border border-white/[0.05] p-3" dir="ltr">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Beispiel</div>
                    <p class="text-sm text-slate-300 leading-relaxed italic">{{ $block['example'] }}</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ── Emergency blocks ────────────────────────────────────── --}}
    @if(!empty($emergency))
    <section>
        <h2 class="text-lg font-bold text-white mb-1">بلوكات الطوارئ 🆘</h2>
        <p class="text-sm text-slate-500 mb-4">إلا توقّفتي أو نسيتي، استعمل واحد من هاد الأجوبة الجاهزة — صالحة لأي موضوع تقريباً.</p>
        <div class="grid sm:grid-cols-2 gap-3">
            @foreach($emergency as $key => $text)
            <div class="rounded-2xl border border-rose-500/20 bg-rose-500/[0.04] p-4">
                <div class="text-xs font-black text-rose-300 mb-2">{{ $emergencyLabels[$key] ?? $key }}</div>
                <p class="text-sm text-slate-300 leading-relaxed" dir="ltr">{{ $text }}</p>
            </div>
            @endforeach
        </div>
    </section>
    @endif

</div>

@endsection
