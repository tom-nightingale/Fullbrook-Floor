module.exports = {
  theme: {
    fontFamily: {
      sans: [
        'Source Sans Pro',
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
          light: '#53D2DF',
          default: '#0857A2',
          dark: '#0B426A', 
        },
        secondary: {
          light: '#f83',
          default: '#FF6A00',
          dark: '#c50',
        },
      },
      screens: {
        '2xl': '1530px',
        '3xl': '1800px',
      },
      spacing: {
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
    },
  },
  variants: {},
  plugins: [
    require('tailwindcss-filters'), // https://github.com/benface/tailwindcss-filters
    require('tailwindcss-gradients'), //https://github.com/benface/tailwindcss-gradients
  ],
  corePlugins: {
      container: false,
  },
}