<div id="thinker-app-preloader" class="thinker-preloader-root" role="status" aria-live="polite" aria-label="Loading think.er HUB">
    <div class="preloader-container">
        <div class="preloader-logo-box">
            <img src="{{ asset('images/logos/green.png') }}" alt="think.er HUB" class="preloader-logo preloader-logo-light">
            <img src="{{ asset('images/logos/yellow_white.png') }}" alt="think.er HUB" class="preloader-logo preloader-logo-dark">
        </div>

        <div class="preloader-indicator" aria-hidden="true">
            <div class="preloader-indicator-bar"></div>
        </div>

        <span class="sr-only">Loading application...</span>
    </div>

    <div class="preloader-credit">
        By Ori Studio Limited
    </div>
</div>

<style>
    .thinker-preloader-root {
        position: fixed;
        inset: 0;
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8fafc;
        color: #0f172a;
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        user-select: none;
        -webkit-user-select: none;
        transition: opacity 280ms cubic-bezier(0.4, 0, 0.2, 1), visibility 280ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Dark mode background resolution */
    @media (prefers-color-scheme: dark) {
        .thinker-preloader-root {
            background-color: #0b141a;
            color: #f8fafc;
        }
    }

    html.dark .thinker-preloader-root,
    body.dark .thinker-preloader-root {
        background-color: #0b141a !important;
        color: #f8fafc !important;
    }

    .thinker-preloader-root.preloader-hidden {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    .preloader-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1.25rem;
        padding: 1.5rem;
        max-width: 260px;
        text-align: center;
    }

    .preloader-logo-box {
        display: flex;
        align-items: center;
        justify-content: center;
        animation: preloaderLogoPulse 1.8s ease-in-out infinite;
    }

    .preloader-logo {
        height: 38px;
        width: auto;
        max-width: 170px;
        object-fit: contain;
        display: block;
    }

    @media (max-width: 640px) {
        .preloader-logo {
            height: 32px;
            max-width: 140px;
        }
    }

    .preloader-logo-dark {
        display: none;
    }

    @media (prefers-color-scheme: dark) {
        .preloader-logo-light {
            display: none;
        }
        .preloader-logo-dark {
            display: block;
        }
    }

    html.dark .preloader-logo-light,
    body.dark .preloader-logo-light {
        display: none !important;
    }

    html.dark .preloader-logo-dark,
    body.dark .preloader-logo-dark {
        display: block !important;
    }

    .preloader-indicator {
        width: 110px;
        height: 2px;
        background: rgba(0, 106, 103, 0.15);
        border-radius: 9999px;
        overflow: hidden;
        position: relative;
    }

    @media (prefers-color-scheme: dark) {
        .preloader-indicator {
            background: rgba(45, 212, 191, 0.15);
        }
    }

    html.dark .preloader-indicator,
    body.dark .preloader-indicator {
        background: rgba(45, 212, 191, 0.15) !important;
    }

    .preloader-indicator-bar {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 40%;
        background: #006a67;
        border-radius: 9999px;
        animation: preloaderSlide 1.1s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    }

    @media (prefers-color-scheme: dark) {
        .preloader-indicator-bar {
            background: #2dd4bf;
        }
    }

    html.dark .preloader-indicator-bar,
    body.dark .preloader-indicator-bar {
        background: #2dd4bf !important;
    }

    .preloader-credit {
        position: absolute;
        bottom: 2rem;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 0.72rem;
        font-weight: 500;
        letter-spacing: 0.05em;
        color: #64748b;
        opacity: 0.8;
        padding: 0 1rem;
        pointer-events: none;
    }

    @media (max-width: 640px) {
        .preloader-credit {
            bottom: calc(1.5rem + env(safe-area-inset-bottom, 0px));
            font-size: 0.68rem;
        }
    }

    @media (prefers-color-scheme: dark) {
        .preloader-credit {
            color: #94a3b8;
        }
    }

    html.dark .preloader-credit,
    body.dark .preloader-credit {
        color: #94a3b8 !important;
    }

    @keyframes preloaderLogoPulse {
        0%, 100% {
            opacity: 0.9;
        }
        50% {
            opacity: 1;
        }
    }

    @keyframes preloaderSlide {
        0% {
            transform: translateX(-120%);
        }
        100% {
            transform: translateX(280%);
        }
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border-width: 0;
    }

    @media (prefers-reduced-motion: reduce) {
        .preloader-logo-box,
        .preloader-indicator-bar {
            animation: none !important;
        }

        .preloader-indicator-bar {
            width: 100%;
            transform: none !important;
        }

        .thinker-preloader-root {
            transition: opacity 150ms ease !important;
        }
    }
</style>

<script>
(function () {
    const preloader = document.getElementById('thinker-app-preloader');
    if (!preloader) return;

    let isDismissed = false;
    const minDisplayTime = 4000;
    const maxSafetyTimeout = 6500;
    const startTime = performance.now();

    function dismissPreloader() {
        if (isDismissed) return;
        isDismissed = true;

        const elapsed = performance.now() - startTime;
        const remaining = Math.max(0, minDisplayTime - elapsed);

        setTimeout(function () {
            preloader.classList.add('preloader-hidden');
            setTimeout(function () {
                if (preloader && preloader.parentNode) {
                    preloader.parentNode.removeChild(preloader);
                }
            }, 300);
        }, remaining);
    }

    if (document.readyState === 'complete') {
        dismissPreloader();
    } else {
        window.addEventListener('load', dismissPreloader, { once: true });
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(dismissPreloader, 200);
        }, { once: true });
    }

    document.addEventListener('livewire:navigated', dismissPreloader, { once: true });
    document.addEventListener('livewire:initialized', dismissPreloader, { once: true });

    setTimeout(dismissPreloader, maxSafetyTimeout);
})();
</script>
