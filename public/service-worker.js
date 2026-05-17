// Prokerin Service Worker — minimal app-shell cache.
// Full offline strategy is deferred; this supports installability and push-ready
// lifecycle hooks without changing app data freshness.

const CACHE_NAME = 'prokerin-shell-v1';
const SHELL_URLS = ['/', '/manifest.json'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => cache.addAll(SHELL_URLS))
            .finally(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key !== CACHE_NAME)
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request)),
    );
});
