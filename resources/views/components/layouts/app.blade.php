<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel') }}</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  <!-- Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Livewire Styles -->
  @livewireStyles

  <!-- Custom CSS for SVG Icons -->
  <style>
    /* SVG Icon Styles */
    .svg-icon {
      display: inline-block;
      vertical-align: middle;
    }

    .loading-spinner {
      display: inline-block;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    /* Fix for blocked resources */
    .icon-fallback {
      display: inline-block;
      background-color: currentColor;
      border-radius: 2px;
    }

    /* Search icon positioning */
    .search-icon {
      position: absolute;
      top: 50%;
      left: 12px;
      transform: translateY(-50%);
      color: #6c757d;
      z-index: 5;
    }

    /* Loading states */
    .loading-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(255, 255, 255, 0.8);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }

    /* Filter styles */
    .filter-controls {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      align-items: end;
    }

    .filter-group {
      flex: 1;
      min-width: 200px;
    }

    @media (max-width: 768px) {
      .filter-controls {
        flex-direction: column;
      }

      .filter-group {
        min-width: 100%;
      }
    }
  </style>
</head>

<body class="font-sans antialiased">
  <div class="min-h-screen bg-gray-100">
    <!-- Global Loader -->
    <x-global-loader />

    <!-- Page Content -->
    {{ $slot }}
  </div>

  <!-- Livewire Scripts -->
  @livewireScripts

  <!-- Alpine.js -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Custom JavaScript for Filters -->
  <script>
    // Global filter functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize all filter components
      initializeFilters();

      // Handle Livewire updates
      document.addEventListener('livewire:update', function() {
        initializeFilters();
      });
    });

    function initializeFilters() {
      // Add loading states to filter inputs
      document.querySelectorAll('[wire\\:model\\.live]').forEach(input => {
        input.addEventListener('input', function() {
          showFilterLoading(this);
        });

        input.addEventListener('change', function() {
          hideFilterLoading(this);
        });
      });

      // Add loading states to filter selects
      document.querySelectorAll('select[wire\\:model\\.live]').forEach(select => {
        select.addEventListener('change', function() {
          showFilterLoading(this);
          setTimeout(() => hideFilterLoading(this), 500);
        });
      });
    }

    function showFilterLoading(element) {
      const container = element.closest('.filter-group, .search-box');
      if (container && !container.querySelector('.loading-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<x-svg-loading-icon size="24" color="#007bff" />';
        container.style.position = 'relative';
        container.appendChild(overlay);
      }
    }

    function hideFilterLoading(element) {
      const container = element.closest('.filter-group, .search-box');
      if (container) {
        const overlay = container.querySelector('.loading-overlay');
        if (overlay) {
          overlay.remove();
        }
      }
    }

    // Global error handler for blocked resources
    window.addEventListener('error', function(e) {
      if (e.target.tagName === 'IMG' || e.target.tagName === 'LINK') {
        console.warn('Resource blocked:', e.target.src || e.target.href);
        // Replace blocked images with fallback
        if (e.target.tagName === 'IMG') {
          e.target.style.display = 'none';
          const fallback = document.createElement('div');
          fallback.className = 'icon-fallback';
          fallback.style.width = e.target.width + 'px';
          fallback.style.height = e.target.height + 'px';
          e.target.parentNode.insertBefore(fallback, e.target);
        }
      }
    }, true);
  </script>
</body>

</html>
