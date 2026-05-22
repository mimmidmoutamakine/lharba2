{{-- User-facing tag badge for a topic card.
     Pass: $tag = TopicTag|null
     Behaviour:
       - null    → renders nothing
       - 'new'   → green chip "جديد", no popover (unless admin set a note too)
       - others  → coloured chip; click toggles popover with label + note text
--}}
@props(['tag' => null])

@if($tag)
    @php
        // Static class maps — Tailwind JIT needs full literal class names in the
        // source, so we can't string-interpolate the tone. One map per visual slot.
        $chipClasses = [
            'new'          => 'bg-emerald-500/15 border-emerald-400/50 text-emerald-300',
            'rare'         => 'bg-amber-500/15 border-amber-400/50 text-amber-300',
            'discontinued' => 'bg-red-500/15 border-red-400/50 text-red-300',
            'note'         => 'bg-sky-500/15 border-sky-400/50 text-sky-300',
        ];
        $ringClasses = [
            'new'          => 'ring-1 ring-emerald-400/60',
            'rare'         => 'ring-1 ring-amber-400/60',
            'discontinued' => 'ring-1 ring-red-400/60',
            'note'         => 'ring-1 ring-sky-400/60',
        ];
        $popClasses = [
            'new'          => 'border-emerald-500/30',
            'rare'         => 'border-amber-500/30',
            'discontinued' => 'border-red-500/30',
            'note'         => 'border-sky-500/30',
        ];
        $headClasses = [
            'new'          => 'text-emerald-300',
            'rare'         => 'text-amber-300',
            'discontinued' => 'text-red-300',
            'note'         => 'text-sky-300',
        ];

        $tagKey  = $tag->tag;
        $label   = $tag->label();
        $note    = trim((string) $tag->note);
        $hasPop  = $tagKey !== 'new' || $note !== '';

        $chip    = $chipClasses[$tagKey] ?? $chipClasses['note'];
        $ring    = $ringClasses[$tagKey] ?? $ringClasses['note'];
        $popBdr  = $popClasses[$tagKey]  ?? $popClasses['note'];
        $headTxt = $headClasses[$tagKey] ?? $headClasses['note'];
    @endphp

    <div class="relative inline-block"
         @if($hasPop)
            x-data="{ open: false }"
            @click.outside="open = false"
            @keydown.escape.window="open = false"
         @endif
         dir="rtl">
        <button type="button"
                @if($hasPop)
                    @click.prevent.stop="open = !open"
                @else
                    disabled
                @endif
                @class([
                    'inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-black border transition-all',
                    'cursor-pointer hover:brightness-110' => $hasPop,
                    $chip,
                ])
                @if($hasPop) :class="open ? '{{ $ring }}' : ''" @endif>
            <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/></svg>
            <span>{{ $label }}</span>
        </button>

        @if($hasPop)
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             @class([
                'absolute top-full mt-1 right-0 z-20 w-64 p-3 rounded-xl bg-[#0B0C10] border shadow-xl shadow-black/60 text-right',
                $popBdr,
             ])>
            <div class="text-xs font-bold mb-1 {{ $headTxt }}">{{ $label }}</div>
            @if($note !== '')
                <div class="text-[11.5px] text-slate-300 leading-relaxed whitespace-pre-line">{{ $note }}</div>
            @else
                <div class="text-[11px] text-slate-500 italic">ما كاينش ملاحظة إضافية.</div>
            @endif
        </div>
        @endif
    </div>
@endif
