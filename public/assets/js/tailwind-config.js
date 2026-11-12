/**
 * PT Technodrives - Cash Advance System
 * Tailwind CSS Configuration
 */
tailwind.config = {
    theme: {
        extend: {
            colors: {
                brand: '#FF3C00',          // Official Brand Orange (action, hover, active, focus)
                brandalt: '#FF3B00',       // Primary button background
                brandhover: '#FF3D00',     // Hover & focus glow
                navy: '#050A1E',           // Primary Navy (headers, dark summary band, table headers)
                navy2: '#161A2B',          // Secondary Navy
                bodygrey: '#7A7A7A',       // Body text grey
                surface: '#F4F4F4',        // Light surface background
                cardborder: '#E1E1E1',     // Clean card border
                accent: '#FFC704',         // Yellow accent (PENDING_APPROVAL only)
                retail: {
                    orange: '#FF3C00',
                    navy: '#050A1E',
                    navy2: '#161A2B',
                    ink: '#050A1E',
                    darkInk: '#030612',
                    surface: '#E1E1E1',
                    body: '#7A7A7A',
                    bg: '#F4F4F4',
                }
            },
            fontFamily: {
                sans: ['"Fira Sans"', 'sans-serif'],
                head: ['"Poppins"', 'sans-serif'],
                body: ['"Fira Sans"', 'sans-serif'],
                mono: ['"Poppins"', 'monospace'], // Currency & technical amounts use bold Poppins
            },
            borderRadius: {
                'pill': '30px',
                'card': '15px',
                'input': '15px',
                'label': '20px',
                'sm': '6px',
                'DEFAULT': '10px',
                'md': '12px',
                'lg': '15px',
                'xl': '20px',
                '2xl': '30px',
            },
            boxShadow: {
                'header': '0 2px 5px -2px rgba(0, 0, 0, 0.1)',
                'submenu': '0px 1px 3px rgba(0, 0, 0, 0.3)',
                'card': '0 1px 3px 0 rgba(0, 0, 0, 0.05)',
                'focus': '0 0 4px rgba(255, 61, 0, 0.5)',
            },
            transitionDuration: {
                'brand': '500ms',
            }
        }
    }
};
