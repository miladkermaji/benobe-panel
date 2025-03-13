<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'خطا - به نوبه' }}</title>
     @vite(['resources/js/app.js', 'resources/css/app.css'])
    <style>
        @import url('{{ asset('app-assets/fonts/vazir/font-face.css') }}');

        body {
            font-family: 'Vazir', sans-serif;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        }

        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .error-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
            max-width: 28rem;
        }

        .error-icon {
            font-size: 4rem;
            color: #3b82f6;
        }

        .btn-home {
            background-color: #3b82f6;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.3s;
        }

        .btn-home:hover {
            background-color: #2563eb;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-card">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">{{ $code }}</h1>
            <p class="text-lg text-gray-600 mb-6">{{ $message }}</p>
            @yield('content')
            <a style="text-decoration: none" href="/" class="btn-home inline-block mt-4">برگشت به صفحه اصلی</a>
        </div>
    </div>
</body>

</html>