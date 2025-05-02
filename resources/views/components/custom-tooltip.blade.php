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
    <div class="x-tooltip__content" data-tooltip-id="{{ $id }}">
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
        position: fixed;
        background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
        color: #edf2f7;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 400;
        line-height: 1.4;
        width: 140px;
        min-height: 36px;
        text-align: center;
        white-space: normal;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        z-index: 100001;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .x-tooltip__content::before {
        content: '';
        position: absolute;
        border: 6px solid transparent;
        z-index: 100002;
    }

    .x-tooltip[data-placement="top"] .x-tooltip__content::before {
        border-top-color: #1a202c;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
    }

    .x-tooltip[data-placement="bottom"] .x-tooltip__content::before {
        border-bottom-color: #1a202c;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
    }

    .x-tooltip[data-placement="left"] .x-tooltip__content::before {
        border-left-color: #1a202c;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
    }

    .x-tooltip[data-placement="right"] .x-tooltip__content::before {
        border-right-color: #1a202c;
        right: 100%;
        top: 50%;
        transform: translateY(-50%);
    }

    .x-tooltip[data-trigger="hover"]:hover .x-tooltip__content,
    .x-tooltip[data-trigger="click"].x-tooltip--active .x-tooltip__content {
        opacity: 1;
        visibility: visible;
    }

    @media (max-width: 768px) {
        .x-tooltip__content {
            width: 120px;
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
            min-height: 32px;
        }
    }

    @media (max-width: 480px) {
        .x-tooltip__content {
            width: 100px;
            font-size: 0.75rem;
            padding: 0.3rem 0.5rem;
            min-height: 28px;
        }
    }
</style>

<script>
    if (!window.tooltipInitialized) {
        window.tooltipInitialized = true;
        const processedTooltipIds = new Set();

        const initializeTooltips = () => {
            const tooltips = document.querySelectorAll('[x-tooltip]:not([data-processed])');
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

                tooltip.dataset.processed = 'true';
                processedTooltipIds.add(tooltipId);

                const adjustPlacement = () => {
                    const triggerRect = trigger.getBoundingClientRect();
                    const contentRect = content.getBoundingClientRect();
                    const viewportWidth = window.innerWidth;
                    const viewportHeight = window.innerHeight;
                    const margin = 10;
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

                if (tooltip.dataset.trigger === 'click') {
                    trigger.addEventListener('click', e => {
                        e.preventDefault();
                        e.stopPropagation();
                        document.querySelectorAll('.x-tooltip--active').forEach(t => {
                            t.classList.remove('x-tooltip--active');
                            const tContent = document.body.querySelector(`.x-tooltip__content[data-tooltip-id="${t.id}"]`);
                            if (tContent) {
                                tContent.style.opacity = '0';
                                tContent.style.visibility = 'hidden';
                            }
                        });
                        tooltip.classList.toggle('x-tooltip--active');
                        if (tooltip.classList.contains('x-tooltip--active')) {
                            content.style.opacity = '1';
                            content.style.visibility = 'visible';
                            adjustPlacement();
                        }
                    });
                }

                if (tooltip.dataset.trigger === 'hover') {
                    trigger.addEventListener('mouseenter', () => {
                        content.style.opacity = '1';
                        content.style.visibility = 'visible';
                        adjustPlacement();
                    });
                    trigger.addEventListener('mouseleave', () => {
                        content.style.opacity = '0';
                        content.style.visibility = 'hidden';
                        tooltip.dataset.placement = originalPlacement;
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
                        if (node.nodeType === Node.ELEMENT_NODE && (node.matches('[x-tooltip]:not([data-processed])') || node.querySelector('[x-tooltip]:not([data-processed])'))) {
                            hasNewTooltips = true;
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
                    const content = document.body.querySelector(`.x-tooltip__content[data-tooltip-id="${tooltip.id}"]`);
                    if (content) {
                        content.style.opacity = '0';
                        content.style.visibility = 'hidden';
                    }
                }
            });
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.x-tooltip--active').forEach(tooltip => {
                    tooltip.classList.remove('x-tooltip--active');
                    const content = document.body.querySelector(`.x-tooltip__content[data-tooltip-id="${tooltip.id}"]`);
                    if (content) {
                        content.style.opacity = '0';
                        content.style.visibility = 'hidden';
                    }
                });
            }
        });

        window.addEventListener('resize', () => {
            document.querySelectorAll('.x-tooltip--active').forEach(tooltip => {
                const trigger = tooltip.querySelector('.x-tooltip__trigger');
                if (trigger) {
                    trigger.dispatchEvent(new Event('mouseenter'));
                }
            });
        });
    }
</script>