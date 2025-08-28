import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#4F46E5',
                    hover: '#4338CA',
                    focus: '#6366F1',
                },
                secondary: {
                    DEFAULT: '#FFFFFF',
                    hover: '#F9FAFB',
                    focus: '#F3F4F6',
                },
                danger: {
                    DEFAULT: '#DC2626',
                    hover: '#B91C1C',
                    focus: '#EF4444',
                },
                success: {
                    DEFAULT: '#16A34A',
                    hover: '#15803D',
                    focus: '#22C55E',
                },
                warning: {
                    DEFAULT: '#F59E0B',
                    hover: '#D97706',
                    focus: '#FBBF24',
                },
                light: '#F9FAFB',
                dark: '#1F2937',
            },
        },
    },

    plugins: [forms],
};
