@props(['name', 'id', 'title', 'size' => 'md'])

@php
  $sizes = [
      'sm' => 'xai-modal__dialog--sm',
      'md' => 'xai-modal__dialog--md',
      'lg' => 'xai-modal__dialog--lg',
      'xl' => 'xai-modal__dialog--xl',
  ];
  $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<style>
  .xai-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(17, 24, 39, 0.65);
    transition: opacity 0.2s ease-out;
  }

  .xai-modal-container {
    position: relative;
    background-color: #ffffff;
    border-radius: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    overflow-y: auto;
    max-height: calc(100vh - 3rem);
    transition: transform 0.2s ease-out, opacity 0.2s ease-out;
    transform: scale(0.95);
    opacity: 0;
  }

  [x-show="show"] .xai-modal-container {
    transform: scale(1);
    opacity: 1;
  }

  .xai-modal-container.xai-modal__dialog--sm {
    width: 400px !important;
  }

  .xai-modal-container.xai-modal__dialog--md {
    width: 520px !important;
  }

  .xai-modal-container.xai-modal__dialog--lg {
    width: 720px !important;
  }

  .xai-modal-container.xai-modal__dialog--xl {
    width: 1080px !important;
  }

  .xai-modal-header {
    padding: 0.75rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e5e7eb;
  }

  .xai-modal-title {
    font-size: 1rem;
    line-height: 2rem;
    font-weight: 600;
    color: #111827;
  }

  .xai-modal-close {
    position: relative;
    padding: 0.75rem;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 50%;
    transition: background-color 0.2s ease-out;
  }

  .xai-modal-close:hover {
    background-color: rgba(229, 231, 235, 0.25);
  }

  .xai-modal-close:hover .xai-modal-close-icon {
    color: #111827;
  }

  .xai-modal-close::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 50%;
    background-color: rgba(229, 231, 235, 0);
    transition: background-color 0.2s ease-out;
  }

  .xai-modal-close:hover::before {
    background-color: rgba(229, 231, 235, 0.25);
  }

  .xai-modal-close-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: #6b7280;
    transition: color 0.2s ease-out;
  }

  .xai-modal-body {
    padding: 2.25rem;
  }

  [x-cloak] {
    display: none;
  }
</style>

<div id="{{ $id ?? $name }}" x-data="{ show: false, name: '{{ $name }}' }" x-show="show" x-cloak
  x-on:open-modal.window="show = ($event.detail.name === name)" x-on:close-modal.window="show = false"
  x-on:keydown.escape.window="show = false" class="fixed z-[1000] inset-0 flex items-center justify-center"
  x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
  <div x-on:click="show = false" class="xai-modal-overlay"></div>
  <div class="xai-modal-container {{ $sizeClass }}">
    @if (isset($title))
      <div class="xai-modal-header">
        <div class="xai-modal-title">{{ $title }}</div>
        <button x-on:click="$dispatch('close-modal')" class="xai-modal-close">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="xai-modal-close-icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    @endif
    <div class="xai-modal-body">
      {{ $body }}
    </div>
  </div>
</div>
