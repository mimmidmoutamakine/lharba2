/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
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
