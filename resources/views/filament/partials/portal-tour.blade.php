@php
    $portal = $portal ?? 'student';
    $tourService = app(\App\Services\PortalTourService::class);
    $steps = $tourService->getStepsForPortal($portal);
    $user = auth()->user();
    $hasCompleted = $user ? $user->hasCompletedTour($portal) : false;
@endphp

@once
<link rel="stylesheet" href="{{ asset('vendor/driverjs/driver.css') }}">
<script src="{{ asset('vendor/driverjs/driver.js') }}"></script>
<style>
    /* Thinker HUB Theme for Driver.js Popovers (Zero Emojis, Pure SVG/Clean Typography) */
    .driver-popover {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        background: #ffffff !important;
        color: #0f172a !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 1rem !important;
        padding: 1.25rem !important;
        min-width: 280px !important;
        max-width: 360px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        z-index: 1000000000 !important;
    }

    .dark .driver-popover {
        background: #0f172a !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
    }

    .driver-popover-title {
        font-size: 1rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        padding-right: 1.75rem !important;
        line-height: 1.4 !important;
    }

    .dark .driver-popover-title {
        color: #f8fafc !important;
    }

    .driver-popover-description {
        font-size: 0.875rem !important;
        color: #475569 !important;
        line-height: 1.55 !important;
        margin-top: 0.5rem !important;
    }

    .dark .driver-popover-description {
        color: #cbd5e1 !important;
    }

    .driver-popover-close-btn {
        color: #94a3b8 !important;
        top: 0.75rem !important;
        right: 0.75rem !important;
        width: 1.75rem !important;
        height: 1.75rem !important;
        font-size: 1.35rem !important;
        line-height: 1 !important;
        border-radius: 0.5rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: color 0.15s ease, background-color 0.15s ease !important;
    }

    .driver-popover-close-btn:hover {
        color: #0f172a !important;
        background-color: #f1f5f9 !important;
    }

    .dark .driver-popover-close-btn:hover {
        color: #f8fafc !important;
        background-color: #1e293b !important;
    }

    .driver-popover-progress-text {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        color: #0d9488 !important;
        letter-spacing: 0.025em !important;
    }

    .dark .driver-popover-progress-text {
        color: #2dd4bf !important;
    }

    .driver-popover-footer {
        margin-top: 1.15rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }

    .driver-popover-navigation-btns {
        display: inline-flex !important;
        gap: 0.5rem !important;
    }

    .driver-popover-footer-btn {
        font-family: inherit !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        padding: 0.45rem 0.9rem !important;
        border-radius: 0.625rem !important;
        cursor: pointer !important;
        transition: all 0.15s ease-in-out !important;
    }

    .driver-popover-prev-btn {
        background: #f1f5f9 !important;
        color: #334155 !important;
        border: 1px solid #cbd5e1 !important;
    }

    .dark .driver-popover-prev-btn {
        background: #1e293b !important;
        color: #e2e8f0 !important;
        border-color: #475569 !important;
    }

    .driver-popover-prev-btn:hover {
        background: #e2e8f0 !important;
    }

    .dark .driver-popover-prev-btn:hover {
        background: #334155 !important;
    }

    .driver-popover-next-btn {
        background: #0d9488 !important;
        color: #ffffff !important;
        border: 1px solid transparent !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }

    .driver-popover-next-btn:hover {
        background: #0f766e !important;
    }

    .driver-popover-arrow-side-left {
        border-left-color: #ffffff !important;
    }
    .dark .driver-popover-arrow-side-left {
        border-left-color: #0f172a !important;
    }

    .driver-popover-arrow-side-right {
        border-right-color: #ffffff !important;
    }
    .dark .driver-popover-arrow-side-right {
        border-right-color: #0f172a !important;
    }

    .driver-popover-arrow-side-top {
        border-top-color: #ffffff !important;
    }
    .dark .driver-popover-arrow-side-top {
        border-top-color: #0f172a !important;
    }

    .driver-popover-arrow-side-bottom {
        border-bottom-color: #ffffff !important;
    }
    .dark .driver-popover-arrow-side-bottom {
        border-bottom-color: #0f172a !important;
    }
</style>
@endonce

<script>
(function() {
    const portal = @json($portal);
    const rawSteps = @json($steps);
    const hasCompleted = @json($hasCompleted);
    const completeUrl = @json(route('portal-tour.complete'));
    const csrfToken = @json(csrf_token());

    function markTourCompleted() {
        try {
            localStorage.setItem('thinker_tour_' + portal, 'completed');
        } catch (e) {}

        fetch(completeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ portal: portal })
        }).catch(err => console.debug('Tour completion ping notice:', err));
    }

    window.startPortalTour = function(force = false) {
        if (!window.driver || !window.driver.js || typeof window.driver.js.driver !== 'function') {
            console.warn('Driver.js is not loaded yet');
            return;
        }

        // Filter and adapt steps dynamically depending on active DOM elements
        const resolvedSteps = rawSteps.map(step => {
            if (!step.element) return step;
            const el = document.querySelector(step.element);
            if (el && el.offsetParent !== null) {
                return step;
            }

            // Fallback to centered modal popover if target element is not currently in DOM
            const { element, ...rest } = step;
            return {
                ...rest,
                popover: {
                    ...rest.popover,
                    side: 'over',
                    align: 'center'
                }
            };
        });

        const driverObj = window.driver.js.driver({
            animate: true,
            showProgress: true,
            progressText: 'Step @{{current}} of @{{total}}',
            allowClose: true,
            overlayOpacity: 0.75,
            overlayColor: '#0f172a',
            stagePadding: 8,
            stageRadius: 12,
            popoverOffset: 12,
            nextBtnText: 'Next',
            prevBtnText: 'Back',
            doneBtnText: 'Finish Tour',
            steps: resolvedSteps,
            onDestroyed: () => {
                markTourCompleted();
            }
        });

        driverObj.drive();
    };

    // Auto-launch on first visit if not completed (desktop only, disabled on mobile)
    function checkAutoLaunch() {
        if (window.innerWidth < 640) {
            return;
        }

        let localCompleted = false;
        try {
            localCompleted = localStorage.getItem('thinker_tour_' + portal) === 'completed';
        } catch (e) {}

        if (!hasCompleted && !localCompleted) {
            setTimeout(() => {
                if (typeof window.startPortalTour === 'function') {
                    window.startPortalTour(false);
                }
            }, 850);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkAutoLaunch);
    } else {
        checkAutoLaunch();
    }
})();
</script>
