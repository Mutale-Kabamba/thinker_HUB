{{-- Subtle, Refined Public Site Motion System --}}
<style>
    /* Top Scrolling Sticky Promo Ticker */
    @keyframes hubTickerScroll {
        0% {
            transform: translate3d(0, 0, 0);
        }
        100% {
            transform: translate3d(-50%, 0, 0);
        }
    }

    .hub-ticker-track {
        width: max-content;
        animation: hubTickerScroll 34s linear infinite;
        will-change: transform;
    }

    .hub-top-ticker:hover .hub-ticker-track,
    .hub-top-ticker:focus-within .hub-ticker-track {
        animation-play-state: paused;
    }

    .hub-promo-badge {
        background-color: #e11d48 !important;
        color: #ffffff !important;
        border-color: #be123c !important;
    }

    .hub-promo-strip {
        background-color: #0a2d27 !important;
        color: #ffffff !important;
        border-color: #11443c !important;
    }

    @media (prefers-reduced-motion: reduce) {
        .hub-ticker-track {
            animation: none;
        }
        .hub-ticker-viewport {
            overflow-x: auto;
            scrollbar-width: none;
        }
    }

    @media (prefers-reduced-motion: no-preference) {
        /* 1. Hero Entrance Animations */
        @keyframes hubFadeUp {
            from {
                opacity: 0;
                transform: translate3d(0, 14px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        @keyframes hubFloatSlow {
            0%, 100% {
                transform: translate3d(0, 0, 0);
            }
            50% {
                transform: translate3d(0, -5px, 0);
            }
        }

        @keyframes hubPulseRing {
            0%, 100% {
                transform: scale(1.04);
                opacity: 0.85;
            }
            50% {
                transform: scale(1.07);
                opacity: 1;
            }
        }

        @keyframes hubGlowDrift {
            0%, 100% {
                transform: translate3d(0, 0, 0) scale(1);
            }
            50% {
                transform: translate3d(10px, -8px, 0) scale(1.05);
            }
        }

        .hub-public main > section:first-of-type h1 {
            animation: hubFadeUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) 0.04s both;
        }

        .hub-public main > section:first-of-type p {
            animation: hubFadeUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) 0.1s both;
        }

        .hub-public .hub-float-badge {
            animation: hubFloatSlow 4.5s ease-in-out infinite;
        }

        .hub-public .hub-hero-ring {
            animation: hubPulseRing 6s ease-in-out infinite;
        }

        .hub-public .hub-glow-orb {
            animation: hubGlowDrift 8s ease-in-out infinite;
        }

        /* 2. Scroll Reveal Classes */
        .hub-reveal {
            opacity: 0;
            transform: translate3d(0, 18px, 0);
            transition:
                opacity 0.55s cubic-bezier(0.22, 1, 0.36, 1),
                transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--hub-delay, 0ms);
            will-change: opacity, transform;
        }

        .hub-reveal.is-visible {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }

        /* 3. Interactive Card Polish */
        .hub-public main article {
            transition:
                transform 0.28s cubic-bezier(0.22, 1, 0.36, 1),
                box-shadow 0.28s cubic-bezier(0.22, 1, 0.36, 1),
                border-color 0.28s ease,
                background-color 0.28s ease !important;
        }

        .hub-public main article:hover {
            transform: translate3d(0, -4px, 0);
            box-shadow:
                0 14px 28px -8px rgba(10, 45, 39, 0.08),
                0 4px 10px -3px rgba(10, 45, 39, 0.04);
            border-color: rgba(13, 148, 136, 0.32);
        }

        /* Subtle icon pop when hovering feature/value cards */
        .hub-public main article > div.inline-flex,
        .hub-public main .hub-icon-badge {
            transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .hub-public main article:hover > div.inline-flex,
        .hub-public main *:hover > .hub-icon-badge {
            transform: scale(1.08) translateY(-1px);
        }

        /* 4. Tactile Button & Link Arrow Motion */
        .hub-public a.rounded-full,
        .hub-public button.rounded-full {
            transition:
                transform 0.2s cubic-bezier(0.22, 1, 0.36, 1),
                background-color 0.2s ease,
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                color 0.2s ease;
        }

        .hub-public a.rounded-full:active,
        .hub-public button.rounded-full:active {
            transform: scale(0.98);
        }

        .hub-public a i.fa-arrow-right,
        .hub-public a i.fa-arrow-right-long,
        .hub-public button i.fa-arrow-right,
        .hub-public button i.fa-paper-plane {
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .hub-public a:hover i.fa-arrow-right,
        .hub-public a:hover i.fa-arrow-right-long,
        .hub-public button:hover i.fa-arrow-right {
            transform: translateX(3px);
        }

        .hub-public button:hover i.fa-paper-plane {
            transform: translate(2px, -2px);
        }

        /* 5. Sticky Header Scroll Elevation */
        .hub-public-header {
            transition:
                background-color 0.25s ease,
                box-shadow 0.25s ease,
                border-color 0.25s ease;
        }

        .hub-public-header.is-scrolled {
            background-color: rgba(255, 255, 255, 0.96);
            box-shadow: 0 8px 24px -6px rgba(10, 45, 39, 0.07);
            border-bottom-color: rgba(203, 213, 225, 0.85);
        }

        /* Footer social icons gentle lift */
        .hub-public footer a.rounded-full {
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1), border-color 0.2s ease, color 0.2s ease, background-color 0.2s ease;
        }

        .hub-public footer a.rounded-full:hover {
            transform: translateY(-2px);
        }
    }
</style>

<script>
    (function () {
        if (window.__hubPublicMotionInitialized) {
            return;
        }
        window.__hubPublicMotionInitialized = true;

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function initHeaderScroll() {
            const header = document.querySelector('.hub-public-header');
            if (!header) return;

            const onScroll = () => {
                if (window.scrollY > 14) {
                    header.classList.add('is-scrolled');
                } else {
                    header.classList.remove('is-scrolled');
                }
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }

        function initScrollReveal() {
            if (prefersReducedMotion || !('IntersectionObserver' in window)) {
                return;
            }

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        obs.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -28px 0px'
            });

            // Select cards, section headers, and callout boxes below the hero
            const selectors = [
                '.hub-public main section:not(:first-of-type) h2',
                '.hub-public main article',
                '.hub-public main section:not(:first-of-type) .rounded-3xl',
                '.hub-public main section:not(:first-of-type) > div > .grid > div.rounded-2xl'
            ];

            const elements = document.querySelectorAll(selectors.join(', '));
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight;

            elements.forEach((el) => {
                if (el.dataset.hubRevealBound === '1') {
                    return;
                }
                el.dataset.hubRevealBound = '1';

                // Compute subtle stagger delay for grid items (0ms, 65ms, 130ms, 195ms)
                const parent = el.parentElement;
                if (parent && (parent.classList.contains('grid') || getComputedStyle(parent).display === 'grid')) {
                    const siblings = Array.from(parent.children);
                    const indexInRow = siblings.indexOf(el) % 4;
                    if (indexInRow > 0) {
                        el.style.setProperty('--hub-delay', (indexInRow * 65) + 'ms');
                    }
                }

                const rect = el.getBoundingClientRect();
                // If already well within the initial viewport on page load, show immediately without hiding
                if (rect.top < viewportHeight * 0.88 && rect.bottom > 0) {
                    el.classList.add('hub-reveal', 'is-visible');
                } else {
                    el.classList.add('hub-reveal');
                    observer.observe(el);
                }
            });
        }

        function bootMotion() {
            initHeaderScroll();
            requestAnimationFrame(() => {
                initScrollReveal();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootMotion);
        } else {
            bootMotion();
        }

        document.addEventListener('livewire:navigated', bootMotion);
    })();
</script>
