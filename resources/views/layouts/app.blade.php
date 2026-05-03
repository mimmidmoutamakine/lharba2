<!DOCTYPE html>
<html lang="ar" class="scroll-smooth notranslate" translate="no"
    style="--app-font-size-scale: 1; font-size: 100%;">
<head>
    {{-- Theme bootstrap — runs BEFORE anything renders to avoid FOUC --}}
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('zertify-theme');
                var isDark = stored !== 'light';
                var html   = document.documentElement;
                if (isDark) { html.classList.add('dark'); html.classList.remove('light-theme'); }
                else        { html.classList.add('light-theme'); html.classList.remove('dark'); }

                window.toggleTheme = function () {
                    var nowLight = !html.classList.contains('light-theme');
                    if (nowLight) {
                        html.classList.add('light-theme');
                        html.classList.remove('dark');
                        localStorage.setItem('zertify-theme', 'light');
                    } else {
                        html.classList.remove('light-theme');
                        html.classList.add('dark');
                        localStorage.setItem('zertify-theme', 'dark');
                    }
                    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { isDark: !nowLight } }));
                };
                window.isLightTheme = function () { return html.classList.contains('light-theme'); };
            } catch (e) { /* localStorage may be blocked */ }
        })();
    </script>

    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#08090C">
    <meta name="application-name" content="{{ config('app.name', 'الهربة') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'الهربة') . ' – Telc B1 & B2 Exam Preparation')</title>
    <meta name="description" content="@yield('description', 'Master your German Telc B1 and B2 exams with AI-driven grading, real exam simulations, and instant feedback.')">

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>

<body class="bg-[#08090C] text-slate-900 font-cairo antialiased overflow-x-hidden selection:bg-amber-500/40 selection:text-white">

<div class="bg-[#08090C] min-h-screen font-cairo antialiased overflow-x-hidden relative transition-colors duration-500 text-slate-300 selection:bg-amber-500/30 selection:text-white">

    {{-- Background Effects: soft glow + floating ninja shurikens --}}
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] blur-[120px] rounded-full bg-amber-500/20 opacity-20"></div>

        @php
            // Each shuriken: [left%, size px, duration s, delay s (negative so they're already mid-flight on load), opacity, drift px]
            $shurikens = [
                [  6, 36, 38, -3,   0.07, 18 ],
                [ 14, 28, 46, -22,  0.05, -14 ],
                [ 23, 48, 52, -10,  0.08,  22 ],
                [ 32, 24, 34, -28,  0.05, -12 ],
                [ 42, 40, 42, -15,  0.07,  16 ],
                [ 52, 32, 36, -38,  0.06, -18 ],
                [ 62, 56, 56, -8,   0.09,  24 ],
                [ 72, 28, 40, -19,  0.05, -16 ],
                [ 82, 44, 48, -32,  0.08,  20 ],
                [ 92, 30, 44, -2,   0.06, -14 ],
            ];
        @endphp
        @foreach($shurikens as [$left, $size, $duration, $delay, $opacity, $drift])
        <div class="shuriken"
             style="left: {{ $left }}%;
                    --shuriken-duration: {{ $duration }}s;
                    --shuriken-delay: {{ $delay }}s;
                    --shuriken-opacity: {{ $opacity }};
                    --shuriken-drift: {{ $drift }}px;">
            <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 100 100" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
                <path d="M50 4 L57 38 L93 32 L66 56 L96 78 L60 70 L50 96 L40 70 L4 78 L34 56 L7 32 L43 38 Z"/>
                <circle cx="50" cy="56" r="6" fill="rgba(8, 9, 12, 0.9)" stroke="none"/>
            </svg>
        </div>
        @endforeach

    </div>

    {{-- Navbar --}}
    <nav x-data="mobileMenu()"
         class="fixed top-0 w-full z-50 border-b transition-all duration-300 backdrop-blur-lg bg-[#08090C]/90 border-white/[0.08] shadow-lg shadow-black/20">
        <div class="max-w-7xl mx-auto px-4 md:px-6 h-16 md:h-20 flex items-center justify-between">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 group py-2" title="الهربة · Lharba">
                <img src="{{ asset('images/lharba-logo.svg') }}"
                     alt="الهربة"
                     class="h-8 md:h-10 w-auto shrink-0 transition-transform duration-300 group-hover:scale-105 drop-shadow-[0_0_15px_rgba(245,158,11,0.25)]"
                     loading="eager">
                <span class="hidden md:inline text-[10px] font-bold tracking-widest uppercase text-slate-500 group-hover:text-amber-400 transition-colors border-l border-white/10 pl-3">Telc Prep B1/B2</span>
            </a>

            {{-- Desktop Nav Links --}}
            <div class="hidden lg:flex items-center gap-1 border rounded-full p-1 shrink-0 bg-white/5 border-white/[0.08]">
                <a href="{{ route('lesen.index') }}"
                   class="px-4 py-1.5 text-sm font-medium rounded-full transition-all whitespace-nowrap {{ request()->routeIs('lesen*') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                    Lesen
                </a>
                <a href="{{ route('hoeren.index') }}"
                   class="px-4 py-1.5 text-sm font-medium rounded-full transition-all whitespace-nowrap {{ request()->routeIs('hoeren*') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                    Hören
                </a>
                <a href="{{ route('schreiben.index') }}"
                   class="px-4 py-1.5 text-sm font-medium rounded-full transition-all whitespace-nowrap {{ request()->routeIs('schreiben*') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                    Schreiben
                </a>
                <a href="{{ route('simulation.index') }}"
                   class="px-4 py-1.5 text-sm font-medium rounded-full transition-all whitespace-nowrap {{ request()->routeIs('simulation*') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                    اختبر نفسك
                </a>
                <a href="{{ route('plan') }}"
                   class="px-4 py-1.5 text-sm font-medium rounded-full transition-all whitespace-nowrap inline-flex items-center gap-1.5 {{ request()->routeIs('plan') ? 'bg-white/10 text-white shadow-sm border border-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-4"/><circle cx="12" cy="6" r="4"/><path d="M12 10v12"/></svg>
                    خطتي
                </a>
            </div>

            {{-- Right Side Actions --}}
            <div class="flex items-center gap-1.5 md:gap-2">

                {{-- Theme Toggle (vanilla JS — no Alpine dependency) --}}
                <button type="button" onclick="window.toggleTheme()" title="Toggle Theme"
                        class="theme-toggle-btn p-2 rounded-full border transition-all duration-300 bg-[#111216] border-white/[0.08] text-slate-400 hover:text-amber-400 hover:border-slate-600">
                    {{-- Sun icon — visible in DARK mode (click to go light) --}}
                    <svg class="theme-icon-sun w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2m-7.07-14.07 1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2m-4.34 5.66-1.41 1.41M6.34 6.34 4.93 4.93"/>
                    </svg>
                    {{-- Moon icon — visible in LIGHT mode (click to go dark) --}}
                    <svg class="theme-icon-moon w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                    </svg>
                </button>

                {{-- Font Settings --}}
                <div x-data="fontSettings()" x-init="init()" class="relative">
                    <button @click="open = !open" title="Font Size"
                            class="p-2 rounded-full border transition-all duration-300 bg-[#111216] border-white/[0.08] text-slate-400 hover:text-amber-400 hover:border-amber-500/40">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" :class="open && 'rotate-45'" style="transition: transform 0.5s">
                            <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition
                         class="absolute top-12 left-1/2 -translate-x-1/2 z-50 p-3 rounded-xl border shadow-xl bg-[#111216] border-white/10 min-w-[160px]">
                        <p class="text-[10px] uppercase font-bold text-slate-500 mb-2 text-center">حجم الخط</p>
                        <div class="flex items-center justify-between gap-2">
                            <button @click="decrease()" class="w-8 h-8 rounded-lg border border-white/10 text-slate-400 hover:text-white hover:bg-white/5 flex items-center justify-center text-lg font-bold">−</button>
                            <span class="text-sm font-bold text-white" x-text="Math.round(scale * 100) + '%'"></span>
                            <button @click="increase()" class="w-8 h-8 rounded-lg border border-white/10 text-slate-400 hover:text-white hover:bg-white/5 flex items-center justify-center text-lg font-bold">+</button>
                        </div>
                        <button @click="reset()" class="w-full mt-2 text-[10px] text-slate-500 hover:text-slate-300">إعادة تعيين</button>
                    </div>
                </div>

                {{-- Auth Buttons --}}
                <div class="hidden sm:flex items-center gap-2">
                    @auth
                        @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold px-2.5 py-1 rounded-md bg-amber-500/15 border border-amber-500/40 text-amber-200 hover:bg-amber-500/25 transition-colors">ADMIN</a>
                        @endif
                        @if(auth()->user()->pendingAccess())
                        <a href="{{ route('access.pending') }}" class="text-xs font-bold px-2.5 py-1 rounded-md bg-orange-500/15 border border-orange-500/40 text-orange-200 hover:bg-orange-500/25 transition-colors">طلب فالانتظار</a>
                        @endif
                        <a href="{{ route('profile') }}"
                           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-white/10 hover:border-amber-500/40 hover:bg-amber-500/10 text-slate-300 hover:text-white transition-all">
                            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-[11px] font-black">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                            <span class="text-sm font-medium max-w-[100px] truncate">{{ auth()->user()->name }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium transition-colors font-cairo text-slate-400 hover:text-white">تسجيل الدخول</a>
                        <a href="{{ route('register') }}" class="btn-shine inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-all font-cairo text-white bg-white/10 hover:bg-white/15 border border-white/10 hover:border-white/20">ابدأ الآن</a>
                    @endauth
                </div>

                {{-- Mobile Hamburger --}}
                <button @click="toggle()" class="lg:hidden relative w-11 h-11 flex items-center justify-center rounded-xl border transition-all duration-300 overflow-hidden active:scale-90 bg-white/5 border-white/10 text-white shadow-inner" aria-label="Open menu">
                    <div class="relative flex flex-col gap-1.5 items-end px-3">
                        <span class="h-0.5 rounded-full transition-all duration-300 bg-white" :class="open ? 'w-5 rotate-45 translate-y-2' : 'w-5'"></span>
                        <span class="h-0.5 rounded-full transition-all duration-300 bg-white" :class="open ? 'opacity-0 w-3' : 'w-3'"></span>
                        <span class="h-0.5 rounded-full transition-all duration-300 bg-white" :class="open ? 'w-5 -rotate-45 -translate-y-2' : 'w-4'"></span>
                    </div>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             class="lg:hidden border-t border-white/[0.08] bg-[#08090C]/95 backdrop-blur-lg px-4 py-4 flex flex-col gap-2">
            <a href="{{ route('lesen.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-colors">Lesen</a>
            <a href="{{ route('hoeren.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-colors">Hören</a>
            <a href="{{ route('schreiben.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-colors">Schreiben</a>
            <a href="{{ route('simulation.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-colors">اختبر نفسك</a>
            <a href="{{ route('plan') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-colors">خطتي</a>
            <div class="border-t border-white/[0.08] mt-2 pt-3 flex gap-3">
                @auth
                    <a href="{{ route('profile') }}" class="flex-1 text-center px-4 py-2.5 rounded-lg text-sm font-medium border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 transition-colors">بروفايلي</a>
                    @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="flex-1 text-center px-4 py-2.5 rounded-lg text-sm font-medium bg-amber-500/15 border border-amber-500/40 text-amber-200 transition-colors">Admin</a>
                    @else
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">@csrf<button class="w-full px-4 py-2.5 rounded-lg text-sm font-medium border border-red-500/20 text-red-300 hover:bg-red-500/10 transition-colors">خروج</button></form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="flex-1 text-center px-4 py-2.5 rounded-lg text-sm font-medium border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 transition-colors">تسجيل الدخول</a>
                    <a href="{{ route('register') }}" class="flex-1 text-center px-4 py-2.5 rounded-lg text-sm font-medium bg-amber-600 hover:bg-amber-500 text-white transition-colors">ابدأ الآن</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Page Content --}}
    <main class="relative pt-4">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="transition-colors duration-500 border-t pt-16 pb-8 font-cairo bg-[#08090C] border-white/[0.08]" dir="rtl">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-2">
                    <img src="{{ asset('images/lharba-logo.svg') }}"
                         alt="الهربة"
                         class="h-10 w-auto mb-4 drop-shadow-[0_0_15px_rgba(245,158,11,0.2)]"
                         loading="lazy">
                    <p class="text-sm max-w-xs text-slate-500">المعيار المهني المتكامل للتحضير لشهادات اللغة. خدّامين على الـ Telc, Goethe, ÖSD والمزيد.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-sm text-white">المنتج</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('billing') }}" class="hover:text-amber-400 transition-colors">الأسعار</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-sm text-white">تواصل معنا</h4>
                    <div class="flex gap-4">
                        <a href="https://wa.me/212720061881" target="_blank" rel="noopener noreferrer"
                           class="p-2.5 rounded-xl border transition-all duration-300 group bg-white/5 border-white/5 text-slate-400 hover:text-white hover:bg-[#25D366]"
                           aria-label="WhatsApp">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 group-hover:scale-110 transition-transform">
                                <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9L3 21"/>
                                <path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/[0.08] pt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs text-slate-600">© {{ date('Y') }} {{ config('app.name', 'الهربة') }}. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    {{-- Toast Container --}}
    <div class="fixed bottom-6 left-6 right-6 sm:left-auto sm:right-6 z-[100] flex flex-col gap-3 pointer-events-none" aria-live="polite" aria-atomic="true" id="toast-container"></div>

</div>

<script>
// Unified per-topic status (mastered / revise) across Lesen, Hören, Schreiben.
// Stored in localStorage under 'topic-status' = { lesen:{id:'mastered'|'revise'}, hoeren:{}, schreiben:{} }
window.TopicStatus = (function () {
    const KEY     = 'topic-status';
    const SKILLS  = ['lesen', 'hoeren', 'schreiben'];
    const MIG_KEY = 'topic-status-migrated-v1';

    function load() {
        try {
            const o = JSON.parse(localStorage.getItem(KEY) || '{}');
            for (const s of SKILLS) if (!o[s]) o[s] = {};
            return o;
        } catch (e) { return { lesen: {}, hoeren: {}, schreiben: {} }; }
    }
    function save(o) { try { localStorage.setItem(KEY, JSON.stringify(o)); } catch (e) {} }

    // One-time migration from old per-skill 5-star (≥4 → mastered, 1-3 → revise)
    function migrate() {
        if (localStorage.getItem(MIG_KEY)) return;
        const out = load();
        for (const skill of SKILLS) {
            try {
                const old = JSON.parse(localStorage.getItem(skill + '-mastery') || '{}');
                for (const [k, stars] of Object.entries(old)) {
                    const id = String(k).replace(skill + '-', '');
                    if (out[skill][id]) continue;
                    if (stars >= 4)      out[skill][id] = 'mastered';
                    else if (stars >= 1) out[skill][id] = 'revise';
                }
            } catch (e) {}
        }
        save(out);
        localStorage.setItem(MIG_KEY, '1');
    }

    function get(skill, id)        { return load()[skill]?.[String(id)] || null; }
    function set(skill, id, value) {
        const o = load();
        if (value) o[skill][String(id)] = value;
        else delete o[skill][String(id)];
        save(o);
        window.dispatchEvent(new CustomEvent('topic-status-changed', { detail: { skill, id, value } }));
    }
    function toggle(skill, id, value) { set(skill, id, get(skill, id) === value ? null : value); }
    function all() { return load(); }
    function counts(skill, allIds) {
        const map = load()[skill] || {};
        let mastered = 0, revise = 0;
        for (const id of allIds) {
            const v = map[String(id)];
            if (v === 'mastered') mastered++;
            else if (v === 'revise') revise++;
        }
        return { mastered, revise, untouched: allIds.length - mastered - revise, total: allIds.length };
    }

    migrate();
    return { get, set, toggle, all, counts };
})();
</script>

@auth
{{-- ── Auto welcome overlay — pops up the moment admin approves the user's access request ── --}}
<div id="welcome-overlay-root" x-data="welcomeWatcher()" x-init="start()" x-cloak>
    <template x-if="data">
        <div class="fixed inset-0 z-[200] flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             @keydown.escape.window="dismiss()">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="dismiss()"></div>

            {{-- Card --}}
            <div class="relative z-10 w-full max-w-lg rounded-3xl border border-amber-500/40 bg-gradient-to-br from-[#111216] via-[#0d0e12] to-[#111216] overflow-hidden shadow-2xl shadow-amber-500/20"
                 x-transition:enter="transition ease-out duration-400 delay-100"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                {{-- Halo --}}
                <div class="absolute -top-24 -left-24 w-64 h-64 rounded-full bg-amber-500/20 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-64 h-64 rounded-full bg-orange-500/20 blur-3xl pointer-events-none"></div>

                <div class="relative p-6 md:p-8 text-center" dir="rtl">
                    {{-- Animated check + shuriken --}}
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-amber-500 to-orange-600 mb-5 shadow-xl shadow-amber-500/40 relative">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-white"><polyline points="20 6 9 17 4 12"/></svg>
                        <span class="absolute -top-1 -right-1 w-7 h-7 rounded-full bg-[#0d0e12] border-2 border-amber-400 flex items-center justify-center">
                            <svg width="14" height="14" viewBox="0 0 100 100" fill="currentColor" class="text-amber-300"><path d="M50 4 L57 38 L93 32 L66 56 L96 78 L60 70 L50 96 L40 70 L4 78 L34 56 L7 32 L43 38 Z"/></svg>
                        </span>
                    </div>

                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">🎉 مرحبا بيك فالهربة!</h2>
                    <p class="text-slate-400 text-sm md:text-base mb-5">طلبك تصادق عليه. دبا تقدر تبدا التحضير.</p>

                    {{-- Granted access pill --}}
                    <div class="inline-flex items-center flex-wrap gap-2 px-4 py-2.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 mb-6 max-w-full">
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400">الوصول</span>
                        <span class="text-sm font-bold text-white" x-text="data.language_label"></span>
                        <span class="text-slate-600">·</span>
                        <span class="text-sm font-bold text-amber-300" x-text="data.exam"></span>
                        <span class="text-slate-600">·</span>
                        <span class="text-sm font-bold text-orange-300" x-text="data.level"></span>
                    </div>

                    {{-- Quick tour --}}
                    <div class="grid grid-cols-2 gap-2 mb-6 text-right">
                        <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-400 mb-1">📚 Lesen / Hören</div>
                            <div class="text-xs text-slate-300 leading-snug">تمارين كاملة بكل الـ Teil</div>
                        </div>
                        <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-orange-400 mb-1">✍️ Schreiben AI</div>
                            <div class="text-xs text-slate-300 leading-snug">تصحيح بمعايير telc</div>
                        </div>
                        <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 mb-1">🎯 خطتك</div>
                            <div class="text-xs text-slate-300 leading-snug">تتبع شنو حفظت وشنو خاصك</div>
                        </div>
                        <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-400 mb-1">⚡ Beispiel Generator</div>
                            <div class="text-xs text-slate-300 leading-snug">نماذج Schreiben جاهزة</div>
                        </div>
                    </div>

                    {{-- CTAs --}}
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button @click="goToContent()" class="flex-1 px-5 py-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white font-bold text-sm shadow-lg shadow-amber-500/30 hover:shadow-xl active:scale-95 transition-all">
                            ابدا الدراسة دبا
                        </button>
                        <button @click="dismiss()" class="px-5 py-3 rounded-xl border border-white/10 text-slate-400 hover:text-white hover:bg-white/5 text-sm transition-all">إغلاق</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function welcomeWatcher() {
    return {
        data: null,
        _interval: null,
        _lastPollAt: 0,
        _stopped: false,
        _csrf: document.querySelector('meta[name="csrf-token"]')?.content || '',
        // Tunables
        _intervalMs: 30000,   // poll every 30s while tab is visible
        _minGapMs:    8000,   // hard floor — never re-poll faster than this

        start() {
            // First poll, slight delay so we don't compete with the page's other on-load fetches.
            setTimeout(() => this._poll(), 1500);

            this._interval = setInterval(() => {
                if (this._stopped) return;
                if (document.visibilityState === 'visible') this._poll();
            }, this._intervalMs);

            // No burst on tab focus — only re-poll if it's been a while.
            document.addEventListener('visibilitychange', () => {
                if (this._stopped || this.data) return;
                if (document.visibilityState !== 'visible') return;
                if (Date.now() - this._lastPollAt < this._minGapMs) return;
                this._poll();
            });
        },

        async _poll() {
            if (this._stopped) return;
            // Hard rate-limit on the client too — guarantees we never burst.
            if (Date.now() - this._lastPollAt < this._minGapMs) return;
            this._lastPollAt = Date.now();

            try {
                const res = await fetch('{{ route('access.poll') }}', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                // If the server says "stop spamming me", back off completely until next page load.
                if (res.status === 429) { this._stop(); return; }
                if (!res.ok) return;
                const json = await res.json();
                if (json.welcome && json.request) {
                    this.data = json.request;
                    this._stop();
                }
            } catch (e) { /* network blip — try again next tick */ }
        },

        _stop() {
            this._stopped = true;
            if (this._interval) { clearInterval(this._interval); this._interval = null; }
        },

        async dismiss() {
            this.data = null;
            try {
                await fetch('{{ route('access.welcomed') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this._csrf,
                    },
                    credentials: 'same-origin',
                });
            } catch (e) {}
        },

        goToContent() {
            const target = '{{ route('lesen.index') }}';
            this.dismiss();
            // small delay so the welcomed POST fires before navigation
            setTimeout(() => { window.location.href = target; }, 100);
        },
    };
}
</script>
@endauth

@stack('scripts')
</body>
</html>
