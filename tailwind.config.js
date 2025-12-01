/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/js/**/*.{vue,js,ts,jsx,tsx}',
        './resources/js/vendor/inertia-resource/**/*.{vue,js,ts,jsx,tsx}',
        './vendor/jacquestrdx123/vue3-admin-crud/resources/js/**/*.{vue,js,ts,jsx,tsx}',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#172554',
                },
            },
        },
    },
    plugins: [],
    darkMode: 'class',
}
