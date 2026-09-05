/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eef5fb',
          100: '#d7e8f5',
          500: '#2E74B5',
          600: '#1F4E79',
          700: '#193f61',
        },
      },
    },
  },
  plugins: [],
};
