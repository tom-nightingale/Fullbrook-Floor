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
          light: '#CDE4F2',
          default: '#84b8cd',
          dark: '#2a466c', 
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