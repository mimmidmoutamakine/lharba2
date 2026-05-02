@extends('layouts.app')
@section('title', 'إنشاء حساب | ' . config('app.name'))
@section('content')
<div class="min-h-screen flex items-center justify-center px-4 pt-24">
    <div class="w-full max-w-md p-8 rounded-2xl border bg-[#111216] border-white/[0.08]" dir="rtl">
        <h1 class="text-2xl font-bold text-white mb-1">إنشاء حساب جديد</h1>
        <p class="text-sm text-slate-500 mb-6">سجّل وعقبا قدم طلب الوصول للمحتوى</p>

        @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-200 text-sm space-y-1">
            @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">الاسم</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-3 rounded-xl border bg-[#0B0C10] border-white/10 text-white placeholder:text-slate-600 focus:outline-none focus:border-amber-500"
                       placeholder="اسمك الكامل">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-3 rounded-xl border bg-[#0B0C10] border-white/10 text-white placeholder:text-slate-600 focus:outline-none focus:border-amber-500"
                       placeholder="example@mail.com" dir="ltr">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">كلمة المرور <span class="text-slate-600 text-[10px]">· 8 محاارف على الأقل</span></label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-4 py-3 rounded-xl border bg-[#0B0C10] border-white/10 text-white focus:outline-none focus:border-amber-500" dir="ltr">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-1">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="w-full px-4 py-3 rounded-xl border bg-[#0B0C10] border-white/10 text-white focus:outline-none focus:border-amber-500" dir="ltr">
            </div>
            <button type="submit" class="btn-shine w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold transition-all">ابدأ الآن مجاناً</button>
        </form>
        <p class="mt-4 text-center text-sm text-slate-500">عندك حساب؟ <a href="{{ route('login') }}" class="text-amber-400 hover:underline">دخول</a></p>
    </div>
</div>
@endsection
