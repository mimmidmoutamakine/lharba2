@extends('layouts.app')
@section('title', 'تسجيل الدخول | ' . config('app.name'))
@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md p-8 rounded-2xl border bg-[#111216] border-white/[0.08]" dir="rtl">
        <h1 class="text-2xl font-bold text-white mb-4">تسجيل الدخول</h1>

        {{-- Prominent "new user?" CTA — visible to first-time visitors who
             land on /login by accident. The small text link at the bottom of
             the card is kept too as a secondary path. --}}
        <a href="{{ route('register') }}"
           class="block mb-5 p-3.5 rounded-xl border border-amber-500/30 bg-amber-500/[0.06] hover:bg-amber-500/[0.10] hover:border-amber-500/50 transition-all group">
            <div class="flex items-center gap-3">
                <div class="shrink-0 w-9 h-9 rounded-full bg-amber-500/20 flex items-center justify-center text-base">✨</div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-amber-200">جديد فـ الهربة؟</div>
                    <div class="text-[11px] text-slate-400 leading-tight mt-0.5">دير حساب فـ دقيقة وبدا التحضير ديال الامتحان</div>
                </div>
                <div class="shrink-0 inline-flex items-center gap-1 px-3 h-8 rounded-lg bg-amber-600 group-hover:bg-amber-500 text-white text-xs font-bold transition-all">
                    سجل الآن
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-translate-x-0.5 transition-transform"><path d="m15 18-6-6 6-6"/></svg>
                </div>
            </div>
        </a>

        @if($errors->any())
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
            {{ $errors->first() }}
        </div>
        @endif
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-3 rounded-xl border bg-[#0B0C10] border-white/10 text-white placeholder:text-slate-600 focus:outline-none focus:border-amber-500" placeholder="example@mail.com" dir="ltr">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">كلمة المرور</label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border bg-[#0B0C10] border-white/10 text-white placeholder:text-slate-600 focus:outline-none focus:border-amber-500" placeholder="••••••••" dir="ltr">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-400 cursor-pointer select-none">
                <input type="checkbox" name="remember" value="1" checked
                       class="w-4 h-4 rounded border-white/20 bg-[#0B0C10] text-amber-500 focus:ring-amber-500/40 focus:ring-offset-0">
                <span>خلني مسجل دخول</span>
            </label>
            <button type="submit" class="btn-shine w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold transition-all">دخول</button>
        </form>
        <p class="mt-4 text-center text-sm text-slate-500">ما عندكش حساب؟ <a href="{{ route('register') }}" class="text-amber-400 hover:underline">سجل الآن</a></p>
    </div>
</div>
@endsection
