{{-- Inline admin editor for a TopicTag. Drop into any admin row.

     Required vars:
       $type        — slug ('lesen' | 'hoeren-exam' | 'goethe-b1-lesen')
       $id          — primary key of the target row
       $currentTag  — TopicTag|null  (the row's existing tag, if any)
--}}
@props(['type', 'id', 'currentTag' => null])

<div x-data="{ open: false, sel: @js($currentTag?->tag ?? '') }"
     class="relative inline-block" dir="rtl">

    {{-- Trigger button: shows current tag state at a glance --}}
    <button type="button" @click="open = !open"
            @class([
                'inline-flex items-center gap-1 h-7 px-2 rounded-md border text-[10px] font-bold transition-all active:scale-95',
                'bg-emerald-500/15 border-emerald-400/50 text-emerald-200' => $currentTag?->tag === 'new',
                'bg-amber-500/15 border-amber-400/50 text-amber-200'       => $currentTag?->tag === 'rare',
                'bg-red-500/15 border-red-400/50 text-red-200'             => $currentTag?->tag === 'discontinued',
                'bg-sky-500/15 border-sky-400/50 text-sky-200'             => $currentTag?->tag === 'note',
                'border-white/[0.06] text-slate-500 hover:text-white hover:border-white/20' => ! $currentTag,
            ])
            :title="open ? 'إغلاق' : 'إشارة'">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        <span>{{ $currentTag?->label() ?? 'إشارة' }}</span>
    </button>

    {{-- Popover form --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         @click.outside="open = false"
         @keydown.escape.window="open = false"
         class="absolute top-full mt-1 right-0 w-72 z-30 bg-[#0B0C10] border border-white/10 rounded-xl shadow-2xl shadow-black/60 overflow-hidden">

        <form method="POST" action="{{ route('admin.topic-tags.set', ['type' => $type, 'id' => $id]) }}"
              class="p-3 space-y-2.5">
            @csrf

            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-1">نوع الإشارة</div>
            <div class="space-y-1">
                @php
                    $opts = [
                        'new'          => ['label' => 'جديد',              'tone' => 'emerald', 'desc' => 'موضوع جديد على البلاتفورم'],
                        'rare'         => ['label' => 'نادر فاش كيتحط',     'tone' => 'amber',   'desc' => 'كيخرج فالامتحان قليل'],
                        'discontinued' => ['label' => 'ما بقاش كيتحط',     'tone' => 'red',     'desc' => 'ما عاد كيخرج (Telc قديم)'],
                        'note'         => ['label' => 'ملاحظة خاصة',       'tone' => 'sky',     'desc' => 'فقط ملاحظة من الأستاذ'],
                    ];
                @endphp
                @foreach($opts as $val => $meta)
                <label class="flex items-start gap-2 p-1.5 rounded-md cursor-pointer hover:bg-white/[0.04] transition-colors"
                       :class="sel === '{{ $val }}' ? 'bg-{{ $meta['tone'] }}-500/10' : ''">
                    <input type="radio" name="tag" value="{{ $val }}" x-model="sel"
                           class="mt-0.5 accent-{{ $meta['tone'] }}-500" required>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-{{ $meta['tone'] }}-300">{{ $meta['label'] }}</div>
                        <div class="text-[10px] text-slate-500 leading-tight">{{ $meta['desc'] }}</div>
                    </div>
                </label>
                @endforeach
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-1">ملاحظة (اختياري)</label>
                <textarea name="note" rows="2" maxlength="500"
                          placeholder="نص يظهر للطالب فاش يكليكي على الإشارة..."
                          class="w-full px-2 py-1.5 rounded-md bg-black/40 border border-white/10 text-xs text-white placeholder:text-slate-600 focus:outline-none focus:border-amber-500 resize-none">{{ $currentTag?->note }}</textarea>
            </div>

            <div class="flex items-center gap-1 justify-end pt-1">
                <button type="button" @click="open = false"
                        class="px-2.5 py-1 rounded-md text-[11px] text-slate-400 hover:text-white transition-colors">
                    إلغاء
                </button>
                <button type="submit"
                        :disabled="!sel"
                        class="px-3 py-1 rounded-md bg-amber-600 hover:bg-amber-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-[11px] font-bold transition-all">
                    حفظ
                </button>
            </div>
        </form>

        @if($currentTag)
        {{-- Separate DELETE form so it stays a real Laravel DELETE request. --}}
        <form method="POST" action="{{ route('admin.topic-tags.clear', ['type' => $type, 'id' => $id]) }}"
              class="px-3 pb-3"
              onsubmit="return confirm('تأكد مسح الإشارة؟')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="w-full px-2 py-1.5 rounded-md border border-red-500/30 text-red-300 hover:bg-red-500/10 text-[11px] font-bold transition-all">
                مسح الإشارة
            </button>
        </form>
        @endif
    </div>
</div>
