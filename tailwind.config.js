module.exports = {
  content: [
    'templates/**/*.html.twig',
    'src/Form/*',

  ],
  darkMode: 'class', // or 'media' or 'class'
  theme: {
    extend: {
      colors: {
        transparent: 'transparent', 
      },
    },
    fontFamily: {
      'title': ['"Roboto"'],
      'front': ['"Quicksand"'],
      'mono': ['"Roboto Mono"'],
      'stable': ['"Roboto"'],
      'stable-title': ['"Quicksand"'],
    }
  },  
  plugins: [
    require('@tailwindcss/typography'),
  ],
}