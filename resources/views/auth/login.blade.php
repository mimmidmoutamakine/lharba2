@extends('layouts.app')
@section('title', 'تسجيل الدخول | ' . config('app.name'))
@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md p-8 rounded-2xl border bg-[#111216] border-white/[0.08]" dir="rtl">
        <h1 class="text-2xl font-bold text-white mb-6">تسجيل الدخول</h1>
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
            <button type="submit" class="btn-shine w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold transition-all">دخول</button>
        </form>
        <p class="mt-4 text-center text-sm text-slate-500">ما عندكش حساب؟ <a href="{{ route('register') }}" class="text-amber-400 hover:underline">سجل الآن</a></p>
    </div>
</div>
@endsection
