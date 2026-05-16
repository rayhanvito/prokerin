// Prokerin Service Worker — minimal app-shell cache.
// Full offline strategy is deferred to M33; this only registers the SW so the
// PWA install prompt is available and assets are revalidated on each visit.

const VERSION = 'prokerin-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Always network-first. SW just exists to satisfy PWA install criteria.
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request)),
    );
});
