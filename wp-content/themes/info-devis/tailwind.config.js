/**
 * Configuration Tailwind pour le BUILD de production (remplace le CDN runtime).
 * Reprend à l'identique la config inline d'origine (idv_tailwind_config).
 * Les couleurs primaires restent pilotées par des variables CSS (--tw-primary…)
 * pour conserver la personnalisation « Apparence » (plan Gold).
 */
module.exports = {
  darkMode: 'class',
  content: [
    './**/*.php',
    './assets/js/**/*.js',
    '../../plugins/info-devis-core/**/*.php',
  ],
  // Classes construites dynamiquement en PHP (pastilles de statut) : à préserver.
  safelist: [
    {
      pattern: /(bg|text|border)-(amber|emerald|stone|red|green|sky|blue|yellow|purple|gray|orange|teal)-(50|100|200|300|400|500|600|700|800|900)/,
    },
  ],
  theme: {
    extend: {
      colors: {
        'primary':              'rgb(var(--tw-primary, 32 119 82) / <alpha-value>)',
        'primary-dk':           'rgb(var(--tw-primary-dk, 22 90 60) / <alpha-value>)',
        'primary-lt':           'rgb(var(--tw-primary-lt, 40 147 106) / <alpha-value>)',
        'primary-container':    'rgb(var(--tw-primary-container, 160 244 198) / <alpha-value>)',
        'on-primary':           'rgb(var(--tw-on-primary, 225 255 235) / <alpha-value>)',
        'on-primary-container': 'rgb(var(--tw-on-primary-container, 0 94 61) / <alpha-value>)',
        'secondary': '#57615c',
        'secondary-container': '#dae5de',
        'on-secondary-container': '#4a544f',
        'teal': '#157e90',
        'teal-dk': '#0f6474',
        'surface': '#faf9f8',
        'surface-container-low': '#f3f4f3',
        'surface-container': '#edeeed',
        'surface-container-high': '#e6e9e8',
        'surface-dim': '#d6dbda',
        'on-surface': '#2f3333',
        'on-surface-variant': '#5b605f',
        'outline': '#777c7b',
        'outline-variant': '#aeb3b2',
        'background': '#faf9f8',
        'on-background': '#2f3333',
        'error': '#9f403d',
        'gold': '#f0b429',
      },
      fontFamily: {
        headline: ['Newsreader', 'serif'],
        body: ['Manrope', 'sans-serif'],
        label: ['Manrope', 'sans-serif'],
      },
      borderRadius: {
        DEFAULT: '0.125rem',
        lg: '0.5rem',
        xl: '1rem',
        '2xl': '1.5rem',
        full: '9999px',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/container-queries'),
  ],
};
