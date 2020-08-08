module.exports = {
  theme: {
    screens: {
      xs : '480px',
      sm: '640px',
      md: '768px',
      lg: '1024px',
      xl: '1280px',
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
          default: '#84b8cd',
          dark: '#2a466c', 
          darkest: '#2d3748',
        },
        secondary: {
          light: '#f58c54',
          default: '#F26B24',
          dark: '#d6520d',
        },
      },
      screens: {
        '2xl': '1530px',
        '3xl': '1800px',
      },
      spacing: {
        '27': '6.75rem',
        '36': '9rem',
        '72': '18rem',
        '84': '21rem',
        '96': '24rem',
        '128': '32rem',
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
  variants: {
    boxShadow: ['responsive', 'hover', 'focus', 'group-hover'],
  },
  plugins: [
    require('tailwindcss-filters'), // https://github.com/benface/tailwindcss-filters
    require('tailwindcss-gradients'), //https://github.com/benface/tailwindcss-gradients
  ],
  corePlugins: {
      container: false,
  },
}