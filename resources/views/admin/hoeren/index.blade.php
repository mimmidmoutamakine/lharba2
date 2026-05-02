@extends('admin.layout')
@section('title', 'مواضيع Hören')
@section('page-title', 'مواضيع Hören')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="text-sm text-slate-500">{{ $topics->total() }} موضوع</div>
    <a href="{{ route('admin.import.show', 'hoeren') }}"
       class="btn-shine flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-500 text-white text-sm font-bold transition-all">
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
                <th class="text-right px-5 py-3 font-semibold">الصوت</th>
                <th class="text-right px-5 py-3 font-semibold">الحالة</th>
                <th class="text-right px-5 py-3 font-semibold">التاريخ</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($topics as $topic)
            <tr class="border-b border-white/[0.03] last:border-0 hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3.5">
                    <div class="font-medium text-white">{{ $topic->title }}</div>
                    @if($topic->title_ar)
                    <div class="text-xs text-slate-500 mt-0.5">{{ $topic->title_ar }}</div>
                    @endif
                </td>
                <td class="px-5 py-3.5">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $topic->level === 'B2' ? 'bg-orange-500/10 border-orange-500/20 text-orange-400' : 'bg-amber-500/10 border-amber-500/20 text-amber-400' }}">
                        {{ $topic->level }}
                    </span>
                </td>
                <td class="px-5 py-3.5">
                    @if($topic->audio_path)
                    <span class="flex items-center gap-1.5 text-xs text-green-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                        {{ basename($topic->audio_path) }}
                    </span>
                    @else
                    <span class="text-xs text-slate-600">لا يوجد صوت</span>
                    @endif
                </td>
                <td class="px-5 py-3.5">
                    <span class="flex items-center gap-1.5 text-xs {{ $topic->is_published ? 'text-green-400' : 'text-slate-500' }}">
                        <span class="w-2 h-2 rounded-full {{ $topic->is_published ? 'bg-green-500' : 'bg-slate-600' }}"></span>
                        {{ $topic->is_published ? 'منشور' : 'مخفي' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $topic->created_at->format('d/m/Y') }}</td>
                <td class="px-5 py-3.5">
                    <form method="POST" action="{{ route('admin.hoeren.destroy', $topic) }}"
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
                <td colspan="6" class="px-5 py-12 text-center text-slate-600">
                    <div class="text-4xl mb-3">🎧</div>
                    <div class="text-sm">لا توجد مواضيع بعد</div>
                    <a href="{{ route('admin.import.show', 'hoeren') }}" class="mt-2 inline-block text-xs text-orange-400 hover:underline">استيراد الآن</a>
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
