import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#E5252A', // Đỏ chủ đạo chuẩn Figma GearHub
                    hover: '#CC1E23',
                    50: '#FEF2F2',
                    100: '#FEE2E2',
                    200: '#FECACA',
                    300: '#FCA5A5',
                    400: '#F87171',
                    500: '#EF4444',
                    600: '#DC2626',
                    700: '#B91C1C',
                    800: '#991B1B',
                    900: '#7F1D1D',
                },
                secondary: {
                    DEFAULT: '#18181B', // Đen xám tối (Admin Sidebar, nút phụ)
                    50: '#FAFAFA',
                    100: '#F4F4F5',
                    200: '#E4E4E7',
                    300: '#D4D4D8',
                    400: '#A1A1AA',
                    500: '#71717A',
                    600: '#52525B',
                    700: '#3F3F46',
                    800: '#27272A',
                    900: '#18181B',
                },
                surface: {
                    DEFAULT: '#FFFFFF',
                    subtle: '#F4F5F7', // Màu nền thẻ card sản phẩm & box input trong Figma
                    muted: '#F8FAFC',  // Nền xám nhạt toàn trang
                },
                heading: '#09090B',
            },
        },
    },

    plugins: [forms],
};
