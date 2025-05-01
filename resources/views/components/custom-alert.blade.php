<div x-alert id="{{ $id }}" class="x-alert x-alert--{{ $type }} {{ $show ? 'x-alert--visible' : '' }}"
  data-type="{{ $type }}" data-size="{{ $size }}" role="alert">
  <div class="x-alert__backdrop"></div>
  <div class="x-alert__dialog x-alert__dialog--{{ $size }}">
    <div class="x-alert__content">
      <div class="x-alert__body">
        @if ($title)
          <h2 class="x-alert__title">{{ $title }}</h2>
        @endif
        <p class="x-alert__message">{{ $message ?? '' }}</p> <!-- همیشه رندر می‌شه -->
      </div>
      <div class="x-alert__footer">
        <button type="button" class="x-alert__button x-alert__button--confirm" data-x-alert-close>
          {{ __('بله') }}
        </button>
        <button type="button" class="x-alert__button x-alert__button--cancel" data-x-alert-close>
          {{ __('خیر') }}
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  :root {
    /* Importing your color palette */
    --primary: #2E86C1;
    --primary-light: #84CAF9;
    --secondary: #1DEB3C;
    --secondary-hover: #15802A;
    --background-light: #F0F8FF;
    --background-card: #FFFFFF;
    --text-primary: #000000;
    --text-secondary: #707070;
    --text-discount: #008000;
    --text-original: #FF0000;
    --border-neutral: #E5E7EB;
    --shadow: rgba(0, 0, 0, 0.35);
    --gradient-primary: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
    --gradient-instagram: linear-gradient(135deg, var(--gradient-instagram-from) 0%, var(--gradient-instagram-to) 100%);
    --radius-button: 0.5rem;
    --radius-card: 1.125rem;
    --radius-circle: 9999px;
  }

  /* Base alert styles */
  .x-alert {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1060;
    display: none;
    align-items: center;
    justify-content: center;
    overflow: auto;
    font-family: 'Vazir', sans-serif;
    --webkit-font-smoothing: antialiased;
  }

  .x-alert--visible {
    display: flex;
  }

  .x-alert__backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(6px);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .x-alert--visible .x-alert__backdrop {
    opacity: 1;
  }

  .x-alert__dialog {
    position: relative;
    width: 100%;
    margin: 1rem;
    transform: scale(0.8);
    opacity: 0;
    transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55), opacity 0.3s ease;
  }

  .x-alert--visible .x-alert__dialog {
    transform: scale(1);
    opacity: 1;
  }

  .x-alert__dialog--sm {
    max-width: 360px;
  }

  .x-alert__dialog--md {
    max-width: 480px;
  }

  .x-alert__dialog--lg {
    max-width: 640px;
  }

  .x-alert__content {
    background: var(--background-card);
    border-radius: var(--radius-card);
    box-shadow: 0 12px 48px var(--shadow);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    border: 1px solid var(--border-neutral);
  }

  .x-alert__body {
    padding: 1.5rem;
    text-align: center;
  }

  .x-alert__title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 0.5rem;
  }

  .x-alert__message {
    font-size: 1rem;
    color: var(--text-secondary);
    margin: 0;
    font-weight: bold;
    line-height: 1.6;
  }

  .x-alert__footer {
    padding: 1rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
    background: var(--background-light);
  }

  .x-alert__button {
    flex: 1;
    padding: 0.75rem;
    border: none;
    border-radius: var(--radius-button);
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    max-width: 120px;
  }

  .x-alert__button--confirm {
    background: var(--gradient-primary);
    color: #fff;
  }

  .x-alert__button--confirm:hover {
    background: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }

  .x-alert__button--cancel {
    background: var(--text-original);
    color: #fff;
  }

  .x-alert__button--cancel:hover {
    background: #c13838;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }

  /* Type-specific styles */
  .x-alert--success .x-alert__content {
    border-left: 4px solid var(--secondary);
  }

  .x-alert--error .x-alert__content {
    border-left: 4px solid var(--text-original);
  }

  .x-alert--warning .x-alert__content {
    border-left: 4px solid #f59e0b;
  }

  .x-alert--info .x-alert__content {
    border-left: 4px solid var(--primary);
  }

  /* Responsive design */
  @media (max-width: 640px) {
    .x-alert__dialog {
      margin: 0.75rem;
    }

    .x-alert__dialog--lg {
      max-width: 90%;
    }

    .x-alert__title {
      font-size: 1.25rem;
    }

    .x-alert__message {
      font-size: 0.875rem;
    }

    .x-alert__button {
      padding: 0.5rem;
      font-size: 0.875rem;
      max-width: 100px;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('[x-alert]');

    alerts.forEach(alert => {
      const closeButtons = alert.querySelectorAll('[data-x-alert-close]');
      const backdrop = alert.querySelector('.x-alert__backdrop');
      const content = alert.querySelector('.x-alert__content');

      // Close alert on button clicks
      closeButtons.forEach(button => {
        button.addEventListener('click', (e) => {
          e.stopPropagation();
          alert.classList.remove('x-alert--visible');
        });
      });

      // Close alert on backdrop click
      if (backdrop) {
        backdrop.addEventListener('click', (e) => {
          if (e.target === backdrop) {
            alert.classList.remove('x-alert--visible');
          }
        });
      }

      // Prevent closing when clicking inside content
      if (content) {
        content.addEventListener('click', (e) => {
          e.stopPropagation();
        });
      }

      // Close with Escape key
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && alert.classList.contains('x-alert--visible')) {
          alert.classList.remove('x-alert--visible');
        }
      });
    });
  });

  // Global functions to open/close alert
  window.openXAlert = function(alertId) {
    const alert = document.getElementById(alertId);
    if (alert) {
      alert.classList.add('x-alert--visible');
    }
  };

  window.closeXAlert = function(alertId) {
    const alert = document.getElementById(alertId);
    if (alert) {
      alert.classList.remove('x-alert--visible');
    }
  };
</script>
