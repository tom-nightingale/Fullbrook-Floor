module.exports = {
  mode: 'jit',
  content: ["./_views/**/*.twig", "./_components/**/*.twig", './safelist.txt'],
  theme: {
    screens: {
      xs : '480px',
      sm: '640px',
      md: '768px',
      lg: '1024px',
      xl: '1280px',
      "2xl": "1400px",
      "3xl": "1600px",
      "4xl": "1900px",
    },
    fontFamily: {
      sans: [
        'Mont',
        'sans-serif'
      ],
    },    
    filter: {
      'none': 'none',
      'grayscale': 'grayscale(1)',
    },
    extend: {
      colors: {
        primary: {
          light: '#CDE4F2',
          DEFAULT: '#84b8cd',
          dark: '#2a466c', 
          darkest: '#2d3f64',
        },
        secondary: {
          light: '#cd5480',
          DEFAULT: '#b70a4a',
          dark: '#800734',
        },
        tertiary: {
          light: '#cd5480',
          DEFAULT: '#b70a4a',
          dark: '#800734',
        },
      },
      spacing: {
        '27': '6.75rem',
        '36': '9rem',
        '72': '18rem',
        '84': '21rem',
        '96': '24rem',
        '128': '32rem',
        '160': '40rem',
      },
      inset: (theme, { negative }) => ({
        'full': '100%',
        ...theme('spacing'),
        ...negative(theme('spacing')),
      }),
      maxWidth: (theme) => ({
        ...theme('spacing'),
        ...theme('screens'),
      }),
      minHeight: (theme) => ({
        ...theme('spacing'),
        '25': '25vh',
        '50': '50vh',
        '75': '75vh',
      }),
      opacity: {
        '10': '0.1',
        '20': '0.2',
        '90': '.9',
        '95': '0.95',
      },
    },
  },
  plugins: [
    
  ],
  corePlugins: {
      container: false,
  },
}
