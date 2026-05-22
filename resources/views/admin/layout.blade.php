<!DOCTYPE html>
<html lang="ar" class="scroll-smooth" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') – {{ config('app.name') }}</title>
    {{-- Cairo is self-hosted — see @font-face in resources/css/app.css. No external Google Fonts request. --}}
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/cairo/cairo-400.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/cairo/cairo-700.woff2') }}" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#08090C] text-slate-300 font-cairo antialiased min-h-screen">

<div class="flex min-h-screen">

    {{-- ── Sidebar ── --}}
    <aside class="w-64 shrink-0 border-l border-white/[0.08] bg-[#0B0C10] flex flex-col">
        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-white/[0.08]">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/lharba-logo.svg') }}" alt="الهربة" class="h-7 w-auto shrink-0" loading="lazy" decoding="async">
                <div class="border-r border-white/10 pr-2.5">
                    <div class="text-sm font-bold text-white leading-none">{{ config('app.name', 'الهربة') }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5 font-medium uppercase tracking-widest">Admin Panel</div>
                </div>
            </a>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @php
                $pendingAccessCount = \App\Models\AccessRequest::where('status', 'pending')->count();
                $navItems = [
                    ['route' => 'admin.dashboard',           'icon' => 'grid',       'label' => 'لوحة التحكم'],
                    ['route' => 'admin.access.index',        'icon' => 'key',        'label' => 'طلبات الوصول', 'badge' => $pendingAccessCount],
                    ['route' => 'admin.users.index',         'icon' => 'users',      'label' => 'المستخدمين'],
                    ['route' => 'admin.lesen.index',         'icon' => 'book',       'label' => 'Lesen (Telc)'],
                    ['route' => 'admin.hoeren.index',        'icon' => 'headphones', 'label' => 'Hören'],
                    ['route' => 'admin.schreiben.index',      'icon' => 'pencil',     'label' => 'Schreiben'],
                    ['route' => 'admin.goethe-b1.lesen.index','icon' => 'book',      'label' => 'Goethe B1 Lesen'],
                    ['route' => 'admin.mundlich.b2-planning.index','icon' => 'mic',  'label' => 'Telc B2 Mündlich · Planen'],
                    ['route' => 'admin.import.show',         'icon' => 'upload',     'label' => 'استيراد البيانات', 'param' => 'lesen'],
                ];
                $icons = [
                    'grid'       => '<path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>',
                    'book'       => '<path d="M12 7v14M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
                    'headphones' => '<path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/>',
                    'upload'     => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
                    'key'        => '<circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/>',
                    'users'      => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                    'mic'        => '<path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/>',
                    'pencil'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>',
                ];
            @endphp

            @foreach($navItems as $item)
            @php
                $href = isset($item['param'])
                    ? route($item['route'], $item['param'])
                    : route($item['route']);
                $active = request()->routeIs(rtrim($item['route'], '.index') . '*');
            @endphp
            <a href="{{ $href }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $active ? 'bg-amber-600/20 text-amber-300 border border-amber-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    {!! $icons[$item['icon']] !!}
                </svg>
                <span class="flex-1">{{ $item['label'] }}</span>
                @if(!empty($item['badge']))
                <span class="shrink-0 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-orange-500/30 text-orange-200 text-[10px] font-black">{{ $item['badge'] }}</span>
                @endif
            </a>
            @endforeach
        </nav>

        {{-- Footer --}}
        <div class="px-4 py-4 border-t border-white/[0.08]">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xs text-slate-500 hover:text-slate-300 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                العودة للموقع
            </a>
        </div>
    </aside>

    {{-- ── Main Content ── --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Top bar --}}
        <header class="h-16 border-b border-white/[0.08] flex items-center justify-between px-6 bg-[#08090C]/80 backdrop-blur-sm shrink-0">
            <h1 class="text-base font-bold text-white">@yield('page-title', 'لوحة التحكم')</h1>
            <div class="flex items-center gap-3 text-sm text-slate-400">
                <span>{{ auth()->user()->name ?? 'Admin' }}</span>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-6 mt-4 px-4 py-3 rounded-xl border text-sm bg-green-500/10 border-green-500/20 text-green-400 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mx-6 mt-4 px-4 py-3 rounded-xl border text-sm bg-red-500/10 border-red-500/20 text-red-400">
            {{ session('error') }}
        </div>
        @endif

        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
