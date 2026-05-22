@extends('admin.layout')
@section('title', 'Hören')
@section('page-title', 'Hören · الوحدات')

@section('content')

@if(session('ok'))
<div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-200 text-sm" dir="rtl">
    {{ session('ok') }}
</div>
@endif
@if($errors->any())
<div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-200 text-sm" dir="rtl">
    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
</div>
@endif

<div class="flex items-center justify-between mb-6 gap-3" dir="rtl">
    <div class="text-xs text-slate-500">
        كل وحدة = (Level, Teil). الامتحانات داخل كل وحدة فيها صوت اختياري.
    </div>
    <a href="{{ route('admin.hoeren.import.show') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white font-bold text-sm shadow-md shadow-amber-500/30 hover:shadow-amber-500/50 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        استيراد JSON
    </a>
</div>

@forelse($modules as $module)
<div class="mb-5 rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden"
     x-data="{ open: false }">
    <button type="button" @click="open = !open"
            class="w-full px-5 py-4 flex items-center justify-between gap-4 hover:bg-white/[0.02] transition-colors" dir="rtl">
        <div class="flex items-center gap-3 min-w-0">
            <span class="shrink-0 inline-flex w-9 h-9 items-center justify-center rounded-lg bg-amber-500/15 text-amber-300 font-black text-sm">T{{ $module->teil }}</span>
            <div class="min-w-0 text-right">
                <div class="font-bold text-white">Hören · Teil {{ $module->teil }} <span class="text-slate-500 font-normal">· {{ $module->subtitle }}</span></div>
                <div class="text-xs text-slate-500">
                    <span>{{ $module->codes_count }} كود</span>
                    <span class="mx-1">·</span>
                    <span>{{ $module->exams_count }} امتحان</span>
                    @php $withAudio = $module->exams->filter(fn ($e) => (bool) $e->audio_path)->count(); @endphp
                    <span class="mx-1">·</span>
                    <span>{{ $withAudio }}/{{ $module->exams_count }} عندو صوت</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded text-[10px] font-black border {{ $module->level === 'B2' ? 'bg-orange-500/10 border-orange-500/30 text-orange-300' : 'bg-amber-500/10 border-amber-500/30 text-amber-300' }}">{{ $module->level }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" :class="open ? 'rotate-180' : ''" class="text-slate-500 transition-transform"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
    </button>

    <div x-show="open" x-cloak x-collapse>
        <div class="border-t border-white/[0.04]">
            @forelse($module->exams as $exam)
            <div class="px-5 py-3 border-b border-white/[0.03] last:border-0 flex flex-wrap items-center gap-3 justify-between"
                 x-data="{ uploadOpen: false }" dir="rtl">
                <div class="min-w-0 flex-1">
                    <div class="text-sm text-white truncate flex items-center gap-2" dir="auto">
                        <span class="truncate">{{ $exam->title }}</span>
                        {{-- Inline admin tag editor — appears on hover/click --}}
                        @include('partials.topic-tag.editor', [
                            'type'       => 'hoeren-exam',
                            'id'         => $exam->id,
                            'currentTag' => $exam->topicTag,
                        ])
                    </div>
                    <div class="text-[11px] text-slate-500 font-mono mt-0.5" dir="ltr">{{ $exam->slug }}</div>
                </div>

                @if($exam->audio_path)
                <div class="flex items-center gap-2">
                    <audio controls preload="none" class="h-8" style="width: 220px;">
                        <source src="{{ $exam->audioUrl() }}">
                    </audio>
                    <form method="POST" action="{{ route('admin.hoeren.exam.audio.delete', $exam) }}"
                          onsubmit="return confirm('تأكد حذف الصوت؟')" class="shrink-0">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-500/30 text-red-300 hover:bg-red-500/15 transition-all" title="حذف الصوت">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/></svg>
                        </button>
                    </form>
                    <button type="button" @click="uploadOpen = !uploadOpen"
                            class="shrink-0 text-[11px] text-slate-500 hover:text-white">بدّل</button>
                </div>
                @else
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-slate-600">بلا صوت</span>
                    <button type="button" @click="uploadOpen = !uploadOpen"
                            class="text-[11px] text-amber-400 hover:text-amber-300 font-bold">+ رفع</button>
                </div>
                @endif

                <div x-show="uploadOpen" x-cloak class="w-full">
                    <form method="POST" action="{{ route('admin.hoeren.exam.audio.upload', $exam) }}"
                          enctype="multipart/form-data"
                          class="flex items-center gap-2 p-2 rounded-lg bg-black/30 border border-white/[0.06]">
                        @csrf
                        <input type="file" name="audio"
                               accept="audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/x-m4a,audio/mp4,audio/aac,.mp3,.wav,.ogg,.m4a,.aac"
                               required
                               class="flex-1 text-xs text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-amber-600 file:text-white hover:file:bg-amber-500">
                        <button type="submit" class="px-3 h-9 rounded-md bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold">حفظ</button>
                        <button type="button" @click="uploadOpen = false" class="px-2 h-9 text-xs text-slate-500 hover:text-white">إلغاء</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-xs text-slate-600">ما كاينش امتحانات فهاد الوحدة.</div>
            @endforelse
        </div>
    </div>
</div>
@empty
<div class="rounded-2xl border border-white/[0.08] bg-[#111216] p-12 text-center text-sm text-slate-500" dir="rtl">
    ما كاينش وحدات. <a href="{{ route('admin.hoeren.import.show') }}" class="text-amber-400 hover:text-amber-300 font-bold">استورد ملف JSON</a> باش تبدا.
</div>
@endforelse

@endsection
