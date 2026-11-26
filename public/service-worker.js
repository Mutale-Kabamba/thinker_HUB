const CACHE_NAME = 'thinkerhub-v6';
const COURSE_CACHE_NAME = 'thinkerhub-offline-courses-v1';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
  '/',
  '/learn/offline',
  '/js/thinker-offline.js',
  '/manifest.json',
  OFFLINE_URL,
  '/images/logos/green.png',
  '/images/logos/green_white.png',
  '/images/logos/icon_green.png',
  '/images/logos/white.png',
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return Promise.allSettled(PRECACHE_ASSETS.map(url => cache.add(url)));
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys
          .filter(k => k !== CACHE_NAME && k !== COURSE_CACHE_NAME)
          .map(k => caches.delete(k))
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // Saved offline course assets (videos, PDFs, SCORM files): Offline Course Cache first
  if (
    url.pathname.startsWith('/storage/') ||
    url.pathname.includes('/scorm/') ||
    url.pathname.endsWith('.mp4') ||
    url.pathname.endsWith('.webm') ||
    url.pathname.endsWith('.pdf')
  ) {
    event.respondWith(
      caches.open(COURSE_CACHE_NAME).then(courseCache => {
        return courseCache.match(event.request, { ignoreSearch: false }).then(cached => {
          if (cached) {
            return cached;
          }
          return fetch(event.request).catch(() => caches.match(event.request));
        });
      })
    );
    return;
  }

  // HTML navigation requests: Network-First, caching both original URL and final redirected URL
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          if (response && response.ok) {
            const copy = response.clone();
            const redirectedUrl = response.redirected && response.url ? response.url : null;
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, copy.clone());
              if (redirectedUrl) {
                cache.put(redirectedUrl, copy);
              }
            });
          }
          return response;
        })
        .catch(async () => {
          const exact = await caches.match(event.request);
          if (exact) return exact;

          const noQuery = await caches.match(event.request, { ignoreSearch: true });
          if (noQuery) return noQuery;

          // When launching PWA start_url (/learn) offline, try cached Student Dashboard first
          if (url.pathname === '/learn' || url.pathname === '/learn/') {
            const overview = await caches.match('/learn/overview');
            if (overview) return overview;
          }

          if (url.pathname.startsWith('/learn/offline')) {
            const lib = await caches.match('/learn/offline');
            if (lib) return lib;
          }

          return caches.match(OFFLINE_URL);
        })
    );
    return;
  }

  // Static & Vite-built assets (CSS, JS, Images, Fonts): Cache-First + background refresh
  if (
    url.pathname.startsWith('/build/') ||
    url.pathname.startsWith('/images/') ||
    url.pathname.startsWith('/js/') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.jpg') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.css') ||
    url.pathname.endsWith('.js') ||
    url.pathname.endsWith('.woff2')
  ) {
    event.respondWith(
      caches.match(event.request).then(cached => {
        const fetchPromise = fetch(event.request)
          .then(networkResponse => {
            if (networkResponse && networkResponse.ok) {
              const copy = networkResponse.clone();
              caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
            }
            return networkResponse;
          })
          .catch(() => cached);

        return cached || fetchPromise;
      })
    );
    return;
  }

  // Default fetch
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});
