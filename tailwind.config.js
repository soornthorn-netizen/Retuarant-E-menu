/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#2563EB',    // ពណ៌ចម្បង សម្រាប់ Buttons និង Navbar
        secondary: '#60A5FA',  // ពណ៌បន្ទាប់បន្សំ
        accent: '#F59E0B',     // ពណ៌ Highlight និង Badges
        background: '#F8FAFC', // ពណ៌ផ្ទៃខាងក្រោយ Website
      },
    },
  },
  plugins: [],
}

