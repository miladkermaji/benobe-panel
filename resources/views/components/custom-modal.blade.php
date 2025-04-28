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
  .x-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1050;
  display: none;
  align-items: center;
  justify-content: center;
  overflow: auto;
  direction: rtl;
  font-family: 'Vazir', sans-serif;
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
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  opacity: 0;
  transition: opacity 0.4s ease, backdrop-filter 0.4s ease;
}

.x-modal--visible .x-modal__backdrop {
  opacity: 1;
}

.x-modal__dialog {
  position: relative;
  width: 100%;
  max-width: 500px;
  min-height: 200px;
  margin: 1rem;
  transform: translateY(100px) scale(0.9);
  opacity: 0;
  transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.5s ease, scale 0.5s ease;
}

.x-modal--visible .x-modal__dialog {
  transform: translateY(0) scale(1);
  opacity: 1;
}

.x-modal__dialog--sm {
  max-width: 440px;
}

.x-modal__dialog--md {
  max-width: 550px;
}

.x-modal__dialog--lg {
  max-width: 730px;
}

.x-modal__dialog--xl {
  max-width: 1140px;
}

.x-modal__content {
  background-color: #fff;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  transform: translateZ(0);
  min-height: 200px;
  display: flex;
  flex-direction: column;
}

.x-modal__header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(180deg, rgba(255, 255, 255, 1) 0%, rgba(245, 247, 250, 1) 100%);
}

.x-modal__title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.x-modal__close {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  transition: color 0.3s ease, transform 0.3s ease;
}

.x-modal__close:hover {
  color: #1f2937;
  transform: rotate(90deg);
}

.x-modal__close svg {
  width: 24px;
  height: 24px;
  transition: stroke 0.3s ease;
}

.x-modal__body {
  padding: 1.5rem;
  max-height: 70vh;
  overflow-y: auto;
  flex: 1;
}

@media (max-width: 640px) {
  .x-modal__dialog {
    margin: 0.5rem;
  }

  .x-modal__dialog--lg,
  .x-modal__dialog--xl {
    max-width: 95%;
  }

  .x-modal__content {
    min-height: 180px;
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
