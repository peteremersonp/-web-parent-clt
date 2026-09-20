/*
 * ParentCLT service worker:
 * - Navegación: red primero, página offline si no hay conexión.
 * - Assets estáticos (css/js/img/fuentes): cache-first con refresco en background.
 * - NUNCA cachea la API (/api/) ni las descargas (/downloads/): datos siempre vivos.
 */
const CACHE = 'parentclt-v1';
const OFFLINE_URL = '/offline';
const PRECACHE = [OFFLINE_URL, '/manifest.json', '/pwa-icons/icon-192.png', '/pwa-icons/icon-512.png'];
const ASSET_RE = /\.(css|js|png|jpe?g|gif|svg|ico|woff2?|ttf)$/i;

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== location.origin) return;
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/downloads/')) return;

    // Navegación: red primero; sin conexión -> página offline.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Solo assets estáticos van a caché.
    if (!ASSET_RE.test(url.pathname)) return;

    event.respondWith(
        caches.match(request).then((cached) => {
            const refresh = fetch(request)
                .then((response) => {
                    if (response && response.ok) {
                        caches.open(CACHE).then((cache) => cache.put(request, response.clone()));
                    }
                    return response;
                })
                .catch(() => cached);
            return cached || refresh;
        })
    );
});
