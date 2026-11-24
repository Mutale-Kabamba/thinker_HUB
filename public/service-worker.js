const CACHE_NAME = 'thinkerhub-v4';
const COURSE_CACHE_NAME = 'thinkerhub-offline-courses-v1';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
  '/',
  '/learn',
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

  // Saved offline course assets (videos, PDFs, SCORM files): check offline course cache first
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

  // HTML navigation requests: Network-First with Offline Library / Offline Fallback
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          if (response.status === 200) {
            const copy = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
          }
          return response;
        })
        .catch(() => {
          return caches.match(event.request).then(cached => {
            if (cached) return cached;
            if (url.pathname.startsWith('/learn/offline')) {
              return caches.match('/learn/offline');
            }
            return caches.match(OFFLINE_URL);
          });
        })
    );
    return;
  }

  // Static Assets (Images, Icons, Fonts, CSS, JS): Stale-While-Revalidate / Cache-First
  if (
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
            if (networkResponse.status === 200) {
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
