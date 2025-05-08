@props([
    'title' => '',
    'placement' => 'top', // top, bottom, left, right
    'trigger' => 'hover', // hover, click
    'id' => 'x-tooltip-' . uniqid(),
])

<div x-tooltip id="{{ $id }}" class="x-tooltip" data-trigger="{{ $trigger }}"
  data-placement="{{ $placement }}" role="tooltip" aria-describedby="{{ $id }}-content">
  <div class="x-tooltip__trigger" tabindex="0">
    {{ $slot }}
  </div>
  <div class="x-tooltip__content" data-tooltip-id="{{ $id }}" id="{{ $id }}-content">
    {{ $title }}
  </div>
</div>

<script>
  if (!window.xTooltipInitialized) {
    window.xTooltipInitialized = true;
    const processedTooltipIds = new Set();

    const debounce = (func, wait) => {
      let timeout;
      return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
      };
    };

    const closeAllTooltips = () => {
      document.querySelectorAll('.x-tooltip--active').forEach(tooltip => {
        tooltip.classList.remove('x-tooltip--active');
        const content = document.body.querySelector(`.x-tooltip__content[data-tooltip-id="${tooltip.id}"]`);
        if (content) {
          content.style.opacity = '0';
          content.style.visibility = 'hidden';
          content.style.transform = 'scale(0.95)';
        }
      });
    };

    const initializeTooltips = () => {
      const tooltips = document.querySelectorAll('[x-tooltip]:not([data-x-processed])');
      tooltips.forEach(tooltip => {
        const tooltipId = tooltip.id;
        if (processedTooltipIds.has(tooltipId)) return;

        const trigger = tooltip.querySelector('.x-tooltip__trigger');
        const content = tooltip.querySelector('.x-tooltip__content');
        let originalPlacement = tooltip.dataset.placement;

        if (!trigger || !content) return;

        if (content.parentElement !== document.body) {
          document.body.appendChild(content);
        }

        tooltip.dataset.xProcessed = 'true';
        processedTooltipIds.add(tooltipId);

        const adjustPlacement = () => {
          const triggerRect = trigger.getBoundingClientRect();
          const contentRect = content.getBoundingClientRect();
          const viewportWidth = window.innerWidth;
          const viewportHeight = window.innerHeight;
          const margin = 12;
          const offset = 8;

          const space = {
            top: triggerRect.top - contentRect.height - margin,
            bottom: viewportHeight - triggerRect.bottom - contentRect.height - margin,
            left: triggerRect.left - contentRect.width - margin,
            right: viewportWidth - triggerRect.right - contentRect.width - margin
          };

          const placements = [
            originalPlacement,
            originalPlacement === 'top' ? 'bottom' : originalPlacement === 'bottom' ? 'top' :
            originalPlacement === 'left' ? 'right' : 'left',
            originalPlacement === 'top' || originalPlacement === 'bottom' ? 'right' : 'top',
            originalPlacement === 'top' || originalPlacement === 'bottom' ? 'left' : 'bottom'
          ];

          let bestPlacement = originalPlacement;
          for (const placement of placements) {
            if (
              (placement === 'top' && space.top > 0) ||
              (placement === 'bottom' && space.bottom > 0) ||
              (placement === 'left' && space.left > 0) ||
              (placement === 'right' && space.right > 0)
            ) {
              bestPlacement = placement;
              break;
            }
          }

          tooltip.dataset.placement = bestPlacement;

          let top, left;
          if (bestPlacement === 'top') {
            top = triggerRect.top - contentRect.height - offset;
            left = triggerRect.left + (triggerRect.width - contentRect.width) / 2;
          } else if (bestPlacement === 'bottom') {
            top = triggerRect.bottom + offset;
            left = triggerRect.left + (triggerRect.width - contentRect.width) / 2;
          } else if (bestPlacement === 'left') {
            top = triggerRect.top + (triggerRect.height - contentRect.height) / 2;
            left = triggerRect.left - contentRect.width - offset;
          } else if (bestPlacement === 'right') {
            top = triggerRect.top + (triggerRect.height - contentRect.height) / 2;
            left = triggerRect.right + offset;
          }

          content.style.top = `${top}px`;
          content.style.left = `${left}px`;

          if (left < margin) content.style.left = `${margin}px`;
          if (left + contentRect.width > viewportWidth - margin) {
            content.style.left = `${viewportWidth - contentRect.width - margin}px`;
          }
          if (top < margin) content.style.top = `${margin}px`;
          if (top + contentRect.height > viewportHeight - margin) {
            content.style.top = `${viewportHeight - contentRect.height - margin}px`;
          }
        };

        if (tooltip.dataset.trigger === 'hover') {
          let hoverTimeout;
          trigger.addEventListener('mouseenter', () => {
            clearTimeout(hoverTimeout);
            closeAllTooltips();
            content.style.opacity = '1';
            content.style.visibility = 'visible';
            content.style.transform = 'scale(1)';
            adjustPlacement();
          });
          trigger.addEventListener('mouseleave', () => {
            hoverTimeout = setTimeout(() => {
              content.style.opacity = '0';
              content.style.visibility = 'hidden';
              content.style.transform = 'scale(0.95)';
              tooltip.dataset.placement = originalPlacement;
            }, 150);
          });
          content.addEventListener('mouseenter', () => clearTimeout(hoverTimeout));
          content.addEventListener('mouseleave', () => {
            hoverTimeout = setTimeout(() => {
              content.style.opacity = '0';
              content.style.visibility = 'hidden';
              content.style.transform = 'scale(0.95)';
              tooltip.dataset.placement = originalPlacement;
            }, 150);
          });
        }

        if (tooltip.dataset.trigger === 'click') {
          trigger.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            const wasActive = tooltip.classList.contains('x-tooltip--active');
            closeAllTooltips();
            if (!wasActive) {
              tooltip.classList.add('x-tooltip--active');
              content.style.opacity = '1';
              content.style.visibility = 'visible';
              content.style.transform = 'scale(1)';
              adjustPlacement();
            }
          });
        }

        trigger.setAttribute('aria-describedby', `${tooltipId}-content`);
      });
    };

    document.addEventListener('DOMContentLoaded', initializeTooltips);

    const observer = new MutationObserver(mutations => {
      let hasNewTooltips = false;
      mutations.forEach(mutation => {
        if (mutation.addedNodes.length) {
          mutation.addedNodes.forEach(node => {
            if (node.nodeType === Node.ELEMENT_NODE && (node.matches(
                '[x-tooltip]:not([data-x-processed])') || node.querySelector(
                '[x-tooltip]:not([data-x-processed])'))) {
              hasNewTooltips = true;
            }
          });
        }
        if (mutation.removedNodes.length) {
          mutation.removedNodes.forEach(node => {
            if (node.nodeType === Node.ELEMENT_NODE && node.id && processedTooltipIds.has(node.id)) {
              processedTooltipIds.delete(node.id);
              const content = document.body.querySelector(
                `.x-tooltip__content[data-tooltip-id="${node.id}"]`);
              if (content) content.remove();
            }
          });
        }
      });
      if (hasNewTooltips) {
        initializeTooltips();
      }
    });
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });

    document.addEventListener('click', e => {
      document.querySelectorAll('.x-tooltip--active').forEach(tooltip => {
        if (!tooltip.contains(e.target)) {
          tooltip.classList.remove('x-tooltip--active');
          const content = document.body.querySelector(`.x-tooltip__content[data-tooltip-id="${tooltip.id}"]`);
          if (content) {
            content.style.opacity = '0';
            content.style.visibility = 'hidden';
            content.style.transform = 'scale(0.95)';
          }
        }
      });
    });

    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        closeAllTooltips();
      }
    });

    const handleResize = debounce(() => {
      document.querySelectorAll('.x-tooltip--active').forEach(tooltip => {
        const content = document.body.querySelector(`.x-tooltip__content[data-tooltip-id="${tooltip.id}"]`);
        if (content) {
          const trigger = tooltip.querySelector('.x-tooltip__trigger');
          if (trigger) {
            const evt = new Event('mouseenter');
            trigger.dispatchEvent(evt);
          }
        }
      });
    }, 100);

    window.addEventListener('resize', handleResize);
  }
</script>
<style>
  :root {
    --tooltip-bg: #1e293b;
    --tooltip-bg-light: #334155;
    --tooltip-text: #f1f5f9;
    --tooltip-border: #475569;
    --tooltip-shadow: rgba(0, 0, 0, 0.15);
    --tooltip-radius: 10px;
    --tooltip-font: 'Vazirmatn', 'Inter', system-ui, sans-serif;
    --tooltip-transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    --tooltip-arrow-size: 8px;
    --tooltip-arrow-color: #2e3b4e;
    /* رنگ متضاد برای فلش */
  }

  .x-tooltip {
    position: relative;
    display: inline-block;
    font-family: var(--tooltip-font);
    isolation: isolate;
    z-index: 1000;
  }

  .x-tooltip__trigger {
    display: inline-block;
    cursor: pointer;
  }

  .x-tooltip__content {
    position: fixed;
    background: linear-gradient(135deg, var(--tooltip-bg) 0%, var(--tooltip-bg-light) 100%);
    color: var(--tooltip-text);
    padding: 0.5rem 0.75rem;
    border-radius: var(--tooltip-radius);
    border: 1px solid var(--tooltip-border);
    font-size: 0.85rem;
    font-weight: 500;
    line-height: 1.5;
    max-width: 160px;
    min-width: 100px;
    min-height: 28px;
    text-align: center;
    white-space: normal;
    opacity: 0;
    visibility: hidden;
    transform: scale(0.95);
    transition: var(--tooltip-transition);
    z-index: 1001;
    box-shadow: 0 8px 24px var(--tooltip-shadow);
    pointer-events: none;
    backdrop-filter: blur(4px);
    contain: content;
  }

  .x-tooltip__content::before {
    content: '';
    position: absolute;
    width: 0;
    height: 0;
    border: var(--tooltip-arrow-size) solid transparent;
    z-index: 1002;
    box-sizing: border-box;
    pointer-events: none;
    display: block !important;
    /* برای دیباگ: اطمینان از دیده شدن */
    border-color: transparent !important;
    /* رنگ فلش به صورت دستی برای هر جهت */
  }

  .x-tooltip[data-placement="top"] .x-tooltip__content {
    transform-origin: bottom;
    margin-bottom: calc(var(--tooltip-arrow-size) + 2px);
  }

  .x-tooltip[data-placement="top"] .x-tooltip__content::before {
    border-top-color: var(--tooltip-arrow-color) !important;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    margin-top: -2px;
  }

  .x-tooltip[data-placement="bottom"] .x-tooltip__content {
    transform-origin: top;
    margin-top: calc(var(--tooltip-arrow-size) + 2px);
  }

  .x-tooltip[data-placement="bottom"] .x-tooltip__content::before {
    border-bottom-color: var(--tooltip-arrow-color) !important;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    margin-bottom: -2px;
  }

  .x-tooltip[data-placement="left"] .x-tooltip__content {
    transform-origin: right;
    margin-right: calc(var(--tooltip-arrow-size) + 2px);
  }

  .x-tooltip[data-placement="left"] .x-tooltip__content::before {
    border-left-color: var(--tooltip-arrow-color) !important;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    margin-left: -2px;
  }

  .x-tooltip[data-placement="right"] .x-tooltip__content {
    transform-origin: left;
    margin-left: calc(var(--tooltip-arrow-size) + 2px);
  }

  .x-tooltip[data-placement="right"] .x-tooltip__content::before {
    border-right-color: var(--tooltip-arrow-color) !important;
    right: 100%;
    top: 50%;
    transform: translateY(-50%);
    margin-right: -2px;
  }

  .x-tooltip[data-trigger="hover"]:hover .x-tooltip__content,
  .x-tooltip[data-trigger="click"].x-tooltip--active .x-tooltip__content {
    opacity: 1;
    visibility: visible;
    transform: scale(1);
  }

  @media (max-width: 768px) {
    .x-tooltip__content {
      max-width: 140px;
      min-width: 90px;
      font-size: 0.8rem;
      padding: 0.4rem 0.6rem;
      min-height: 24px;
      border-radius: 8px;
    }

    .x-tooltip__content::before {
      border-width: 6px;
    }

    .x-tooltip[data-placement="top"] .x-tooltip__content {
      margin-bottom: calc(6px + 2px);
    }

    .x-tooltip[data-placement="bottom"] .x-tooltip__content {
      margin-top: calc(6px + 2px);
    }

    .x-tooltip[data-placement="left"] .x-tooltip__content {
      margin-right: calc(6px + 2px);
    }

    .x-tooltip[data-placement="right"] .x-tooltip__content {
      margin-left: calc(6px + 2px);
    }
  }

  @media (max-width: 480px) {
    .x-tooltip__content {
      max-width: 120px;
      min-width: 80px;
      font-size: 0.75rem;
      padding: 0.3rem 0.5rem;
      min-height: 20px;
      border-radius: 6px;
    }

    .x-tooltip__content::before {
      border-width: 5px;
    }

    .x-tooltip[data-placement="top"] .x-tooltip__content {
      margin-bottom: calc(5px + 2px);
    }

    .x-tooltip[data-placement="bottom"] .x-tooltip__content {
      margin-top: calc(5px + 2px);
    }

    .x-tooltip[data-placement="left"] .x-tooltip__content {
      margin-right: calc(5px + 2px);
    }

    .x-tooltip[data-placement="right"] .x-tooltip__content {
      margin-left: calc(5px + 2px);
    }
  }
</style>
