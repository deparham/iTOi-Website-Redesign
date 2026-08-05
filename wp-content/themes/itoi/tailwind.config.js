/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './inc/**/*.php',
    './template-parts/**/*.php',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        // Base tokens — PROJECT.md §3
        bg: 'var(--bg)',
        'hero-bg': 'var(--hero-bg)',
        ink: 'var(--ink)',
        line: 'var(--line)',
        text: 'var(--text)',
        'text-muted': 'var(--text-muted)',
        cta: 'var(--cta)',
        'cta-hover': 'var(--cta-hover)',
        teal: {
          900: 'var(--teal-900)',
          800: 'var(--teal-800)',
          700: 'var(--teal-700)',
          500: 'var(--teal-500)',
        },
        // Signature tokens — ITOI's brand navy, the "Live Detection" layer's
        // color. `signature` is for light backgrounds; `signature-bright` is
        // the same hue lightened for dark backgrounds (hero scrim, teal-900
        // sections, dark modal chrome) where plain navy would be dark-on-dark.
        // Never an ordinary CTA/nav color outside the signature layer.
        signature: 'var(--signature)',
        'signature-dim': 'var(--signature-dim)',
        'signature-bright': 'var(--signature-bright)',
        'signature-glow': 'var(--signature-glow)',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      // Section-padding scale — 2026-07-27 visual consistency audit (see
      // NOTES.md). 3 tiers only, each a fluid clamp() var (src/tailwind.css
      // :root) so one class (e.g. `py-section-md`) replaces what used to be
      // a one-off arbitrary px value plus a separate min-[...]: breakpoint
      // override per section.
      spacing: {
        'section-sm': 'var(--space-section-sm)',
        'section-md': 'var(--space-section-md)',
        'section-lg': 'var(--space-section-lg)',
      },
    },
  },
  plugins: [],
};
