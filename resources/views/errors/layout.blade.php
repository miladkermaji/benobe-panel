<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'خطا - به نوبه')</title>
  @vite(['resources/js/app.js', 'resources/css/app.css'])
  <!-- Lalezar for error code (فارسی فانتزی) -->
  <link href="https://cdn.fontcdn.ir/Font/Persian/Lalezar/Lalezar.css" rel="stylesheet">
  <style>
    @import url('{{ asset('app-assets/fonts/vazir/font-face.css') }}');

    body {
      font-family: 'Vazir', sans-serif;
      background: #f6fafd;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .error-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 100vw;
      min-height: 100vh;
      padding: 1.5rem;
    }

    .error-card {
      background: #fff;
      border-radius: 2rem;
      box-shadow: 0 8px 32px 0 rgba(44, 62, 80, 0.13), 0 1.5px 8px 0 rgba(46, 134, 193, 0.08);
      padding: 1.5rem 2.2rem 1.2rem 2.2rem;
      text-align: center;
      max-width: 420px;
      width: 100%;
      margin: 0 auto;
    }

    .error-icon {
      font-size: 3.8rem;
      color: #2E86C1;
      margin-bottom: 0.7rem;
      display: block;
      filter: drop-shadow(0 2px 8px #84caf977);
    }

    .error-code {
      font-size: 3.2rem;
      font-weight: 900;
      color: #222;
      margin-bottom: 0.4rem;
      letter-spacing: 2px;
      font-family: 'Lalezar', cursive;
      animation: errorCodePulse 1.5s infinite alternate;
      text-shadow: 0 2px 12px #2E86C133;
    }

    @keyframes errorCodePulse {
      0% {
        transform: scale(1) rotate(-2deg);
        color: #2E86C1;
        text-shadow: 0 2px 8px #84caf977;
      }

      50% {
        transform: scale(1.08) rotate(2deg);
        color: #1b4f72;
        text-shadow: 0 4px 16px #84caf955;
      }

      100% {
        transform: scale(1) rotate(-2deg);
        color: #2E86C1;
        text-shadow: 0 2px 8px #84caf977;
      }
    }

    .error-message {
      font-size: 1.25rem;
      font-weight: 800;
      color: #2E86C1;
      margin-bottom: 0.7rem;
      letter-spacing: 0.5px;
    }

    .error-content {
      font-size: 1.08rem;
      color: #666;
      margin-bottom: 1.2rem;
      line-height: 1.7;
    }

    .btn-home {
      display: inline-block;
      background: linear-gradient(90deg, #2E86C1 60%, #84CAF9 100%);
      color: #fff;
      padding: 0.7rem 2.2rem;
      border-radius: 1.1rem;
      text-decoration: none;
      font-size: 1.08rem;
      font-weight: 800;
      transition: background 0.2s, box-shadow 0.2s;
      box-shadow: 0 2px 12px rgba(44, 62, 80, 0.13);
      border: none;
    }

    .btn-home:hover {
      background: linear-gradient(90deg, #1b4f72 60%, #2E86C1 100%);
    }

    @media (max-width: 600px) {
      .error-card {
        padding: 1.5rem 0.5rem 1.2rem 0.5rem;
        max-width: 98vw;
      }

      .error-icon {
        font-size: 2.6rem;
      }

      .error-code {
        font-size: 1.2rem;
      }

      .error-message {
        font-size: 0.98rem;
      }

      .error-content {
        font-size: 0.93rem;
      }
    }
  </style>
</head>

<body>
  <div class="error-container">
    <div class="error-card">
      <span class="error-icon">@yield('icon')</span>
      <h1 class="error-code">@yield('code')</h1>
      <p class="error-message">@yield('message')</p>
      <div class="error-content">
        @yield('content')
      </div>
      <a href="/" class="btn-home">بازگشت به خانه</a>
    </div>
  </div>
</body>

</html>
