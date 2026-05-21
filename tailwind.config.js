/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: ['./**/*.php', './slice/**/*.html'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"IBM Plex Sans Arabic"', '"Noto Sans Arabic"', 'system-ui', 'sans-serif'],
        display: ['Tajawal', '"Reem Kufi"', '"IBM Plex Sans Arabic"', 'system-ui', 'sans-serif'],
        editorial: ['Amiri', '"Aref Ruqaa"', '"IBM Plex Serif"', 'Georgia', 'serif'],
        numeric: ['"IBM Plex Mono"', '"JetBrains Mono"', 'ui-monospace', 'monospace']
      },
      colors: {
        white: '#FAFBFE',
        black: '#030712',
        primary: {
          DEFAULT: '#C9A227',
          hover: '#B88F1E',
          tint: '#E6CB6A',
          border: '#D4C9A8',
          cream: '#F7F4ED',
        },
        secondary: '#8E2A2A',
        dark: '#0B0B0E',
        nocturnal: '#0B0B0E',
        midnight: '#16161B',
        'deep-shadow': '#020617',
        'screen-light': '#F8FAFC',
        'warm-ash': '#F1F5F9',
        mist: '#E2E8F0',
        pewter: '#94A3B8',
        charcoal: '#475569',
      },
      fontSize: {
        hero: ['clamp(2.75rem, 6vw, 5.5rem)', { lineHeight: '1.05', fontWeight: '800' }],
        display: ['clamp(2.25rem, 4.5vw, 4rem)', { lineHeight: '1.12', fontWeight: '700' }],
        headline: ['clamp(1.65rem, 2.6vw, 2.5rem)', { lineHeight: '1.25', fontWeight: '700' }],
        title: ['1.25rem', { lineHeight: '1.4', fontWeight: '700' }],
        body: ['1.125rem', { lineHeight: '1.85', fontWeight: '400' }],
        label: ['0.875rem', { lineHeight: '1.4', fontWeight: '600', letterSpacing: '0' }],
        caption: ['0.75rem', { lineHeight: '1.4', fontWeight: '500' }]
      }
    }
  },
  plugins: []
};
