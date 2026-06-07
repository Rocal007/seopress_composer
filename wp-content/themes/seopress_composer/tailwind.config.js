module.exports = {
  content: [
    './**/*.php',
    './inc/**/*.php',
    './template-parts/**/*.php',
    './assets/js/**/*.js',
    './assets/**/*.svg',  // Scan SVG files for icon classes
  ],
  safelist: [
    // Note: Add patterns here only if dynamically-generated class names are used
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['var(--font-body)', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        heading: ['var(--font-heading)', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        merriweather: ['"Merriweather"', 'serif'],
      },
      colors: {
        primary: 'var(--color-primary)',
        secondary: 'var(--color-secondary)',
        accent: 'var(--color-accent)',
        neutral: 'var(--color-neutral)',
        'background-primary': 'var(--color-background-primary)',
        info: 'var(--color-info)',
        success: 'var(--color-success)',
        warning: 'var(--color-warning)',
        danger: 'var(--color-danger)',
      }
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
    require('daisyui'),
  ],
  daisyui: {
    themes: false, // Disable DaisyUI themes to use our CSS variables
    // Disable unused themes to reduce CSS size
    darkTheme: false,
    base: true,
    styled: true,
    utils: true,
    logs: false,
  },
};
