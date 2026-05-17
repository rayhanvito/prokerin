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

self.addEventListener('push', (event) => {
    const fallback = {
        title: 'Prokerin',
        body: 'Ada notifikasi baru.',
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        data: { url: '/notifications' },
    };

    const payload = event.data ? event.data.json() : fallback;
    const title = payload.title || fallback.title;

    event.waitUntil(
        self.registration.showNotification(title, {
            body: payload.body || fallback.body,
            icon: payload.icon || fallback.icon,
            badge: payload.badge || fallback.badge,
            data: payload.data || fallback.data,
            tag: payload.tag,
            renotify: payload.renotify,
            requireInteraction: payload.requireInteraction,
        }),
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url || '/notifications';

    event.waitUntil(
        self.clients
            .matchAll({ type: 'window', includeUncontrolled: true })
            .then((clients) => {
                for (const client of clients) {
                    if ('focus' in client) {
                        client.navigate(targetUrl);
                        return client.focus();
                    }
                }

                return self.clients.openWindow(targetUrl);
            }),
    );
});
