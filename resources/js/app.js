import Alpine from 'alpinejs';
import Collapse from '@alpinejs/collapse';
Alpine.plugin(Collapse);
window.Alpine = Alpine;

// Theme toggle
Alpine.data('theme', () => ({
    isDark: true,
    init() {
        const stored = localStorage.getItem('zertify-theme');
        this.isDark = stored !== 'light';
        this.apply();
    },
    toggle() {
        this.isDark = !this.isDark;
        localStorage.setItem('zertify-theme', this.isDark ? 'dark' : 'light');
        this.apply();
    },
    apply() {
        const html = document.documentElement;
        if (this.isDark) {
            html.classList.remove('light-theme');
            html.classList.add('dark');
        } else {
            html.classList.add('light-theme');
            html.classList.remove('dark');
        }
    }
}));

// Mobile menu
Alpine.data('mobileMenu', () => ({
    open: false,
    toggle() { this.open = !this.open; }
}));

// Font size
Alpine.data('fontSettings', () => ({
    open: false,
    scale: 1,
    init() {
        const stored = parseFloat(localStorage.getItem('zertify-font-scale') || '1');
        this.scale = stored;
        this.applyScale();
    },
    increase() { this.scale = Math.min(1.4, parseFloat((this.scale + 0.1).toFixed(1))); this.save(); },
    decrease() { this.scale = Math.max(0.8, parseFloat((this.scale - 0.1).toFixed(1))); this.save(); },
    reset()    { this.scale = 1; this.save(); },
    save() {
        localStorage.setItem('zertify-font-scale', this.scale);
        this.applyScale();
    },
    applyScale() {
        document.documentElement.style.setProperty('--app-font-size-scale', this.scale);
        document.documentElement.style.fontSize = (this.scale * 100) + '%';
    }
}));

Alpine.start();
