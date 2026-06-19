@extends('admin.layout')
@section('title', 'Telc B2 Mündlich · Teil 2')
@section('page-title', 'Telc B2 Mündlich · Teil 2 (Präsentation)')

@section('content')

@if(session('ok'))
<div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-200 text-sm" dir="rtl">{{ session('ok') }}</div>
@endif

<div class="flex items-center justify-between mb-6 gap-3 flex-wrap">
    <div class="flex items-center gap-3 text-sm flex-wrap">
        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $universalRow && !empty($universalRow->argumentCategories()) ? 'bg-green-500/15 text-green-400 border border-green-500/30' : 'bg-red-500/15 text-red-400 border border-red-500/30' }}">
            {{ $universalRow && !empty($universalRow->argumentCategories()) ? 'Universal ✓' : 'Universal ✗' }}
        </span>
        <span class="text-slate-500">{{ $clusters->count() }} عائلة</span>
        <span class="text-slate-500">·</span>
        <span class="text-slate-500">{{ $topics->total() }} موضوع</span>
    </div>
    <a href="{{ route('admin.mundlich.b2-sprechen.import.show') }}"
       class="btn-shine flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        استيراد JSON
    </a>
</div>

@if(! $universalRow || empty($universalRow->argumentCategories()))
<div class="mb-6 p-4 rounded-2xl border border-amber-500/30 bg-amber-500/5">
    <div class="text-sm text-amber-300 mb-1 font-bold">⚠ الملف الشامل (Universal) لم يُحمَّل بعد</div>
    <div class="text-xs text-slate-400">ارفع <code dir="ltr" class="text-amber-400">sprechen_teil2_universal.json</code> أولاً باش تظهر العُدّة الشاملة والحجج المرتبطة فالعائلات.</div>
</div>
@endif

{{-- Clusters --}}
<h2 class="text-sm font-bold text-white mb-2">العائلات (Clusters)</h2>
<div class="rounded-2xl border border-white/[0.08] overflow-hidden bg-[#111216] mb-8">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/[0.05] text-xs text-slate-500 uppercase tracking-wider">
                <th class="text-right px-5 py-3 font-semibold">العائلة</th>
                <th class="text-right px-5 py-3 font-semibold">key</th>
                <th class="text-right px-5 py-3 font-semibold">المواضيع</th>
                <th class="text-right px-5 py-3 font-semibold">الحجج الشاملة</th>
                <th class="text-right px-5 py-3 font-semibold">الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clusters as $cluster)
            <tr class="border-b border-white/[0.03] last:border-0 hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3 font-medium text-white">{{ $cluster->title }}</td>
                <td class="px-5 py-3"><code class="text-[11px] text-slate-400" dir="ltr">{{ $cluster->cluster_key }}</code></td>
                <td class="px-5 py-3 text-slate-400">{{ count($cluster->topic_orders ?? []) }}</td>
                <td class="px-5 py-3 text-slate-400" dir="ltr">{{ implode(', ', $cluster->universal_argument_ids ?? []) }}</td>
                <td class="px-5 py-3">
                    <form method="POST" action="{{ route('admin.mundlich.b2-sprechen.cluster.toggle', $cluster) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="flex items-center gap-1.5 text-xs font-medium transition-colors {{ $cluster->is_published ? 'text-green-400 hover:text-red-400' : 'text-slate-500 hover:text-green-400' }}">
                            <span class="w-2 h-2 rounded-full {{ $cluster->is_published ? 'bg-green-500' : 'bg-slate-600' }}"></span>
                            {{ $cluster->is_published ? 'منشور' : 'مخفي' }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-600 text-sm">ما كاينش عائلات. استورد <code dir="ltr">sprechen_teil2_clusters.json</code>.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Topics --}}
<h2 class="text-sm font-bold text-white mb-2">المواضيع (Topics)</h2>
<div class="rounded-2xl border border-white/[0.08] overflow-hidden bg-[#111216]">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/[0.05] text-xs text-slate-500 uppercase tracking-wider">
                <th class="text-right px-5 py-3 font-semibold">#</th>
                <th class="text-right px-5 py-3 font-semibold">الموضوع</th>
                <th class="text-right px-5 py-3 font-semibold">العائلات</th>
                <th class="text-right px-5 py-3 font-semibold">الحجج</th>
                <th class="text-right px-5 py-3 font-semibold">الحالة</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($topics as $topic)
            <tr class="border-b border-white/[0.03] last:border-0 hover:bg-white/[0.02] transition-colors">
                <td class="px-5 py-3 text-slate-500 tabular-nums">{{ $topic->order }}</td>
                <td class="px-5 py-3">
                    <div class="font-medium text-white" dir="ltr">{{ $topic->title }}</div>
                    @if($topic->title_ar)<div class="text-xs text-slate-500 mt-0.5">{{ $topic->title_ar }}</div>@endif
                </td>
                <td class="px-5 py-3 text-slate-400 text-xs" dir="ltr">{{ implode(', ', $topic->cluster_ids ?? []) }}</td>
                <td class="px-5 py-3 text-slate-400">{{ count($topic->argumentsDafuer()) + count($topic->argumentsDagegen()) }}</td>
                <td class="px-5 py-3">
                    <form method="POST" action="{{ route('admin.mundlich.b2-sprechen.topic.toggle', $topic) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="flex items-center gap-1.5 text-xs font-medium transition-colors {{ $topic->is_published ? 'text-green-400 hover:text-red-400' : 'text-slate-500 hover:text-green-400' }}">
                            <span class="w-2 h-2 rounded-full {{ $topic->is_published ? 'bg-green-500' : 'bg-slate-600' }}"></span>
                            {{ $topic->is_published ? 'منشور' : 'مخفي' }}
                        </button>
                    </form>
                </td>
                <td class="px-5 py-3">
                    <form method="POST" action="{{ route('admin.mundlich.b2-sprechen.topic.destroy', $topic) }}"
                          onsubmit="return confirm('هل أنت متأكد من حذف هذا الموضوع؟')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-slate-600 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-slate-600">
                <div class="text-4xl mb-3">🎤</div>
                <div class="text-sm">لا توجد مواضيع بعد</div>
                <a href="{{ route('admin.mundlich.b2-sprechen.import.show') }}" class="mt-2 inline-block text-xs text-amber-400 hover:underline">استيراد الآن</a>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($topics->hasPages())
<div class="mt-4">{{ $topics->links() }}</div>
@endif

@endsection
