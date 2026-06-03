/* Service Worker — Sistema PDV (PWA) */
const CACHE = 'pdv-cache-v1';
const ASSETS = [
    '/offline.html',
    '/assets/css/bootstrap.min.css',
    '/assets/js/bootstrap.bundle.min.js',
    '/assets/js/qrcode.min.js',
    '/assets/tabler/tabler-icons.min.css',
    '/assets/tabler/fonts/tabler-icons.woff2',
    '/favicon.svg',
    '/icon-192.png',
    '/icon-512.png',
];

// Instala e pré-cacheia os assets estáticos
self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE).then((c) => c.addAll(ASSETS).catch(() => {})).then(() => self.skipWaiting())
    );
});

// Limpa caches antigos
self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (e) => {
    const req = e.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== location.origin) return;

    // Assets estáticos: cache-first
    if (url.pathname.startsWith('/assets/') || url.pathname.startsWith('/storage/') ||
        url.pathname.endsWith('.png') || url.pathname.endsWith('.svg') || url.pathname.endsWith('.ico')) {
        e.respondWith(
            caches.match(req).then((hit) => hit || fetch(req).then((res) => {
                const copy = res.clone();
                caches.open(CACHE).then((c) => c.put(req, copy));
                return res;
            }).catch(() => hit))
        );
        return;
    }

    // Navegação (páginas): network-first, fallback offline
    if (req.mode === 'navigate') {
        e.respondWith(
            fetch(req).then((res) => {
                const copy = res.clone();
                caches.open(CACHE).then((c) => c.put(req, copy));
                return res;
            }).catch(() => caches.match(req).then((hit) => hit || caches.match('/offline.html')))
        );
    }
});
