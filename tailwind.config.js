/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: ['./**/*.php'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"IBM Plex Sans Arabic"', 'sans-serif']
      },
      colors: {
        primary: '#D4AF37',
        secondary: '#E50914',
        dark: '#0F172A'
      }
    }
  },
  plugins: []
};
