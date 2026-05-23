/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
  ],
  // Topic-tag badges use sky-* classes that aren't referenced elsewhere in the
  // codebase. Plus the light-theme overrides target sky-100/200, green-50..300
  // and other pale text colours — these need explicit safelist entries because
  // light-theme rewrites them in app.css but Tailwind only generates the base
  // classes if it sees them in source.
  safelist: [
    'bg-sky-500/10', 'bg-sky-500/15',
    'border-sky-400/50', 'border-sky-500/30',
    'text-sky-300', 'ring-sky-400/60',
    'accent-sky-500',
    // Pale text shades — used in topic-page filled blanks, success/fail pills.
    'text-amber-100', 'text-amber-50',
    'text-orange-100', 'text-orange-50',
    'text-emerald-100', 'text-emerald-50',
    'text-green-300', 'text-green-200', 'text-green-100', 'text-green-50',
    'text-red-100', 'text-red-50',
    'text-sky-200', 'text-sky-100',
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
