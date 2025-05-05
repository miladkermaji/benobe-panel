@props([
    'id' => 'custom-modal-' . uniqid(),
    'title' => null,
    'size' => 'md', // sm, md, lg, xl
    'show' => false,
    'closeOnBackdrop' => true,
])

<div x-modal id="{{ $id }}" class="x-modal {{ $show ? 'x-modal--visible' : '' }}"
  @if ($closeOnBackdrop) data-close-on-backdrop="true" @endif>
  <div class="x-modal__backdrop"></div>
  <div class="x-modal__dialog x-modal__dialog--{{ $size }}">
    <div class="x-modal__content">
      @if ($title)
        <div class="x-modal__header">
          <h2 class="x-modal__title">{{ $title }}</h2>
          <button type="button" class="x-modal__close" data-x-modal-close>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      @endif
      <div class="x-modal__body">
        {{ $slot }}
      </div>
    </div>
  </div>
</div>

<style>
  :root {
  --modal-bg: #ffffff;
  --modal-border: #e5e7eb;
  --modal-shadow: rgba(0, 0, 0, 0.15);
  --modal-backdrop: rgba(0, 0, 0, 0.65);
  --modal-text: #1f2937;
  --modal-text-secondary: #6b7280;
  --modal-accent: #2E86C1;
  --modal-accent-hover: #256d9b;
  --modal-radius: 16px;
  --modal-font: 'Vazirmatn', 'Inter', system-ui, sans-serif;
  --modal-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.x-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1000;
  display: none;
  align-items: center;
  justify-content: center;
  overflow: auto;
  direction: rtl;
  font-family: var(--modal-font);
  isolation: isolate;
}

.x-modal--visible {
  display: flex;
}

.x-modal__backdrop {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: var(--modal-backdrop);
  backdrop-filter: blur(4px);
  opacity: 0;
  transition: opacity 0.3s ease, backdrop-filter 0.3s ease;
}

.x-modal--visible .x-modal__backdrop {
  opacity: 1;
}

.x-modal__dialog {
  position: relative;
  width: 100%;
  max-width: 520px;
  margin: 1rem;
  transform: translateY(50px) scale(0.95);
  opacity: 0;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease, scale 0.3s ease;
}

.x-modal--visible .x-modal__dialog {
  transform: translateY(0) scale(1);
  opacity: 1;
}

.x-modal__dialog--sm {
  max-width: 400px;
}

.x-modal__dialog--md {
  max-width: 520px;
}

.x-modal__dialog--lg {
  max-width: 720px;
}

.x-modal__dialog--xl {
  max-width: 1080px;
}

.x-modal__content {
  background-color: var(--modal-bg);
  border-radius: var(--modal-radius);
  box-shadow: 0 10px 40px var(--modal-shadow);
  overflow: hidden;
  transform: translateZ(0);
  display: flex;
  flex-direction: column;
  min-height: 160px;
}

.x-modal__header {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--modal-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
}

.x-modal__title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--modal-text);
  margin: 0;
}

.x-modal__close {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--modal-text-secondary);
  transition: color 0.2s ease, transform 0.2s ease;
}

.x-modal__close:hover {
  color: var(--modal-text);
  transform: scale(1.1);
}

.x-modal__close svg {
  width: 20px;
  height: 20px;
  transition: stroke 0.2s ease;
}

.x-modal__body {
  padding: 1rem;
  flex: 1;
  overflow-y: auto;
  max-height: 80vh;
  scrollbar-width: thin;
  scrollbar-color: var(--modal-accent) var(--modal-border);
}

.x-modal__body::-webkit-scrollbar {
  width: 6px;
}

.x-modal__body::-webkit-scrollbar-track {
  background: var(--modal-border);
  border-radius: 8px;
}

.x-modal__body::-webkit-scrollbar-thumb {
  background-color: var(--modal-accent);
  border-radius: 8px;
  border: 1px solid var(--modal-border);
}

.x-modal__body::-webkit-scrollbar-thumb:hover {
  background-color: var(--modal-accent-hover);
}

@media (max-width: 768px) {
  .x-modal__dialog {
    margin: 0.75rem;
  }

  .x-modal__dialog--lg,
  .x-modal__dialog--xl {
    max-width: 92%;
  }

  .x-modal__content {
    min-height: 140px;
  }

  .x-modal__header {
    padding: 0.5rem 0.75rem;
  }

  .x-modal__title {
    font-size: 1rem;
  }

  .x-modal__body {
    padding: 0.75rem;
  }
}

@media (max-width: 480px) {
  .x-modal__dialog {
    margin: 0;
    width: 100%;
    max-width: 100%;
  }

  .x-modal__dialog--sm,
  .x-modal__dialog--md,
  .x-modal__dialog--lg,
  .x-modal__dialog--xl {
    max-width: 100%;
    width: 100%;
  }

  .x-modal__content {
    min-height: 120px;
    border-radius: var(--modal-radius) var(--modal-radius) 0 0;
  }

  .x-modal__header {
    padding: 0.5rem 0.75rem;
  }

  .x-modal__title {
    font-size: 0.875rem;
  }

  .x-modal__body {
    padding: 0.5rem;
  }

  .x-modal__close svg {
    width: 16px;
    height: 16px;
  }
}
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const modals = document.querySelectorAll('[x-modal]');

    modals.forEach(modal => {
      const closeButton = modal.querySelector('[data-x-modal-close]');
      const backdrop = modal.querySelector('.x-modal__backdrop');
      const content = modal.querySelector('.x-modal__content');
      const closeOnBackdrop = modal.dataset.closeOnBackdrop === 'true';

      // بستن مودال با کلیک روی دکمه بسته
      if (closeButton) {
        closeButton.addEventListener('click', (e) => {
          e.stopPropagation();
          modal.classList.remove('x-modal--visible');
        });
      }

      // بستن مودال فقط با کلیک روی پس‌زمینه
      if (backdrop && closeOnBackdrop) {
        backdrop.addEventListener('click', (e) => {
          if (e.target === backdrop) {
            modal.classList.remove('x-modal--visible');
          }
        });
      }

      // جلوگیری از انتشار رویداد کلیک در محتوای مودال
      if (content) {
        content.addEventListener('click', (e) => {
          e.stopPropagation();
        });
      }

      // جلوگیری از بسته شدن مودال هنگام تعامل با دیت‌پیکر و تکست‌اریا
      const inputs = modal.querySelectorAll('input, textarea, select');
      inputs.forEach(input => {
        input.addEventListener('click', (e) => {
          e.stopPropagation();
        });
        input.addEventListener('focus', (e) => {
          e.stopPropagation();
        });
        input.addEventListener('input', (e) => {
          e.stopPropagation();
        });
      });

      // بستن با کلید Escape
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('x-modal--visible')) {
          modal.classList.remove('x-modal--visible');
        }
      });
    });
  });

  // تابع عمومی برای باز کردن مودال
  window.openXModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('x-modal--visible');
    }
  };

  // تابع عمومی برای بستن مودال
  window.closeXModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('x-modal--visible');
    }
  };
</script>