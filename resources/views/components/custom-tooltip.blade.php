@props([
    'title' => '',
    'placement' => 'top', // top, bottom, left, right
    'trigger' => 'hover', // hover, click
    'id' => 'tooltip-' . uniqid(),
])

<div x-tooltip id="{{ $id }}" class="x-tooltip" data-trigger="{{ $trigger }}"
  data-placement="{{ $placement }}">
  <div class="x-tooltip__trigger">
    {{ $slot }}
  </div>
  <div class="x-tooltip__content">
    {{ $title }}
  </div>
</div>

<style>
  .x-tooltip {
    position: relative;
    display: inline-block;
    font-family: 'Vazir', sans-serif;
  }

  .x-tooltip__trigger {
    display: inline-block;
  }

  .x-tooltip__content {
    position: absolute;
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    color: #ffffff;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  }

  .x-tooltip__content::before {
    content: '';
    position: absolute;
    border: 6px solid transparent;
    z-index: 1001;
  }

  /* موقعیت‌های مختلف تولتیپ */
  .x-tooltip[data-placement="top"] .x-tooltip__content {
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(-10px);
    margin-bottom: 8px;
  }

  .x-tooltip[data-placement="top"] .x-tooltip__content::before {
    border-top-color: #1f2937;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
  }

  .x-tooltip[data-placement="bottom"] .x-tooltip__content {
    top: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(10px);
    margin-top: 8px;
  }

  .x-tooltip[data-placement="bottom"] .x-tooltip__content::before {
    border-bottom-color: #1f2937;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
  }

  .x-tooltip[data-placement="left"] .x-tooltip__content {
    right: 100%;
    top: 50%;
    transform: translateX(10px) translateY(-50%);
    margin-right: 8px;
  }

  .x-tooltip[data-placement="left"] .x-tooltip__content::before {
    border-left-color: #1f2937;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
  }

  .x-tooltip[data-placement="right"] .x-tooltip__content {
    left: 100%;
    top: 50%;
    transform: translateX(-10px) translateY(-50%);
    margin-left: 8px;
  }

  .x-tooltip[data-placement="right"] .x-tooltip__content::before {
    border-right-color: #1f2937;
    right: 100%;
    top: 50%;
    transform: translateY(-50%);
  }

  /* نمایش تولتیپ هنگام هاور */
  .x-tooltip[data-trigger="hover"]:hover .x-tooltip__content {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
    /* برای top/bottom */
  }

  .x-tooltip[data-trigger="hover"][data-placement="left"]:hover .x-tooltip__content,
  .x-tooltip[data-trigger="hover"][data-placement="right"]:hover .x-tooltip__content {
    transform: translateY(-50%) translateX(0);
  }

  /* نمایش تولتیپ هنگام کلیک */
  .x-tooltip[data-trigger="click"] .x-tooltip__content {
    opacity: 0;
    visibility: hidden;
  }

  .x-tooltip[data-trigger="click"].x-tooltip--active .x-tooltip__content {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
  }

  .x-tooltip[data-trigger="click"][data-placement="left"].x-tooltip--active .x-tooltip__content,
  .x-tooltip[data-trigger="click"][data-placement="right"].x-tooltip--active .x-tooltip__content {
    transform: translateY(-50%) translateX(0);
  }

  @media (max-width: 640px) {
    .x-tooltip__content {
      font-size: 0.75rem;
      padding: 0.4rem 0.8rem;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const tooltips = document.querySelectorAll('[x-tooltip][data-trigger="click"]');

    tooltips.forEach(tooltip => {
      const trigger = tooltip.querySelector('.x-tooltip__trigger');

      trigger.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const isActive = tooltip.classList.contains('x-tooltip--active');

        // بستن همه تولتیپ‌های دیگر
        document.querySelectorAll('.x-tooltip--active').forEach(otherTooltip => {
          otherTooltip.classList.remove('x-tooltip--active');
        });

        // تغییر وضعیت تولتیپ فعلی
        if (!isActive) {
          tooltip.classList.add('x-tooltip--active');
        }
      });
    });

    // بستن تولتیپ با کلیک خارج از آن
    document.addEventListener('click', (e) => {
      const activeTooltips = document.querySelectorAll('.x-tooltip--active');
      activeTooltips.forEach(tooltip => {
        if (!tooltip.contains(e.target)) {
          tooltip.classList.remove('x-tooltip--active');
        }
      });
    });

    // بستن تولتیپ با کلید Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        document.querySelectorAll('.x-tooltip--active').forEach(tooltip => {
          tooltip.classList.remove('x-tooltip--active');
        });
      }
    });
  });
</script>
