@extends('layouts.app')
@section('title', 'طلب الوصول | ' . config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto px-4 md:px-6 pt-28 pb-16"
     x-data="accessForm({{ Js::from(\App\Models\AccessRequest::EXAMS_BY_LANGUAGE) }})">

    {{-- Pending banner --}}
    @if($pending)
    <div class="mb-4 p-4 rounded-2xl border border-amber-500/30 bg-amber-500/10 flex items-center justify-between gap-3" dir="rtl">
        <div>
            <div class="font-bold text-amber-200">عندك طلب فالانتظار</div>
            <div class="text-sm text-amber-300/80 mt-0.5">{{ $pending->languageLabel() }} · {{ $pending->exam }} · {{ $pending->level }}</div>
        </div>
        <a href="{{ route('access.pending') }}" class="text-xs text-amber-300 hover:text-white">شوف الطلب</a>
    </div>
    @endif

    {{-- Current access banner --}}
    @if($current && !$pending)
    <div class="mb-4 p-4 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 flex items-center justify-between gap-3" dir="rtl">
        <div>
            <div class="font-bold text-emerald-200">الوصول الحالي ديالك</div>
            <div class="text-sm text-emerald-300/80 mt-0.5">{{ $current->languageLabel() }} · {{ $current->exam }} · {{ $current->level }}</div>
            <div class="text-[11px] text-slate-500 mt-1">قدم طلب جديد إلى بغيتي تبدل لاختبار آخر — الوصول الحالي كيبقى نشط حتى يدوز الجديد.</div>
        </div>
    </div>
    @endif

    <div class="rounded-2xl border border-white/[0.08] bg-[#111216] overflow-hidden">
        <div class="px-6 py-5 border-b border-white/[0.05]">
            <h1 class="text-xl font-bold text-white" dir="rtl">طلب وصول للمحتوى</h1>
            <p class="text-sm text-slate-500 mt-1" dir="rtl">اختار اللغة، الاختبار، والمستوى. الأدمن غادي يصادق على الطلب ديالك.</p>
        </div>

        @if($errors->any())
        <div class="mx-6 mt-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-200 text-sm space-y-1" dir="rtl">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('access.store') }}" class="p-6 space-y-5" dir="rtl">
            @csrf

            {{-- Language --}}
            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-2">اللغة</label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($languages as $code => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="language" value="{{ $code }}" x-model="lang" required class="peer sr-only">
                        <div class="px-4 py-3 rounded-xl border text-sm font-bold transition-all
                                    border-white/[0.08] bg-[#0B0C10] text-slate-300
                                    peer-checked:border-amber-500/60 peer-checked:bg-amber-500/10 peer-checked:text-white
                                    hover:border-amber-500/30">
                            {{ $label }}
                            @if($code !== 'de')
                            <span class="block text-[10px] text-slate-500 mt-0.5 font-normal">المحتوى قريب</span>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Exam (depends on language) --}}
            <div x-show="lang" x-transition>
                <label class="block text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-2">الاختبار</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <template x-for="examName in availableExams" :key="examName">
                        <label class="cursor-pointer">
                            <input type="radio" name="exam" :value="examName" x-model="exam" required class="peer sr-only">
                            <div class="px-4 py-2.5 rounded-xl border text-sm font-bold text-center transition-all
                                        border-white/[0.08] bg-[#0B0C10] text-slate-300
                                        peer-checked:border-amber-500/60 peer-checked:bg-amber-500/10 peer-checked:text-white
                                        hover:border-amber-500/30"
                                 x-text="examName"></div>
                        </label>
                    </template>
                </div>
            </div>

            {{-- Level --}}
            <div x-show="lang && exam" x-transition>
                <label class="block text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 mb-2">المستوى</label>
                <div class="grid grid-cols-5 gap-2">
                    @foreach($levels as $lvl)
                    <label class="cursor-pointer">
                        <input type="radio" name="level" value="{{ $lvl }}" required class="peer sr-only">
                        <div class="px-3 py-2.5 rounded-xl border text-sm font-bold text-center transition-all
                                    border-white/[0.08] bg-[#0B0C10] text-slate-300
                                    peer-checked:border-amber-500/60 peer-checked:bg-amber-500/10 peer-checked:text-white
                                    hover:border-amber-500/30">
                            {{ $lvl }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between gap-2">
                <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:text-white">إلغاء</a>
                <button type="submit" :disabled="!lang || !exam"
                        class="px-6 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white text-sm font-bold shadow-md hover:shadow-lg active:scale-95 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                    إرسال الطلب
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function accessForm(examsByLang) {
    return {
        lang: '',
        exam: '',
        examsByLang,
        get availableExams() {
            return this.examsByLang[this.lang] || [];
        },
    };
}
</script>
@endpush

@endsection
