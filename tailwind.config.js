/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
  ],
  // Topic-tag badges use sky-* classes that aren't referenced elsewhere in the
  // codebase. Without the safelist they'd be JIT-purged.
  safelist: [
    'bg-sky-500/10', 'bg-sky-500/15',
    'border-sky-400/50', 'border-sky-500/30',
    'text-sky-300', 'ring-sky-400/60',
    'accent-sky-500',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        background: '#08090C',
        surface: '#111216',
        primary: '#F59E0B',
        primaryDark: '#D97706',
        accent: '#F97316',
        accentDark: '#EA580C',
      },
      fontFamily: {
        cairo: ['Cairo', 'sans-serif'],
      },
      borderColor: {
        border: 'rgba(255,255,255,0.08)',
      },
    },
  },
  plugins: [],
}
