/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{astro,html,js,jsx,md,mdx,svelte,ts,tsx,vue}'],
  theme: {
    extend: {
      // カスタムカラーパレット
      colors: {
        // ベースカラー：柔らかい水色系
        primary: {
          50: '#f0fdff',
          100: '#ccf7fe',
          200: '#99effd',
          300: '#5ce2fa',
          400: '#22ccf0',
          500: '#09b0d6',
          600: '#0a8bb4',
          700: '#0f7092',
          800: '#155a77',
          900: '#174b65',
        },
        // アクセントカラー：濃い青・ネイビー
        accent: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
        },
        // グレー系
        neutral: {
          50: '#f8fafc',
          100: '#f1f5f9',
          200: '#e2e8f0',
          300: '#cbd5e1',
          400: '#94a3b8',
          500: '#64748b',
          600: '#475569',
          700: '#334155',
          800: '#1e293b',
          900: '#0f172a',
        }
      },
      // フォントファミリー
      fontFamily: {
        'noto': ['Noto Sans JP', 'sans-serif'],
        'mplus': ['M PLUS 1p', 'sans-serif'],
        'sans': ['Inter', 'system-ui', 'sans-serif'],
      },
      // アニメーション
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in-out',
        'slide-in': 'slideIn 0.5s ease-out',
        'float': 'float 6s ease-in-out infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideIn: {
          '0%': { transform: 'translateY(20px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        float: {
          '0%, 100%': { transform: 'translateY(0px)' },
          '50%': { transform: 'translateY(-20px)' },
        },
      },
    },
  },
  plugins: [],
};