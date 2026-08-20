// Service Worker for Mobile Sampling Offline Support
const CACHE_NAME = 'mobile-sampling-v2';
const OFFLINE_PAGES = [
  '/mobile/sampling',
  '/mobile/sampling/',
];

// Install event - cache essential files
self.addEventListener('install', (event) => {
  console.log('[SW] Installing service worker...');
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[SW] Caching essential files');
      return cache.addAll([
        '/',
        '/mobile/sampling',
        '/js/mobile-sampling-offline.js',
        '/js/mobile-sampling-spa.js',
      ]);
    })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating service worker...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Cache all mobile/sampling pages
  if (request.method === 'GET' && url.pathname.includes('/mobile/sampling/')) {
    event.respondWith(
      caches.open(CACHE_NAME).then((cache) => {
        return fetch(request)
          .then((response) => {
            // Cache successful responses
            if (response.status === 200) {
              const responseClone = response.clone();
              cache.put(request, responseClone);
            }
            return response;
          })
          .catch(() => {
            // Serve from cache when offline
            return cache.match(request).then((response) => {
              if (response) {
                return response;
              }
              // Return offline page if available
              return cache.match('/mobile/sampling');
            });
          });
      })
    );
  } 
  // Handle JS/CSS assets
  else if (request.method === 'GET' && (url.pathname.endsWith('.js') || url.pathname.endsWith('.css'))) {
    event.respondWith(
      caches.open(CACHE_NAME).then((cache) => {
        return cache.match(request).then((response) => {
          if (response) {
            return response;
          }
          return fetch(request).then((response) => {
            if (response.status === 200) {
              cache.put(request, response.clone());
            }
            return response;
          }).catch(() => {
            return new Response('', { status: 404 });
          });
        });
      })
    );
  }
  // For API requests, try network first, then fail gracefully
  else {
    event.respondWith(
      fetch(request).catch(() => {
        // Return a response indicating offline mode
        return new Response(
          JSON.stringify({ 
            offline: true, 
            message: 'Anda sedang offline. Data akan disimpan secara lokal dan disinkronkan ketika online kembali.' 
          }),
          {
            headers: { 'Content-Type': 'application/json' },
            status: 503
          }
        );
      })
    );
  }
});

// Message event - handle sync requests
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
