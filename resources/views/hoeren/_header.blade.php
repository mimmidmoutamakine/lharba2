{{-- Shared Hören header: title + Teil tabs + Learn/Imtihanat section switch.
     Expects: $teilKey ('teil1'|...), $teilNum, $level, $module, $section ('learn'|'imtihanat'). --}}
<div class="mb-6" dir="rtl">
    <div class="inline-flex items-center gap-2 text-amber-400 font-bold mb-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
        </svg>
        <span>Hören – الإستماع</span>
        <span class="px-1.5 py-0.5 rounded text-[10px] font-black border {{ $level === 'B2' ? 'bg-orange-500/10 border-orange-500/30 text-orange-300' : 'bg-amber-500/10 border-amber-500/30 text-amber-300' }}">{{ $level }}</span>
    </div>
    <h1 class="text-2xl md:text-3xl font-bold text-white">
        Teil {{ $teilNum }}
        @if($module?->subtitle)<span class="text-slate-500 font-normal">· {{ $module->subtitle }}</span>@endif
    </h1>
</div>

{{-- Teil tabs (1 / 2 / 3) --}}
<div class="grid grid-cols-3 md:flex md:items-center gap-0.5 p-1 rounded-2xl bg-black/30 border border-white/[0.06] shadow-inner shadow-black/30 mb-3" dir="rtl">
    @foreach(['teil1' => 'Teil 1', 'teil2' => 'Teil 2', 'teil3' => 'Teil 3'] as $tk => $label)
    @php $isActive = $teilKey === $tk; @endphp
    <a href="{{ route('hoeren.' . $section, ['teil' => $tk]) }}"
       @class([
            'relative z-10 px-3 md:px-5 py-1.5 md:py-2 rounded-xl text-sm font-bold whitespace-nowrap transition-all duration-200 text-center',
            'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-md shadow-amber-500/30' => $isActive,
            'text-slate-400 hover:text-white' => ! $isActive,
       ])>{{ $label }}</a>
    @endforeach
</div>

{{-- Section switch: Learn / Imtihanat --}}
<div class="grid grid-cols-2 gap-0.5 p-1 rounded-2xl bg-black/30 border border-white/[0.06] shadow-inner shadow-black/30 mb-6" dir="rtl">
    <a href="{{ route('hoeren.learn', ['teil' => $teilKey]) }}"
       @class([
            'px-3 py-2 rounded-xl text-sm font-bold transition-all duration-200 text-center inline-flex items-center justify-center gap-2',
            'bg-white/10 text-white' => $section === 'learn',
            'text-slate-400 hover:text-white' => $section !== 'learn',
       ])>
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7v14M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
        التعلم (Codes)
    </a>
    <a href="{{ route('hoeren.imtihanat', ['teil' => $teilKey]) }}"
       @class([
            'px-3 py-2 rounded-xl text-sm font-bold transition-all duration-200 text-center inline-flex items-center justify-center gap-2',
            'bg-white/10 text-white' => $section === 'imtihanat',
            'text-slate-400 hover:text-white' => $section !== 'imtihanat',
       ])>
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        الإمتحانات
    </a>
</div>
