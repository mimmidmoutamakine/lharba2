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
    <div class="px-5 py-4 border-b border-white/[0.04] last:border-0" x-data="{ pwOpen: false, pw: '', showPw: false }">
        <div class="flex flex-wrap items-center gap-3 justify-between">
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

            <div class="flex items-center gap-2">
                <button type="button" @click="pwOpen = !pwOpen; if (pwOpen) $nextTick(() => $refs.pwInput?.focus())"
                        class="inline-flex items-center gap-1.5 px-3 h-8 rounded-lg border text-xs font-bold transition-all border-slate-500/40 text-slate-300 hover:bg-slate-500/15"
                        :class="pwOpen ? 'bg-slate-500/20 text-white' : ''">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    باسوورد
                </button>

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
        </div>

        {{-- Inline password-reset form. Plain text on purpose: admin sees what they're setting,
             can copy it, and the success flash repeats it once so it can be handed off. --}}
        <div x-show="pwOpen" x-cloak x-collapse>
            <form method="POST" action="{{ route('admin.users.resetPassword', $user) }}"
                  class="mt-3 p-3 rounded-xl bg-black/30 border border-white/[0.06] flex flex-wrap items-center gap-2"
                  @submit="pwOpen = false">
                @csrf
                <div class="relative flex-1 min-w-[200px]">
                    <input :type="showPw ? 'text' : 'password'" name="password" x-model="pw" x-ref="pwInput"
                           required minlength="8" maxlength="72" autocomplete="new-password"
                           placeholder="الباسوورد الجديد (8 على الأقل)"
                           class="w-full pl-9 pr-3 h-9 rounded-lg bg-[#0B0C10] border border-white/10 text-white text-sm placeholder:text-slate-600 focus:outline-none focus:border-amber-500" dir="ltr">
                    <button type="button" @click="showPw = !showPw"
                            class="absolute left-2 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-slate-500 hover:text-white"
                            :title="showPw ? 'إخفاء' : 'إظهار'">
                        <template x-if="!showPw"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></template>
                        <template x-if="showPw"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/></svg></template>
                    </button>
                </div>
                <button type="button"
                        @click="pw = Array.from(crypto.getRandomValues(new Uint8Array(12))).map(b => 'abcdefghijkmnpqrstuvwxyz23456789'[b % 32]).join(''); showPw = true"
                        class="inline-flex items-center gap-1 h-9 px-3 rounded-lg border border-white/10 text-xs font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-all" dir="rtl"
                        title="ولّد باسوورد عشوائي">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M16 8h.01"/><path d="M8 8h.01"/><path d="M8 16h.01"/><path d="M16 16h.01"/><path d="M12 12h.01"/></svg>
                    ولّد
                </button>
                <button type="submit"
                        :disabled="pw.length < 8"
                        class="inline-flex items-center gap-1 h-9 px-4 rounded-lg bg-amber-600 text-white text-xs font-bold hover:bg-amber-500 active:scale-95 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                    حفظ
                </button>
                <button type="button" @click="pwOpen = false; pw = ''"
                        class="inline-flex items-center gap-1 h-9 px-3 rounded-lg border border-white/10 text-xs text-slate-400 hover:text-white">
                    إلغاء
                </button>
            </form>
        </div>
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
