@extends('admin.layout')
@section('title', 'Goethe B1 — Lesen')
@section('page-title', 'Goethe B1 — مواضيع Lesen')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="text-sm text-slate-500">{{ $topics->total() }} موضوع</div>
    <a href="{{ route('admin.goethe-b1.lesen.import.show') }}"
       class="btn-shine flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        استيراد مواضيع
    </a>
</div>

<div class="rounded-2xl border border-white/[0.08] overflow-hidden bg-[#111216]">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/[0.05] text-xs text-slate-500 uppercase tracking-wider">
                <th class="text-right px-5 py-3 font-semibold">العنوان</th>
                <th class="text-right px-5 py-3 font-semibold">المستوى</th>
                <th class="text-right px-5 py-3 font-semibold">الأجزاء</th>
                <th class="text-right px-5 py-3 font-semibold">عدد الأسئلة</th>
                <th class="text-right px-5 py-3 font-semibold">الحالة</th>
                <th class="text-right px-5 py-3 font-semibold">التاريخ</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($topics as $topic)
            <tr class="border-b border-white/[0.03] last:border-0 hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3.5">
                    <div class="font-medium text-white flex items-center gap-2 flex-wrap">
                        <span>{{ $topic->title }}</span>
                        @include('partials.topic-tag.editor', [
                            'type'       => 'goethe-b1-lesen',
                            'id'         => $topic->id,
                            'currentTag' => $topic->topicTag,
                        ])
                    </div>
                    @if($topic->title_ar)
                    <div class="text-xs text-slate-500 mt-0.5">{{ $topic->title_ar }}</div>
                    @endif
                </td>
                <td class="px-5 py-3.5">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border bg-amber-500/10 border-amber-500/20 text-amber-400">{{ $topic->level }}</span>
                </td>
                <td class="px-5 py-3.5 text-slate-400">{{ $topic->parts_count }} / 5</td>
                <td class="px-5 py-3.5 text-slate-400">{{ $topic->questions_count }}</td>
                <td class="px-5 py-3.5">
                    <form method="POST" action="{{ route('admin.goethe-b1.lesen.toggle', $topic) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="flex items-center gap-1.5 text-xs font-medium transition-colors {{ $topic->is_published ? 'text-green-400 hover:text-red-400' : 'text-slate-500 hover:text-green-400' }}">
                            <span class="w-2 h-2 rounded-full {{ $topic->is_published ? 'bg-green-500' : 'bg-slate-600' }}"></span>
                            {{ $topic->is_published ? 'منشور' : 'مخفي' }}
                        </button>
                    </form>
                </td>
                <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $topic->created_at->format('d/m/Y') }}</td>
                <td class="px-5 py-3.5">
                    <form method="POST" action="{{ route('admin.goethe-b1.lesen.destroy', $topic) }}"
                          onsubmit="return confirm('هل أنت متأكد من حذف هذا الموضوع؟')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-slate-600 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-12 text-center text-slate-600">
                    <div class="text-4xl mb-3">📚</div>
                    <div class="text-sm">لا توجد مواضيع بعد</div>
                    <a href="{{ route('admin.goethe-b1.lesen.import.show') }}" class="mt-2 inline-block text-xs text-amber-400 hover:underline">استيراد الآن</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($topics->hasPages())
<div class="mt-4">{{ $topics->links() }}</div>
@endif

@endsection
