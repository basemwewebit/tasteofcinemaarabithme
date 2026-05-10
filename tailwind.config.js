/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: ['./**/*.php', './slice/**/*.html'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"IBM Plex Sans Arabic"', 'sans-serif']
      },
      colors: {
        white: '#FAFBFE',
        black: '#030712',
        primary: {
          DEFAULT: '#D4AF37',
          hover: '#C5A028',
          tint: '#F8E4A1',
          border: '#F5E5A7',
          cream: '#FFF8E1',
        },
        secondary: '#E50914',
        dark: '#0F172A',
        nocturnal: '#0F172A',
        midnight: '#1E293B',
        'deep-shadow': '#020617',
        'screen-light': '#F8FAFC',
        'warm-ash': '#F1F5F9',
        mist: '#E2E8F0',
        pewter: '#94A3B8',
        charcoal: '#475569',
      },
      fontSize: {
        display: ['clamp(2.1rem, 4.5vw, 4.25rem)', { lineHeight: '1.08', fontWeight: '700' }],
        headline: ['clamp(1.5rem, 3vw, 2.25rem)', { lineHeight: '1.25', fontWeight: '700' }],
        title: ['1.25rem', { lineHeight: '1.4', fontWeight: '700' }],
        body: ['1.125rem', { lineHeight: '2', fontWeight: '400' }],
        label: ['0.875rem', { lineHeight: '1.4', fontWeight: '600', letterSpacing: '0.08em' }],
        caption: ['0.75rem', { lineHeight: '1.4', fontWeight: '500' }]
      }
    }
  },
  plugins: []
};
