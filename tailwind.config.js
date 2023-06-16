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
    }
  },  
  plugins: [
    require('@tailwindcss/typography'),
  ],
}