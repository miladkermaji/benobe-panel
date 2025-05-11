<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'خطا - به نوبه')</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <style>
        @import url('{{ asset('app-assets/fonts/vazir/font-face.css') }}');

        :root {
            /* پالت رنگی */
            --primary: #2E86C1;
            --primary-light: #84CAF9;
            --secondary: #1DEB3C;
            --background-light: #F0F8FF;
            --background-card: #FFFFFF;
            --text-primary: #000000;
            --text-secondary: #707070;
            --border-neutral: #E5E7EB;
            --shadow: rgba(0, 0, 0, 0.35);
            --gradient-primary: linear-gradient(45deg, #84CAF9, #2E86C1);
            --radius-card: 1.5rem;
            --radius-button: 0.75rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Vazir', sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden; /* جلوگیری از اسکرول افقی */
            position: relative;
        }

        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Ccircle cx="20" cy="20" r="2" fill="%2384CAF9" opacity="0.5"/%3E%3Ccircle cx="80" cy="80" r="3" fill="%232E86C1" opacity="0.5"/%3E%3Ccircle cx="50" cy="30" r="2" fill="%232E86C1" opacity="0.5"/%3E%3C/svg%3E');
            animation: particleMove 10s linear infinite;
            z-index: -1;
        }

        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            width: 100%;
            max-width: 90vw; /* عرض نسبی */
            min-height: 50vh; /* حداقل ارتفاع */
            animation: zoomIn 0.6s ease-out;
        }

        .error-card {
            background: var(--background-card);
            border-radius: var(--radius-card);
            box-shadow: 0 8px 24px var(--shadow), 0 0 20px rgba(132, 202, 249, 0.3);
            padding: 1.5rem;
            text-align: center;
            width: 100%;
            max-width: 28rem; /* عرض حداکثر برای دسکتاپ */
            position: relative;
            overflow: hidden;
            border: 2px solid var(--primary-light);
            aspect-ratio: unset; /* حذف نسبت مربعی برای انعطاف‌پذیری بیشتر */
        }

        .error-icon {
            font-size: clamp(3rem, 10vw, 4.5rem); /* اندازه فونت نسبی */
            color: var(--primary);
            margin-bottom: 0.75rem;
            text-shadow: 0 0 8px var(--primary-light), 0 0 16px var(--primary);
            animation: glow 1.5s infinite ease-in-out;
        }

        .error-code {
            font-size: clamp(2.5rem, 8vw, 3.5rem);
            font-weight: 900;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 5px var(--shadow), 0 0 6px var(--primary-light);
            letter-spacing: 1.5px;
            animation: textPop 0.7s ease-out;
        }

        .error-message {
            font-size: clamp(1.2rem, 5vw, 1.5rem);
            font-weight: 800;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            text-shadow: 0 1px 3px var(--shadow);
        }

        .error-content {
            font-size: clamp(0.9rem, 4vw, 1.125rem);
            font-weight: 600;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient-primary);
            color: var(--background-card);
            padding: clamp(0.5rem, 2vw, 0.75rem) clamp(1rem, 4vw, 2rem);
            border-radius: var(--radius-button);
            text-decoration: none;
            font-size: clamp(0.9rem, 3.5vw, 1.125rem);
            font-weight: 700;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-home::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.4s;
        }

        .btn-home:hover::before {
            left: 100%;
        }

        .btn-home:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 16px var(--shadow);
            color: var(--background-card);
        }

        /* انیمیشن‌ها */
        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes glow {
            0% { text-shadow: 0 0 8px var(--primary-light), 0 0 16px var(--primary); }
            50% { text-shadow: 0 0 12px var(--primary-light), 0 0 24px var(--primary); }
            100% { text-shadow: 0 0 8px var(--primary-light), 0 0 16px var(--primary); }
        }

        @keyframes textPop {
            0% { transform: scale(0.5); opacity: 0; }
            70% { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(1); }
        }

        @keyframes particleMove {
            0% { background-position: 0 0; }
            100% { background-position: 80px 80px; }
        }

        /* مدیا کوئری‌ها */
        @media (max-width: 768px) {
            .error-container {
                padding: 0.75rem;
                max-width: 95vw;
            }

            .error-card {
                padding: 1.25rem;
                max-width: 90vw;
            }

            .error-icon {
                font-size: clamp(2.5rem, 8vw, 4rem);
                margin-bottom: 0.5rem;
            }

            .error-code {
                font-size: clamp(2rem, 7vw, 3rem);
            }

            .error-message {
                font-size: clamp(1rem, 4.5vw, 1.3rem);
            }

            .error-content {
                font-size: clamp(0.85rem, 3.5vw, 1rem);
            }

            .btn-home {
                padding: clamp(0.4rem, 1.5vw, 0.6rem) clamp(0.8rem, 3vw, 1.5rem);
                font-size: clamp(0.8rem, 3vw, 1rem);
            }
        }

        @media (max-width: 480px) {
            .error-container {
                padding: 0.5rem;
            }

            .error-card {
                padding: 1rem;
                max-width: 100vw;
                border-radius: 1rem;
            }

            .error-icon {
                font-size: clamp(2rem, 7vw, 3.5rem);
                margin-bottom: 0.4rem;
            }

            .error-code {
                font-size: clamp(1.8rem, 6vw, 2.5rem);
            }

            .error-message {
                font-size: clamp(0.9rem, 4vw, 1.2rem);
            }

            .error-content {
                font-size: clamp(0.8rem, 3vw, 0.9rem);
                line-height: 1.4;
            }

            .btn-home {
                padding: clamp(0.35rem, 1.2vw, 0.5rem) clamp(0.7rem, 2.5vw, 1.2rem);
                font-size: clamp(0.75rem, 2.8vw, 0.9rem);
            }
        }
    </style>
</head>
<body>
    <div class="particles"></div>
    <div class="error-container">
        <div class="error-card">
            <span class="error-icon">@yield('icon')</span>
            <h1 class="error-code">@yield('code')</h1>
            <p class="error-message">@yield('message')</p>
            <div class="error-content">
                @yield('content')
            </div>
            <a href="/" class="btn-home">برگشت به صفحه اصلی</a>
        </div>
    </div>
</body>
</html>