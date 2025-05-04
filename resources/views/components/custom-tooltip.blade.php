@props([
    'title' => '',
    'placement' => 'top', // top, bottom, left, right
    'trigger' => 'hover', // hover, click
    'id' => 'x-tooltip-' . uniqid(),
])

<div x-tooltip id="{{ $id }}" class="x-tooltip" data-trigger="{{ $trigger }}" data-placement="{{ $placement }}" role="tooltip" aria-describedby="{{ $id }}-content">
    <div class="x-tooltip__trigger" tabindex="0">
        {{ $slot }}
    </div>
    <div class="x-tooltip__content" data-tooltip-id="{{ $id }}" id="{{ $id }}-content">
        {{ $title }}
    </div>
</div>

<style>
    :root {
        --tooltip-bg-start: #1e293b;
        --tooltip-bg-end: #334155;
        --tooltip-text: #f1f5f9;
        --tooltip-border: #475569;
        --tooltip-shadow: rgba(0, 0, 0, 0.2);
        --tooltip-radius: 8px;
        --tooltip-font: 'Vazirmatn', 'Inter', system-ui, sans-serif;
        --tooltip-transition: opacity 0.15s ease, transform 0.15s ease, visibility 0.15s ease;
    }

    .x-tooltip {
        position: relative;
        display: inline-block;
        font-family: var(--tooltip-font);
        isolation: isolate;
        z-index: 9999;
    }

    .x-tooltip__trigger {
        display: inline-block;
        cursor: pointer;
    }

    .x-tooltip__content {
        position: fixed;
        background: linear-gradient(135deg, var(--tooltip-bg-start) 0%, var(--tooltip-bg-end) 100%);
        color: var(--tooltip-text);
        padding: 0.5rem 0.75rem;
        border-radius: var(--tooltip-radius);
        border: 1px solid var(--tooltip-border);
        font-size: 0.85rem;
        font-weight: 400;
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
        z-index: 1000001;
        box-shadow: 0 6px 16px var(--tooltip-shadow);
        pointer-events: none;
    }

    .x-tooltip__content::before {
        content: '';
        position: absolute;
        width: 0;
        height: 0;
        border: 6px solid transparent;
        z-index: 1000002;
        transition: var(--tooltip-transition);
    }

    .x-tooltip[data-placement="top"] .x-tooltip__content {
        transform-origin: bottom;
    }

    .x-tooltip[data-placement="top"] .x-tooltip__content::before {
        border-top-color: var(--tooltip-bg-start);
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
    }

    .x-tooltip[data-placement="bottom"] .x-tooltip__content {
        transformйтесь-origin: top;
    }

    .x-tooltip[data-placement="bottom"] .x-tooltip__content::before {
        border-bottom-color: var(--tooltip-bg-start);
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
    }

    .x-tooltip[data-placement="left"] .x-tooltip__content {
        transform-origin: right;
    }

    .x-tooltip[data-placement="left"] .x-tooltip__content::before {
        border-left-color: var(--tooltip-bg-start);
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
    }

    .x-tooltip[data-placement="right"] .x-tooltip__content {
        transform-origin: left;
    }

    .x-tooltip[data-placement="right"] .x-tooltip__content::before {
        border-right-color: var(--tooltip-bg-start);
        right: 100%;
        top: 50%;
        transform: translateY(-50%);
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
        }
    }

    @media (max-width: 480px) {
        .x-tooltip__content {
            max-width: 120px;
            min-width: 80px;
            font-size: 0.75rem;
            padding: 0.3rem 0.5rem;
            min-height: 20px;
        }
    }
</style>

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
                    content.style.transition = 'none';
                    content.style.opacity = '0';
                    content.style.visibility = 'hidden';
                    content.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        content.style.transition = 'opacity 0.15s ease, transform 0.15s ease, visibility 0.15s ease';
                    }, 0);
                }
            });
        };

        const initializeTooltips = () => {
            const tooltips = document.querySelectorAll('[x-tooltip]:not([data-x-processed])');
            if (tooltips.length === 0) return;

            tooltips.forEach(tooltip => {
                const tooltipId = tooltip.id;
                if (processedTooltipIds.has(tooltipId)) return;

                const trigger = tooltip.querySelector('.x-tooltip__trigger');
                const content = tooltip.querySelector('.x-tooltip__content');
                let originalPlacement = tooltip.dataset.placement;

                if (!trigger || !content) {
                    console.error('Invalid tooltip structure:', { tooltipId, trigger, content });
                    return;
                }

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
                        originalPlacement === 'top' ? 'bottom' : originalPlacement === 'bottom' ? 'top' : originalPlacement === 'left' ? 'right' : 'left',
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

                let hoverTimeout;
                if (tooltip.dataset.trigger === 'hover') {
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
                            content.style.transition = 'none';
                            content.style.opacity = '0';
                            content.style.visibility = 'hidden';
                            content.style.transform = 'scale(0.95)';
                            setTimeout(() => {
                                content.style.transition = 'opacity 0.15s ease, transform 0.15s ease, visibility 0.15s ease';
                            }, 0);
                            tooltip.dataset.placement = originalPlacement;
                        }, 150);
                    });
                    content.addEventListener('mouseenter', () => clearTimeout(hoverTimeout));
                    content.addEventListener('mouseleave', () => {
                        hoverTimeout = setTimeout(() => {
                            content.style.transition = 'none';
                            content.style.opacity = '0';
                            content.style.visibility = 'hidden';
                            content.style.transform = 'scale(0.95)';
                            setTimeout(() => {
                                content.style.transition = 'opacity 0.15s ease, transform 0.15s ease, visibility 0.15s ease';
                            }, 0);
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

                // Ensure re-initialization on Livewire updates
                if (window.Livewire) {
                    window.Livewire.hook('element.updated', () => {
                        if (!tooltip.dataset.xProcessed) {
                            initializeTooltips();
                        }
                    });
                }
            });
        };

        document.addEventListener('DOMContentLoaded', initializeTooltips);

        const observer = new MutationObserver(mutations => {
            let hasNewTooltips = false;
            mutations.forEach(mutation => {
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === Node.ELEMENT_NODE && (node.matches('[x-tooltip]:not([data-x-processed])') || node.querySelector('[x-tooltip]:not([data-x-processed])'))) {
                            hasNewTooltips = true;
                        }
                    });
                }
                if (mutation.removedNodes.length) {
                    mutation.removedNodes.forEach(node => {
                        if (node.nodeType === Node.ELEMENT_NODE && node.id && processedTooltipIds.has(node.id)) {
                            processedTooltipIds.delete(node.id);
                            const content = document.body.querySelector(`.x-tooltip__content[data-tooltip-id="${node.id}"]`);
                            if (content) content.remove();
                        }
                    });
                }
            });
            if (hasNewTooltips) {
                initializeTooltips();
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });

        document.addEventListener('click', e => {
            document.querySelectorAll('.x-tooltip--active').forEach(tooltip => {
                if (!tooltip.contains(e.target)) {
                    tooltip.classList.remove('x-tooltip--active');
                    const content = document.body.querySelector(`.x-tooltip__content[data-tooltip回復-id="${tooltip.id}"]`);
                    if (content) {
                        content.style.transition = 'none';
                        content.style.opacity = '0';
                        content.style.visibility = 'hidden';
                        content.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            content.style.transition = 'opacity 0.15s ease, transform 0.15s ease, visibility 0.15s ease';
                        }, 0);
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
        window.addEventListener('scroll', debounce(closeAllTooltips, 100), { passive: true });
    }
</script>