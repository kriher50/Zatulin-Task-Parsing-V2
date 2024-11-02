// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme'

export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#00b894',
                columns: '#ffffff',
                title: '#00886d',    //  <заголовки
                background: '#1e272e',
                highlight: '#00cec9',


            },
            fontFamily: {
                sans: ['Fig tree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
}
