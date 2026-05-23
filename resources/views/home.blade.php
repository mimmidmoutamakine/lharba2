@extends('layouts.app')

@section('title', config('app.name') . ' – Telc B1 & B2 Exam Preparation | AI Grading')
@section('description', 'Master your German Telc B1 and B2 exams with AI-driven grading, real exam simulations, and instant feedback.')

@section('content')

{{-- ═══ HERO ═══ --}}
<section class="max-w-7xl mx-auto px-6 mb-0 pt-32" aria-label="Introduction">
    <div class="flex flex-col items-center text-center max-w-4xl mx-auto">

        {{-- ── HIDDEN — waiting for new copy ──────────────────────────────────
            Badge + Headline + Subtitle were here. Drop the new text in this
            block when ready, then remove the @if(false) wrapper.
        --}}
        @if(false)
        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-xs font-semibold uppercase mb-8 bg-amber-500/10 border-amber-500/20 text-amber-400" dir="rtl">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            <span>جديد: وجد لـ TELC B1 &amp; B2 و نتا هاني</span>
        </div>

        {{-- Headline --}}
        <h1 class="text-4xl sm:text-5xl md:text-7xl font-bold mb-6 leading-tight text-white" dir="rtl">
            نسا تمارة د الـ PDFs.. <br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-orange-400 to-white">
                ضمن نجاحك فـ Telc بذكاء!
            </span>
        </h1>

        {{-- Subtitle --}}
        <p class="text-lg md:text-xl mb-10 max-w-2xl leading-relaxed text-slate-400" dir="rtl">
            المنصة رقم 1 لي فيها كاع الـ <strong>Themen</strong> بالتعديلات ديالهم كاملين لـ <strong>Telc B1 &amp; B2</strong>.
            من جميع المواضيع د Lesen لجميع الـ <strong>Hören</strong> المسرب القديم و حتى ديال تركيا،
            وصولاً للتصحيح بالذكاء الاصطناعي فـ Schreiben. راجع بطريقة منظمة، ربح الوقت، و جيبها من الدقة اللولة!
        </p>
        @endif

        {{-- CTAs --}}
        <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto" dir="rtl">
            {{-- Start Free CTA — only CTA on the hero, no more WhatsApp / Explore distractions --}}
            <a href="{{ route('lesen.index') }}"
               class="btn-shine group w-full sm:w-auto px-8 py-4 rounded-xl font-bold text-lg transition-all shadow-lg flex items-center justify-center cursor-pointer bg-white text-black hover:bg-slate-200 shadow-[0_0_40px_-10px_rgba(255,255,255,0.3)]">
                <span>ابدأ مجاناً</span>
                <span class="inline-block transition-transform group-hover:-translate-x-1 mr-2">←</span>
            </a>
        </div>

        {{-- Trust Badge --}}
        <div class="mt-12 pt-8 border-t w-full max-w-2xl border-white/[0.08]">
            <p class="text-xs uppercase mb-4 font-semibold text-slate-500">موثوق به من قبل الطلاب الذين يستعدون لـ</p>
            <div class="flex flex-wrap justify-center items-center gap-6 md:gap-8 opacity-40 grayscale hover:grayscale-0 transition-all duration-500">
                <span class="text-lg md:text-xl font-bold tracking-widest text-white">TELC</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══ WHAT MAKES US UNIQUE — original feature grid ═══ --}}
<section id="features-lesen" class="scroll-mt-32 max-w-7xl mx-auto px-6 pt-24 pb-12">
    <div class="text-center mb-12" dir="rtl">
        <div class="inline-flex items-center gap-2 mb-3 text-amber-400 font-bold">
            <svg width="16" height="16" viewBox="0 0 100 100" fill="currentColor"><path d="M50 4 L57 38 L93 32 L66 56 L96 78 L60 70 L50 96 L40 70 L4 78 L34 56 L7 32 L43 38 Z"/><circle cx="50" cy="56" r="6" fill="#08090C"/></svg>
            <span class="text-xs uppercase tracking-[0.3em]">مميزات الهربة</span>
        </div>
        <h2 class="text-3xl md:text-5xl font-bold mb-4 text-white">ما شي PDF · ما شي تكرار · ما شي ضياع وقت</h2>
        <p class="text-lg text-slate-400 max-w-2xl mx-auto">أدوات بنيناها بيد، باش تكون أسرع نينجا فالمتحان</p>
    </div>

    {{-- 6 unique features grid --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">

        {{-- AI Examiner --}}
        <div class="group relative rounded-2xl border border-amber-500/20 bg-gradient-to-br from-amber-500/[0.05] to-transparent p-6 hover:border-amber-500/40 hover:-translate-y-1 transition-all" dir="rtl">
            <div class="absolute top-4 left-4 text-[10px] font-mono text-amber-500/60">01</div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-amber-300"><path d="M12 2 4 6v6c0 5.5 3.5 10.7 8 12 4.5-1.3 8-6.5 8-12V6l-8-4Z"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">AI Examiner ديال Schreiben</h3>
            <p class="text-sm text-slate-400 leading-relaxed mb-4">تصحيح صارم بمعايير telc B2 الرسمية. كتشوف نقطتك على 45، Verbesserte Version، وتوصيات بالدارجة.</p>
            <div class="flex items-center flex-wrap gap-1.5 text-[10px]">
                <span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30">x/45</span>
                <span class="px-2 py-0.5 rounded bg-amber-500/15 text-amber-300 font-bold border border-amber-500/30">A·B·C·D</span>
                <span class="px-2 py-0.5 rounded bg-orange-500/15 text-orange-300 font-bold border border-orange-500/30">Empfehlungen DARIJA</span>
            </div>
        </div>

        {{-- Beispiel Generator --}}
        <div class="group relative rounded-2xl border border-orange-500/20 bg-gradient-to-br from-orange-500/[0.05] to-transparent p-6 hover:border-orange-500/40 hover:-translate-y-1 transition-all" dir="rtl">
            <div class="absolute top-4 left-4 text-[10px] font-mono text-orange-500/60">02</div>
            <div class="w-10 h-10 rounded-xl bg-orange-500/15 border border-orange-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-orange-300"><path d="M12 2v4"/><path d="M12 18v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="m16.24 16.24 2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Beispiel Generator</h3>
            <p class="text-sm text-slate-400 leading-relaxed mb-4">عطي 2-3 أفكار بالدارجة على Leitpunkte، وگوگل-AI كيرد ليك Email B2 جاهز يدوز 39/45.</p>
            <div class="flex items-center flex-wrap gap-1.5 text-[10px]">
                <span class="px-2 py-0.5 rounded bg-orange-500/15 text-orange-300 font-bold border border-orange-500/30">دارجة → ألماني</span>
                <span class="px-2 py-0.5 rounded bg-amber-500/15 text-amber-300 font-bold border border-amber-500/30">~39/45</span>
            </div>
        </div>

        {{-- Plan dashboard --}}
        <div class="group relative rounded-2xl border border-emerald-500/20 bg-gradient-to-br from-emerald-500/[0.05] to-transparent p-6 hover:border-emerald-500/40 hover:-translate-y-1 transition-all" dir="rtl">
            <div class="absolute top-4 left-4 text-[10px] font-mono text-emerald-500/60">03</div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-emerald-300"><path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-4"/><circle cx="12" cy="6" r="4"/><path d="M12 10v12"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">خطة شخصية</h3>
            <p class="text-sm text-slate-400 leading-relaxed mb-4">علّم كل تمرين كـ "حفظت" ولا "راجع". الخطة كتقول ليك شنو تبدا ابدا اليوم.</p>
            <div class="flex items-center flex-wrap gap-1.5 text-[10px]">
                <span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30">✓ حفظت</span>
                <span class="px-2 py-0.5 rounded bg-amber-500/15 text-amber-300 font-bold border border-amber-500/30">↻ راجع</span>
                <span class="px-2 py-0.5 rounded bg-orange-500/15 text-orange-300 font-bold border border-orange-500/30">ابدا من هنا</span>
            </div>
        </div>

        {{-- Multi-language access --}}
        <div class="group relative rounded-2xl border border-amber-500/20 bg-gradient-to-br from-amber-500/[0.05] to-transparent p-6 hover:border-amber-500/40 hover:-translate-y-1 transition-all" dir="rtl">
            <div class="absolute top-4 left-4 text-[10px] font-mono text-amber-500/60">04</div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-amber-300"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">5 لغات · 12 امتحان</h3>
            <p class="text-sm text-slate-400 leading-relaxed mb-4">Telc · Goethe · ÖSD · ECL · TCF · TEF · DELE · CILS · Cambridge · IELTS · TOEFL — اطلب الوصول لي بغيتي.</p>
            <div class="flex items-center flex-wrap gap-1.5 text-[10px]">
                <span class="px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10">🇩🇪 DE</span>
                <span class="px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10">🇫🇷 FR</span>
                <span class="px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10">🇪🇸 ES</span>
                <span class="px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10">🇮🇹 IT</span>
                <span class="px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10">🇬🇧 EN</span>
            </div>
        </div>

        {{-- Print + Tools --}}
        <div class="group relative rounded-2xl border border-orange-500/20 bg-gradient-to-br from-orange-500/[0.05] to-transparent p-6 hover:border-orange-500/40 hover:-translate-y-1 transition-all" dir="rtl">
            <div class="absolute top-4 left-4 text-[10px] font-mono text-orange-500/60">05</div>
            <div class="w-10 h-10 rounded-xl bg-orange-500/15 border border-orange-500/30 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="text-orange-300"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">طبع · نسخ · ä ö ü ß</h3>
            <p class="text-sm text-slate-400 leading-relaxed mb-4">طبع نصك، طبع Verbesserte Version، طبع النموذج المولد. كيبورد ألماني داخل النص بضغطة.</p>
            <div class="flex items-center flex-wrap gap-1.5 text-[10px]">
                <span class="px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10 font-bold">ä</span>
                <span class="px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10 font-bold">ö</span>
                <span class="px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10 font-bold">ü</span>
                <span class="px-2 py-0.5 rounded bg-white/5 text-slate-300 border border-white/10 font-bold">ß</span>
                <span class="px-2 py-0.5 rounded bg-orange-500/15 text-orange-300 border border-orange-500/30 font-bold">PDF</span>
            </div>
        </div>

        {{-- Ninja UX --}}
        <div class="group relative rounded-2xl border border-emerald-500/20 bg-gradient-to-br from-emerald-500/[0.05] to-transparent p-6 hover:border-emerald-500/40 hover:-translate-y-1 transition-all" dir="rtl">
            <div class="absolute top-4 left-4 text-[10px] font-mono text-emerald-500/60">06</div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center mb-4 group-hover:rotate-180 transition-transform duration-700">
                <svg width="18" height="18" viewBox="0 0 100 100" fill="currentColor" class="text-emerald-300"><path d="M50 4 L57 38 L93 32 L66 56 L96 78 L60 70 L50 96 L40 70 L4 78 L34 56 L7 32 L43 38 Z"/><circle cx="50" cy="56" r="6" fill="#08090C"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">واجهة نينجا</h3>
            <p class="text-sm text-slate-400 leading-relaxed mb-4">Ninja-reveal للنقطة، compact mode للتصحيح، dark mode، Mobile-first، حفظ تلقائي ديال draft فالكاش.</p>
            <div class="flex items-center flex-wrap gap-1.5 text-[10px]">
                <span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 font-bold">⚡ Reveal</span>
                <span class="px-2 py-0.5 rounded bg-amber-500/15 text-amber-300 border border-amber-500/30 font-bold">📱 Mobile-first</span>
                <span class="px-2 py-0.5 rounded bg-orange-500/15 text-orange-300 border border-orange-500/30 font-bold">💾 Auto-save</span>
            </div>
        </div>
    </div>
</section>

{{-- ═══ AI EXAMINER DEEP-DIVE — text preview, no fake mockup ═══ --}}
<section id="features-schreiben" class="scroll-mt-32 max-w-7xl mx-auto px-6 py-16">
    <div class="rounded-3xl border border-amber-500/20 bg-gradient-to-br from-amber-500/[0.04] via-transparent to-orange-500/[0.04] p-6 md:p-10 overflow-hidden">
        <div class="grid lg:grid-cols-5 gap-8 items-start">

            {{-- Left: copy --}}
            <div class="lg:col-span-2" dir="rtl">
                <div class="inline-flex items-center gap-2 text-amber-400 font-bold mb-3">
                    <svg width="14" height="14" viewBox="0 0 100 100" fill="currentColor"><path d="M50 4 L57 38 L93 32 L66 56 L96 78 L60 70 L50 96 L40 70 L4 78 L34 56 L7 32 L43 38 Z"/></svg>
                    <span class="text-[11px] uppercase tracking-[0.25em]">AI Examiner</span>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-4 leading-snug">يصحح ليك Schreiben بحال أستاذ telc حقيقي</h2>
                <p class="text-slate-400 leading-relaxed mb-6">prompt مكتوب بـ 600 سطر من قواعد telc B2 الرسمية. كنفصلو ليك على Aufgabenbewältigung · Kommunikative Gestaltung · Formale Richtigkeit، ونعطيك توصيات قابلة للتطبيق دغيا.</p>
                <ul class="space-y-2.5 mb-6 text-sm">
                    <li class="flex items-center gap-2 text-slate-300"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> نقطة على 45 + Roh على 15</li>
                    <li class="flex items-center gap-2 text-slate-300"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Thema/Situierung verfehlt detection</li>
                    <li class="flex items-center gap-2 text-slate-300"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Verbesserte Version جاهزة للنسخ</li>
                    <li class="flex items-center gap-2 text-slate-300"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Empfehlungen بالدارجة</li>
                </ul>
                <a href="{{ route('schreiben.index') }}" class="btn-shine inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white font-bold text-sm shadow-lg shadow-amber-500/20 hover:shadow-amber-500/40 transition-all">
                    جرّب AI Examiner
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            </div>

            {{-- Right: text-style preview (NOT a fake screenshot — actual stylized markdown look) --}}
            <div class="lg:col-span-3 rounded-2xl border border-white/[0.06] bg-[#0B0C10] overflow-hidden font-mono text-[12px]" dir="ltr">
                <div class="px-4 py-2.5 border-b border-white/[0.05] flex items-center justify-between bg-[#111216]">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span class="text-[10px] font-bold text-slate-400">examiner.output</span>
                    </div>
                    <span class="text-[10px] text-slate-600">~3.2s · gemini-2.5-flash</span>
                </div>
                <div class="p-5 space-y-4">
                    {{-- Score badge --}}
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl border-2 border-emerald-400 bg-emerald-500/15 flex flex-col items-center justify-center text-emerald-200 font-black leading-none">
                            <span class="text-xl">39</span>
                            <span class="text-[9px] opacity-70 mt-0.5">/ 45</span>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500">telc Bewertung</div>
                            <div class="text-white font-bold text-base">B2 niveau erreicht</div>
                            <div class="text-[10px] text-slate-500">Roh: 13/15 · 178 Wörter</div>
                        </div>
                    </div>
                    {{-- Mini table --}}
                    <div class="grid grid-cols-3 gap-px bg-white/[0.05] rounded-lg overflow-hidden text-center">
                        <div class="bg-[#111216] py-2">
                            <div class="text-[9px] uppercase tracking-wider text-slate-500">Inhalt</div>
                            <div class="text-emerald-300 font-bold text-base">A · 5/5</div>
                        </div>
                        <div class="bg-[#111216] py-2">
                            <div class="text-[9px] uppercase tracking-wider text-slate-500">Gestaltung</div>
                            <div class="text-amber-300 font-bold text-base">B · 3/5</div>
                        </div>
                        <div class="bg-[#111216] py-2">
                            <div class="text-[9px] uppercase tracking-wider text-slate-500">Formal</div>
                            <div class="text-emerald-300 font-bold text-base">A · 5/5</div>
                        </div>
                    </div>
                    {{-- Verbesserte Version preview --}}
                    <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/[0.04] p-3">
                        <div class="text-[9px] font-bold uppercase tracking-widest text-emerald-400 mb-1.5">## Verbesserte Version</div>
                        <p class="text-slate-300 leading-relaxed whitespace-pre-line">Sehr geehrte Damen und Herren,&#10;&#10;ich schreibe Ihnen, weil ich mit Ihrem Streaming-Dienst sehr unzufrieden bin. Aufgrund der zahlreichen Werbungen…</p>
                    </div>
                    {{-- Darija recommendation --}}
                    <div class="rounded-lg border border-orange-500/20 bg-orange-500/[0.04] p-3" dir="rtl">
                        <div class="text-[9px] font-bold uppercase tracking-widest text-orange-400 mb-1.5">## توصيات بالدارجة</div>
                        <p class="text-slate-300 leading-relaxed font-cairo">فالنقطة ديال Forderung خاصك تكون واضح: بغيت استرجاع المبلغ، أو إلغاء الاشتراك، أو جواب كتابي داخل 14 يوم.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══ BEISPIEL GENERATOR — flow diagram ═══ --}}
<section id="features-generator" class="scroll-mt-32 max-w-7xl mx-auto px-6 py-16">
    <div class="text-center mb-10" dir="rtl">
        <div class="inline-flex items-center gap-2 mb-3 text-orange-400 font-bold">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 2v4"/><path d="M12 18v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="m16.24 16.24 2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/></svg>
            <span class="text-[11px] uppercase tracking-[0.25em]">Beispiel Generator · حصري</span>
        </div>
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">من فكرة بالدارجة → إيميل B2 جاهز</h2>
        <p class="text-slate-400 max-w-2xl mx-auto">اختار النقط، عطي 2-3 سيناريوهات بالدارجة، احصل على نموذج يدوز ~39/45.</p>
    </div>

    {{-- 3 step cards --}}
    <div class="grid md:grid-cols-3 gap-4">
        @php
            $steps = [
                ['n' => '1', 'title' => 'اختار Leitpunkte', 'body' => 'شيك على 3 من 4 نقط لي بغيتي تعالج، ولا 2 + Eigener Aspekt من راسك.', 'preview' => '☑ LP1 · Werbung&#10;☑ LP2 · Forderung&#10;☑ LP3 · Probleme'],
                ['n' => '2', 'title' => 'عطي أفكارك بالدارجة', 'body' => 'لكل نقطة، كتب 2-3 سيناريوهات واقعية بالدارجة. خليه فارغ → AI كيخترع سيناريو.', 'preview' => 'خلصت 9.99€ فالشهر،&#10;الإعلانات كتقطع الفيديو،&#10;ماكاينش ترجمة ألمانية'],
                ['n' => '3', 'title' => 'دير Generate', 'body' => 'فـ 5-15 ثانية، كتشوف Email B2 كامل: Betreff · Anrede · Paragraphs · Forderung · Schluss.', 'preview' => 'Sehr geehrte Damen und Herren,&#10;&#10;mit großer Enttäuschung musste ich…&#10;&#10;Mit freundlichen Grüßen,&#10;Moutmakine Harban'],
            ];
        @endphp
        @foreach($steps as $step)
        <div class="rounded-2xl border border-white/[0.06] bg-[#111216] overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-white/[0.05] flex items-center gap-3" dir="rtl">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-500 to-amber-600 flex items-center justify-center text-white font-black text-base shrink-0">{{ $step['n'] }}</div>
                <h3 class="font-bold text-white">{{ $step['title'] }}</h3>
            </div>
            <div class="px-5 py-4 flex-1 flex flex-col gap-3">
                <p class="text-sm text-slate-400 leading-relaxed" dir="rtl">{{ $step['body'] }}</p>
                <div class="mt-auto rounded-lg border border-white/[0.05] bg-black/30 p-3 font-mono text-[11px] text-slate-400 leading-relaxed whitespace-pre-line" dir="auto">{!! $step['preview'] !!}</div>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ═══ PLAN PREVIEW ═══ --}}
<section id="features-plan" class="scroll-mt-32 max-w-7xl mx-auto px-6 py-16">
    <div class="rounded-3xl border border-emerald-500/20 bg-gradient-to-br from-emerald-500/[0.04] to-transparent overflow-hidden">
        <div class="grid lg:grid-cols-2 gap-8 items-center p-6 md:p-10">

            {{-- Left: stylized plan board preview --}}
            <div class="space-y-3" dir="ltr">
                {{-- Top stats --}}
                <div class="grid grid-cols-3 gap-2">
                    <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/[0.06] p-3 text-center">
                        <div class="text-2xl font-bold text-emerald-300 tabular-nums">23</div>
                        <div class="text-[9px] uppercase tracking-wider text-slate-500">حفظت</div>
                    </div>
                    <div class="rounded-xl border border-amber-500/20 bg-amber-500/[0.06] p-3 text-center">
                        <div class="text-2xl font-bold text-amber-300 tabular-nums">7</div>
                        <div class="text-[9px] uppercase tracking-wider text-slate-500">راجع</div>
                    </div>
                    <div class="rounded-xl border border-white/[0.06] bg-[#111216] p-3 text-center">
                        <div class="text-2xl font-bold text-slate-200 tabular-nums">12</div>
                        <div class="text-[9px] uppercase tracking-wider text-slate-500">جديد</div>
                    </div>
                </div>
                {{-- Bucket cards --}}
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['Lesen Teil 1' => 80, 'Lesen Teil 2' => 60, 'Sprachbausteine 1' => 45, 'Hören' => 70] as $label => $pct)
                    <div class="rounded-xl border border-white/[0.06] bg-[#111216] p-3">
                        <div class="text-[10px] font-bold text-white mb-1 truncate">{{ $label }}</div>
                        <div class="h-1.5 rounded-full bg-white/[0.05] overflow-hidden">
                            <div class="h-full bg-emerald-500" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="text-[9px] text-slate-500 mt-1 tabular-nums">{{ $pct }}% mastered</div>
                    </div>
                    @endforeach
                </div>
                {{-- ASAP queue --}}
                <div class="rounded-xl border border-orange-500/20 bg-orange-500/[0.04] p-3">
                    <div class="text-[10px] font-black uppercase tracking-widest text-orange-400 mb-2">ابدا من هنا</div>
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-300">Telekommunikation · Hören Teil 1 · B2</span>
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-orange-400"><path d="m9 18 6-6-6-6"/></svg>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-slate-300">Sport ist gesund · Lesen Teil 2 · B2</span>
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-orange-400"><path d="m9 18 6-6-6-6"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: copy --}}
            <div dir="rtl">
                <div class="inline-flex items-center gap-2 text-emerald-400 font-bold mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-4"/><circle cx="12" cy="6" r="4"/><path d="M12 10v12"/></svg>
                    <span class="text-[11px] uppercase tracking-[0.25em]">خطتك الذكية</span>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">7 Buckets · إحصائيات حية · ABDA من هنا</h2>
                <p class="text-slate-400 leading-relaxed mb-6">
                    عوض ما تحتار شنو غادي تدير اليوم، الخطة كتورّيك دغيا:
                    شحال حفظتي، شحال خاصك تراجع، ومنين خاصك تبدا.
                    منفصلة بـ Teil — Lesen Teil 1, 2, 3 + Sprachbausteine 1 &amp; 2 + Hören + Schreiben.
                </p>
                <a href="{{ route('plan') }}" class="btn-shine inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white font-bold text-sm shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 transition-all">
                    شوف الخطة ديالك
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ═══ STATS BAR ═══ --}}
<section class="max-w-7xl mx-auto px-6 py-12">
    <div class="rounded-2xl border border-white/[0.06] bg-gradient-to-br from-amber-500/[0.03] via-transparent to-orange-500/[0.03] p-6 md:p-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center" dir="rtl">
            <div>
                <div class="text-3xl md:text-4xl font-black text-white tabular-nums">5</div>
                <div class="text-xs text-slate-500 mt-1">لغات مدعومة</div>
            </div>
            <div>
                <div class="text-3xl md:text-4xl font-black text-white tabular-nums">12+</div>
                <div class="text-xs text-slate-500 mt-1">امتحان معترف به</div>
            </div>
            <div>
                <div class="text-3xl md:text-4xl font-black text-white tabular-nums">~3s</div>
                <div class="text-xs text-slate-500 mt-1">سرعة AI Examiner</div>
            </div>
            <div>
                <div class="text-3xl md:text-4xl font-black text-white tabular-nums">100%</div>
                <div class="text-xs text-slate-500 mt-1">بالدارجة المغربية</div>
            </div>
        </div>
    </div>
</section>

{{-- ═══ TESTIMONIALS — NINJA QUOTES ═══ --}}
<section class="transition-colors duration-500 py-16 md:py-24 overflow-hidden border-y bg-[#111216]/30 border-white/[0.08]">
    <div class="max-w-7xl mx-auto px-6 mb-12 text-center">
        <div class="inline-flex items-center gap-2 mb-3 text-amber-400 font-bold" dir="rtl">
            {{-- shuriken --}}
            <svg width="18" height="18" viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
                <path d="M50 4 L57 38 L93 32 L66 56 L96 78 L60 70 L50 96 L40 70 L4 78 L34 56 L7 32 L43 38 Z"/>
                <circle cx="50" cy="56" r="6" fill="#08090C"/>
            </svg>
            <span class="text-xs uppercase tracking-[0.3em]">شهادات النينجا</span>
        </div>
        <h2 class="text-3xl md:text-4xl font-bold mb-4 font-cairo text-white">آراء المشتركين</h2>
        <p class="max-w-2xl mx-auto font-cairo text-slate-400" dir="rtl">طلاب الهرب لي ولاو نينجا د الألماني — نجحوا فالمتحان وحققوا حلم ألمانيا</p>
    </div>

    @php
        $tones = [
            'amber'   => ['bg' => 'bg-amber-500/10',   'border' => 'border-amber-500/20',   'text' => 'text-amber-300',   'avatar' => 'from-amber-500 to-orange-600'],
            'orange'  => ['bg' => 'bg-orange-500/10',  'border' => 'border-orange-500/20',  'text' => 'text-orange-300',  'avatar' => 'from-orange-500 to-red-600'],
            'emerald' => ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/20', 'text' => 'text-emerald-300', 'avatar' => 'from-emerald-500 to-emerald-700'],
        ];
        $testimonials = [
            ['name' => 'Yassir',  'city' => 'الدار البيضاء', 'exam' => 'Telc B2',   'tone' => 'amber',   'quote' => 'نجحت B2 من اللولة. المنصة ديال الهرب خداتني بحال أستاذ خاص فدراي. التصحيح بالـ AI خلاني نفهم أخطائي فدقيقة.'],
            ['name' => 'Khadija', 'city' => 'الرباط',         'exam' => 'Telc B1',   'tone' => 'orange',  'quote' => 'الـ Hören كان كيخلعني، دبا ولا خفيف عليا. قصص الـ Codat كتعاون بزاف على الحفظ.'],
            ['name' => 'Mehdi',   'city' => 'مراكش',          'exam' => 'Goethe B2', 'tone' => 'emerald', 'quote' => 'تصحيح Schreiben بالذكاء الاصطناعي قلب اللعبة. كنشوف نقطتي ومن وين خاصني نحسن فثواني.'],
            ['name' => 'Ines',    'city' => 'فاس',            'exam' => 'ÖSD B1',    'tone' => 'amber',   'quote' => 'محتوى منظم، ما عاد كنخسر الوقت كنقلب فـ PDFs. كلش فبلاصة، حتى Sprachbausteine.'],
            ['name' => 'Ahmed',   'city' => 'طنجة',           'exam' => 'Telc B2',   'tone' => 'orange',  'quote' => 'بدأت بمستوى A2 ودبا راني فـ B2 مع شهادة. الفضل لتمارين الهرب وللخطة الذكية.'],
            ['name' => 'Salma',   'city' => 'أكادير',         'exam' => 'Telc B1',   'tone' => 'emerald', 'quote' => 'المراجعة ولات سهلة بفضل خطة "حفظت / راجع". كنعرف فاش غادي ندير اليوم بلا حيرة.'],
            ['name' => 'Omar',    'city' => 'تطوان',          'exam' => 'Goethe C1', 'tone' => 'amber',   'quote' => 'أحسن استثمار درت قبل المتحان. ربحت أسابيع من الوقت وكنت مرتاح ومركّز.'],
            ['name' => 'Fatima',  'city' => 'وجدة',           'exam' => 'Telc B2',   'tone' => 'orange',  'quote' => 'بصراحة، ما لقيت بحالها فالويب. خاصة Beispiel ديال Schreiben — كنتعلم منها بزاف.'],
        ];
    @endphp

    <div class="relative w-full" dir="ltr">
        <div class="absolute left-0 top-0 h-full w-24 md:w-48 bg-gradient-to-r pointer-events-none z-10 from-[#08090C] to-transparent"></div>
        <div class="absolute right-0 top-0 h-full w-24 md:w-48 bg-gradient-to-l pointer-events-none z-10 from-[#08090C] to-transparent"></div>
        <div class="animate-marquee" id="lharba-testimonials-marquee">
            @foreach([1, 2] as $_)
            <div class="flex gap-5 px-3 shrink-0">
                @foreach($testimonials as $t)
                @php $tone = $tones[$t['tone']]; @endphp
                <div class="shrink-0 w-[280px] sm:w-[320px] md:w-[340px] rounded-2xl border bg-[#111216] {{ $tone['border'] }} hover:border-white/20 transition-all duration-300 hover:-translate-y-1 shadow-2xl shadow-black/40 overflow-hidden group" dir="rtl">

                    {{-- Top: shuriken decoration + 5 stars --}}
                    <div class="px-5 pt-4 pb-3 flex items-center justify-between">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $tone['bg'] }} {{ $tone['border'] }} border">
                            <svg width="18" height="18" viewBox="0 0 100 100" fill="currentColor" class="{{ $tone['text'] }} group-hover:rotate-180 transition-transform duration-700" aria-hidden="true">
                                <path d="M50 4 L57 38 L93 32 L66 56 L96 78 L60 70 L50 96 L40 70 L4 78 L34 56 L7 32 L43 38 Z"/>
                                <circle cx="50" cy="56" r="6" fill="#111216"/>
                            </svg>
                        </div>
                        <div class="flex items-center gap-0.5 text-amber-400">
                            @for($i = 0; $i < 5; $i++)
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            @endfor
                        </div>
                    </div>

                    {{-- Quote --}}
                    <blockquote class="px-5 pb-4 text-sm leading-relaxed text-slate-200">
                        "{{ $t['quote'] }}"
                    </blockquote>

                    {{-- Footer: avatar + name + exam --}}
                    <div class="px-5 py-3 border-t border-white/[0.05] bg-black/20 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="relative w-9 h-9 shrink-0 rounded-lg bg-gradient-to-br {{ $tone['avatar'] }} flex items-center justify-center text-white font-black text-sm">
                                {{ mb_substr($t['name'], 0, 1) }}
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-[#111216] border border-white/10 flex items-center justify-center">
                                    <svg width="9" height="9" viewBox="0 0 100 100" fill="currentColor" class="{{ $tone['text'] }}" aria-hidden="true"><path d="M50 4 L57 38 L93 32 L66 56 L96 78 L60 70 L50 96 L40 70 L4 78 L34 56 L7 32 L43 38 Z"/></svg>
                                </span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-white truncate">{{ $t['name'] }}</div>
                                <div class="text-[11px] text-slate-500 truncate">{{ $t['city'] }}</div>
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold {{ $tone['bg'] }} {{ $tone['border'] }} {{ $tone['text'] }} border">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            {{ $t['exam'] }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ CTA SECTION ═══ --}}
<section class="py-32 text-center px-6 transition-colors duration-500" aria-label="Call to Action" dir="rtl">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-4xl md:text-5xl font-bold mb-6 text-white">واجد باش تنجح فامتحان اللغة الألمانية؟</h2>
        <p class="mb-10 text-lg text-slate-400">انضم لكثر من 800 طالب لي خداو شهادة اللغة و راهم دبا فـ ألمانيا كيحققوا أحلامهم بفضل المنصة ديالنا.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('lesen.index') }}"
               class="btn-shine px-8 py-4 rounded-xl bg-amber-600 text-white font-bold hover:bg-amber-500 transition-all shadow-lg shadow-amber-500/20 cursor-pointer">
                ابدأ مجاناً
            </a>
            <a href="{{ route('billing') }}"
               class="px-8 py-4 rounded-xl border font-medium transition-all cursor-pointer border-white/[0.08] text-white hover:bg-white/5">
                عرض الأسعار
            </a>
        </div>
    </div>
</section>

@push('scripts')
<script>
    // Pause the testimonials marquee when it's offscreen — saves continuous CPU/GPU
    // on a heavy homepage. Also pause when the tab is hidden.
    (function () {
        const el = document.getElementById('lharba-testimonials-marquee');
        if (!el) return;
        const setPaused = (paused) => { el.style.animationPlayState = paused ? 'paused' : 'running'; };

        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver(
                (entries) => entries.forEach(e => setPaused(!e.isIntersecting)),
                { rootMargin: '100px' }
            );
            io.observe(el);
        }
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) setPaused(true);
            // Resume only if the marquee is also onscreen — IO will fix it on next event.
            else if (el.getBoundingClientRect().bottom > 0 && el.getBoundingClientRect().top < window.innerHeight) {
                setPaused(false);
            }
        });
    })();
</script>
@endpush

@endsection
