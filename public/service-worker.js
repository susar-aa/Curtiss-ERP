const CACHE_NAME = 'curtiss-erp-v1';
const ASSETS_TO_CACHE = [
  '/Curtiss-ERP/public/',
  '/Curtiss-ERP/public/manifest.json',
  '/Curtiss-ERP/public/icon-192.png',
  '/Curtiss-ERP/public/icon-512.png',
  'https://unpkg.com/@phosphor-icons/web'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Network-First with fallback to cache for static resources
self.addEventListener('fetch', (event) => {
  // Suppress browser extension requests or non-http requests
  if (!event.request.url.startsWith(self.location.origin) && !event.request.url.startsWith('https://unpkg.com')) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        return response;
      })
      .catch(() => {
        return caches.match(event.request);
      })
  );
});
