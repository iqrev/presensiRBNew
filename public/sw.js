const CACHE_NAME = 'absensirb-v1';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(() => {});
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Skip non-GET and API/attendance requests (must be fresh)
    if (event.request.method !== 'GET') return;
    if (event.request.url.includes('/absensi/check')) return;
    if (event.request.url.includes('/api/')) return;

    event.respondWith(
        caches.match(event.request).then((cached) => {
            const fetchPromise = fetch(event.request)
                .then((response) => {
                    if (response && response.status === 200 && response.type === 'basic') {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    }
                    return response;
                })
                .catch(() => cached);
            // Return cached version immediately, update in background
            return cached || fetchPromise;
        })
    );
});

// ─────────────────────────────────────────────
// Web Push Notification Events
// ─────────────────────────────────────────────
self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    const sendNotification = body => {
        const title = body.title || 'Pemberitahuan Baru';
        return self.registration.showNotification(title, {
            body: body.body || '',
            icon: body.icon || '/logo.png',
            actions: body.actions || [],
            data: body.data || {},
            vibrate: [200, 100, 200, 100, 200, 100, 200]
        });
    };

    if (event.data) {
        const payload = event.data.json();
        event.waitUntil(sendNotification(payload));
    }
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            if (clientList.length > 0) {
                let client = clientList[0];
                for (let i = 0; i < clientList.length; i++) {
                    if (clientList[i].focused) {
                        client = clientList[i];
                    }
                }
                return client.focus();
            }
            return clients.openWindow('/');
        })
    );
});
