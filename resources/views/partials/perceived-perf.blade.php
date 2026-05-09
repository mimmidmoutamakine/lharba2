{{-- ─────────────────────────────────────────────────────────────────
  Perceived-performance polish — three layers in one partial:
    1. Top progress bar that animates during navigations + form submits.
    2. Hover/touch prefetch so the next page is already cached on click.
    3. Subtle page fade-in on each navigation (respects prefers-reduced-motion).
  All vanilla JS / CSS, no extra deps. Safe to include once near top of <body>.
───────────────────────────────────────────────────────────────── --}}

<style>
    #lharba-progress {
        position: fixed; top: 0; left: 0; right: 0;
        height: 2px;
        z-index: 9999;
        pointer-events: none;
        background: transparent;
        opacity: 0;
        transition: opacity 220ms ease;
    }
    #lharba-progress.is-active { opacity: 1; }
    #lharba-progress::before {
        content: '';
        display: block;
        height: 100%;
        width: var(--lharba-progress, 0%);
        background: linear-gradient(90deg, #f59e0b 0%, #fb923c 50%, #f97316 100%);
        box-shadow: 0 0 10px rgba(245, 158, 11, 0.55), 0 0 4px rgba(245, 158, 11, 0.4);
        border-radius: 0 2px 2px 0;
        transition: width 260ms cubic-bezier(0.1, 0.5, 0.2, 1);
    }

    /* Page fade-in removed — was adding 180ms of perceived delay on every nav,
       compounding with content-render time on slow mobile. Pages now appear instantly. */
</style>

<div id="lharba-progress" aria-hidden="true"></div>

<script>
(function () {
    'use strict';

    // ── 1. Top progress bar ───────────────────────────────────────────
    const bar = document.getElementById('lharba-progress');
    let progress = 0;
    let timer = null;

    function setProgress(pct) {
        if (!bar) return;
        progress = pct;
        bar.style.setProperty('--lharba-progress', pct + '%');
    }
    function startBar() {
        if (!bar) return;
        bar.classList.add('is-active');
        setProgress(0);
        requestAnimationFrame(() => setProgress(20));
        clearInterval(timer);
        timer = setInterval(() => {
            // Asymptotic approach to ~80% so the bar always feels active.
            progress = Math.min(80, progress + (80 - progress) * 0.15);
            setProgress(progress);
        }, 250);
    }
    function endBar() {
        if (!bar) return;
        clearInterval(timer);
        setProgress(100);
        setTimeout(() => { bar.classList.remove('is-active'); setProgress(0); }, 220);
    }

    // Internal-link guard shared by progress + prefetch.
    function isInternalNavLink(a) {
        if (!a || !a.href) return false;
        if (a.target && a.target !== '_self') return false;
        if (a.hasAttribute('download')) return false;
        if (a.hasAttribute('data-no-prefetch')) return false;
        let u;
        try { u = new URL(a.href, location.href); } catch (e) { return false; }
        if (u.origin !== location.origin) return false;
        // Same path + only-fragment-changed → no real navigation.
        if (u.pathname === location.pathname && u.search === location.search && u.hash) return false;
        // Skip auth-mutating endpoints (logout, etc.) — never prefetchable.
        if (/^\/(logout)/.test(u.pathname)) return false;
        return true;
    }

    document.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!link || !isInternalNavLink(link)) return;
        if (e.defaultPrevented) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
        startBar();
    }, true);

    document.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;
        const form = e.target;
        // Skip forms explicitly opted-out (e.g. inline R/F submit Alpine handles client-side).
        if (form && form.hasAttribute('data-no-progress')) return;
        startBar();
    }, true);

    // BFCache restore — page reappearing after back/forward.
    window.addEventListener('pageshow', endBar);
    // Tab/window hidden mid-nav — drop the bar so it doesn't stick.
    window.addEventListener('pagehide', () => {
        clearInterval(timer);
        if (bar) bar.classList.remove('is-active');
    });

    // ── 2. Hover / touch prefetch ─────────────────────────────────────
    // Skip on Save-Data or slow connections — prefetching there hurts more than helps.
    const conn = navigator.connection;
    const slowConn = conn && (conn.saveData === true || /^(slow-2g|2g)$/.test(conn.effectiveType || ''));
    const prefetched = new Set();

    function prefetch(url) {
        if (slowConn || prefetched.has(url)) return;
        prefetched.add(url);
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        link.as = 'document';
        document.head.appendChild(link);
    }

    let hoverTimer = null;
    document.addEventListener('mouseover', function (e) {
        const a = e.target.closest && e.target.closest('a');
        if (!a || !isInternalNavLink(a)) return;
        clearTimeout(hoverTimer);
        // 80ms hover delay to skip incidental cursor sweeps.
        hoverTimer = setTimeout(() => prefetch(a.href), 80);
    }, { passive: true });
    document.addEventListener('mouseout', function () {
        clearTimeout(hoverTimer);
    }, { passive: true });

    // Touch — fire immediately on touchstart so the request races the click.
    document.addEventListener('touchstart', function (e) {
        const a = e.target.closest && e.target.closest('a');
        if (!a || !isInternalNavLink(a)) return;
        prefetch(a.href);
    }, { passive: true, capture: true });
})();
</script>
