@extends('admin.layout')
@section('title', 'المستخدمين')
@section('page-title', 'المستخدمين')

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

<form method="GET" class="mb-4">
    <input type="search" name="q" value="{{ $q }}" placeholder="بحث بالاسم أو البريد..."
           class="w-full md:max-w-sm px-4 py-2 rounded-xl border bg-[#0B0C10] border-white/10 text-white placeholder:text-slate-600 focus:outline-none focus:border-amber-500" dir="rtl">
</form>

<div class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
    @forelse($users as $user)
    <div class="px-5 py-4 border-b border-white/[0.04] last:border-0 flex flex-wrap items-center gap-3 justify-between">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 mb-0.5">
                <div class="font-bold text-white">{{ $user->name }}</div>
                @if($user->is_admin)
                <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-500/20 border border-amber-500/40 text-amber-200">ADMIN</span>
                @endif
            </div>
            <div class="text-xs text-slate-500" dir="ltr">{{ $user->email }}</div>
            <div class="text-[11px] text-slate-600 mt-1">
                <span>{{ $user->approved_count }} طلب مصادق</span>
                <span class="mx-1">·</span>
                <span>{{ $user->created_at->diffForHumans() }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.toggleAdmin', $user) }}">
            @csrf
            <button class="inline-flex items-center gap-1.5 px-3 h-8 rounded-lg border text-xs font-bold transition-all
                           {{ $user->is_admin
                               ? 'bg-red-500/15 border-red-500/40 text-red-200 hover:bg-red-500/25'
                               : 'bg-amber-500/15 border-amber-500/40 text-amber-200 hover:bg-amber-500/25' }}">
                @if($user->is_admin)
                    تجريد من Admin
                @else
                    رقّي ل Admin
                @endif
            </button>
        </form>
    </div>
    @empty
    <div class="px-5 py-12 text-center text-sm text-slate-500" dir="rtl">ماكاينش مستخدمين.</div>
    @endforelse

    @if($users->hasPages())
    <div class="px-5 py-3 border-t border-white/[0.05]">
        {{ $users->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
