/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    darkMode: "class", // فعال کردن دارک مود با کلاس
    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: "#2e86c1",
                    light: "#84caf9",
                    50: "#f0f9ff",
                    100: "#e0f2fe",
                    200: "#bae6fd",
                    300: "#7dd3fc",
                    400: "#38bdf8",
                    500: "#0ea5e9",
                    600: "#0284c7",
                    700: "#0369a1",
                    800: "#075985",
                    900: "#0c4a6e",
                },
                secondary: {
                    DEFAULT: "#1deb3c",
                    hover: "#15802a",
                    50: "#f8fafc",
                    100: "#f1f5f9",
                    200: "#e2e8f0",
                    300: "#cbd5e1",
                    400: "#94a3b8",
                    500: "#64748b",
                    600: "#475569",
                    700: "#334155",
                    800: "#1e293b",
                    900: "#0f172a",
                },
                background: {
                    light: "#f0f8ff",
                    footer: "#d4ecfd",
                    card: "#ffffff",
                },
                text: {
                    primary: "#000000",
                    secondary: "#707070",
                    discount: "#008000",
                    original: "#ff0000",
                },
                border: {
                    neutral: "#e5e7eb",
                },
                shadow: "rgba(0, 0, 0, 0.35)",
                instagram: {
                    from: "#f92ca7",
                    to: "#6b1a93",
                },
                button: {
                    mobile: "#4f9acd",
                    "mobile-light": "#a2cdeb",
                },
                support: {
                    section: "#2e86c1",
                    text: "#084d7c",
                },
                dark: {
                    primary: "#1a1a1a",
                    secondary: "#2d2d2d",
                    accent: "#3d3d3d",
                    text: {
                        primary: "#ffffff",
                        secondary: "#a0aec0",
                    },
                    border: "#4a5568",
                },
            },
            backgroundColor: {
                dark: {
                    primary: "var(--bg-primary)",
                    secondary: "var(--bg-secondary)",
                },
            },
            textColor: {
                dark: {
                    primary: "var(--text-primary)",
                    secondary: "var(--text-secondary)",
                },
            },
            borderColor: {
                dark: {
                    DEFAULT: "var(--border-color)",
                },
            },
            borderRadius: {
                button: "0.5rem",
                "button-large": "1rem",
                "button-xl": "1.25rem",
                card: "1.125rem",
                footer: "1.875rem",
                nav: "1.25rem",
                circle: "9999px",
            },
        },
    },
    plugins: [],
};
