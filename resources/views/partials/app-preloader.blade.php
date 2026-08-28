<div id="thinker-app-preloader" class="thinker-preloader-root" aria-hidden="true" style="display: none;">
    <div class="preloader-ambient"></div>
    <div class="preloader-content">
        <div class="preloader-logo-ring">
            <img src="{{ asset('images/logos/icon_green.png') }}" alt="think.er HUB" class="preloader-logo-img">
        </div>

        <div class="preloader-brand">
            <span class="preloader-title">think.er <span class="preloader-title-hub">HUB</span></span>
            <span class="preloader-tagline">Learn • Level Up • Achieve</span>
        </div>

        <div class="preloader-progress-wrap">
            <div class="preloader-progress-bar" id="preloader-bar"></div>
        </div>

        <div class="preloader-status" id="preloader-status-text">
            Initializing mobile app...
        </div>
    </div>
</div>

<style>
    .thinker-preloader-root {
        position: fixed;
        inset: 0;
        z-index: 999999;
        display: none;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at center, #0a242c 0%, #06161a 100%);
        color: #e2f1f0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        opacity: 1;
        transform: scale(1);
        transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
        user-select: none;
        -webkit-user-select: none;
    }

    .thinker-preloader-root.preloader-active {
        display: flex !important;
        pointer-events: auto !important;
    }

    .thinker-preloader-root.preloader-hidden {
        opacity: 0 !important;
        transform: scale(1.025) !important;
        pointer-events: none !important;
        display: none !important;
    }

    .preloader-ambient {
        position: absolute;
        width: 380px;
        height: 380px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(0, 106, 103, 0.5) 0%, rgba(6, 22, 26, 0) 70%);
        animation: preloaderGlow 3s ease-in-out infinite alternate;
        pointer-events: none;
    }

    @keyframes preloaderGlow {
        0% { transform: scale(0.85); opacity: 0.5; }
        100% { transform: scale(1.2); opacity: 0.95; }
    }

    .preloader-content {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.25rem;
        padding: 2rem;
        max-width: 320px;
        text-align: center;
    }

    .preloader-logo-ring {
        position: relative;
        width: 84px;
        height: 84px;
        border-radius: 24px;
        background: linear-gradient(145deg, #112d36, #091a20);
        border: 1.5px solid rgba(0, 106, 103, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: preloaderLogoPulse 2.4s ease-in-out infinite;
    }

    @keyframes preloaderLogoPulse {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-4px) scale(1.04); }
    }

    .preloader-logo-img {
        width: 52px;
        height: 52px;
        object-fit: contain;
    }

    .preloader-brand {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }

    .preloader-title {
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #ffffff;
    }

    .preloader-title-hub {
        color: #2dd4bf;
    }

    .preloader-tagline {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #94a3b8;
    }

    .preloader-progress-wrap {
        width: 220px;
        height: 5px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 999px;
        overflow: hidden;
        position: relative;
    }

    .preloader-progress-bar {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 0%;
        background: linear-gradient(90deg, #006a67, #2dd4bf, #008884);
        border-radius: 999px;
        transition: width 1.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .preloader-status {
        font-size: 0.78rem;
        font-weight: 600;
        color: #a4bbba;
        min-height: 1.2rem;
        transition: opacity 0.2s ease;
    }
</style>

<script>
(function () {
    const preloader = document.getElementById('thinker-app-preloader');
    if (!preloader) return;

    function dismissPreloader() {
        preloader.classList.add('preloader-hidden');
        preloader.classList.remove('preloader-active');
        preloader.style.pointerEvents = 'none';
        preloader.style.display = 'none';
    }

    try {
        const isStandalonePWA = window.matchMedia('(display-mode: standalone)').matches 
            || window.navigator.standalone === true;

        const path = window.location.pathname.toLowerCase();
        const isAuthPage = path.includes('/login') || path.includes('/register') || path.includes('/auth');
        let alreadyPreloaded = false;

        try {
            alreadyPreloaded = sessionStorage.getItem('thinker_pwa_preloaded') === 'true';
        } catch (e) {
            alreadyPreloaded = true; // Fallback if storage access is restricted
        }

        // ONLY trigger on standalone PWA launch at auth screen
        if (isStandalonePWA && isAuthPage && !alreadyPreloaded) {
            try {
                sessionStorage.setItem('thinker_pwa_preloaded', 'true');
            } catch (e) {}

            preloader.classList.add('preloader-active');

            const bar = document.getElementById('preloader-bar');
            requestAnimationFrame(function () {
                if (bar) bar.style.width = '100%';
            });

            setTimeout(dismissPreloader, 1200);
        } else {
            dismissPreloader();
        }
    } catch (err) {
        dismissPreloader();
    }
})();
</script>
