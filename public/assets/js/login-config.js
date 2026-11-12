/**
 * PT Technodrives - Cash Advance System
 * Login Page Tailwind CSS Configuration
 */
tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Fira Sans"', 'system-ui', 'sans-serif'],
                heading: ['Poppins', 'sans-serif'],
                mono: ['"Fira Sans"', 'monospace'],
            },
            colors: {
                brand: {
                    DEFAULT: '#FF3C00',
                    alt: '#FF3B00',
                    hover: '#FF3D00',
                },
                navy: {
                    DEFAULT: '#050A1E',
                    light: '#161A2B',
                },
                surface: '#F4F4F4',
                bodygrey: '#7A7A7A',
                cardborder: '#E1E1E1',
                accentyellow: '#FFC704',
            }
        }
    }
};
