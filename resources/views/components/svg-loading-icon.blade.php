@props(['size' => '40', 'color' => '#2196f3', 'class' => ''])

<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 40 40"
  class="loading-spinner {{ $class }}" style="animation: spin 1s linear infinite;">
  <defs>
    <linearGradient id="loading-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{{ $color }};stop-opacity:0.3" />
      <stop offset="50%" style="stop-color:{{ $color }};stop-opacity:0.7" />
      <stop offset="100%" style="stop-color:{{ $color }};stop-opacity:1" />
    </linearGradient>
  </defs>

  <!-- Outer ring -->
  <circle cx="20" cy="20" r="18" fill="none" stroke="url(#loading-gradient)" stroke-width="3"
    stroke-linecap="round" stroke-dasharray="113.097" stroke-dashoffset="113.097"
    style="animation: loading-dash 1.5s ease-in-out infinite;" />

  <!-- Inner dots -->
  <circle cx="20" cy="8" r="2" fill="{{ $color }}" opacity="0.8">
    <animate attributeName="opacity" values="0.3;1;0.3" dur="1.5s" repeatCount="indefinite" begin="0s" />
  </circle>
  <circle cx="32" cy="20" r="2" fill="{{ $color }}" opacity="0.8">
    <animate attributeName="opacity" values="0.3;1;0.3" dur="1.5s" repeatCount="indefinite" begin="0.2s" />
  </circle>
  <circle cx="20" cy="32" r="2" fill="{{ $color }}" opacity="0.8">
    <animate attributeName="opacity" values="0.3;1;0.3" dur="1.5s" repeatCount="indefinite" begin="0.4s" />
  </circle>
  <circle cx="8" cy="20" r="2" fill="{{ $color }}" opacity="0.8">
    <animate attributeName="opacity" values="0.3;1;0.3" dur="1.5s" repeatCount="indefinite" begin="0.6s" />
  </circle>
</svg>

<style>
  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }

  @keyframes loading-dash {
    0% {
      stroke-dasharray: 1, 150;
      stroke-dashoffset: 0;
    }

    50% {
      stroke-dasharray: 90, 150;
      stroke-dashoffset: -35;
    }

    100% {
      stroke-dasharray: 90, 150;
      stroke-dashoffset: -124;
    }
  }

  .loading-spinner {
    display: inline-block;
  }
</style>
