const CACHE_NAME = 'tadbirkor-shell-v6';
const PUBLIC_PAGES = new Set(['login', 'register']);
const SHELL_ASSETS = [
    '/?page=login',
    '/?page=register',
    '/assets/css/app.css',
    '/assets/js/app.js',
    '/manifest.webmanifest',
    '/assets/icons/icon.svg',
    '/assets/icons/icon-180.png',
    '/assets/icons/icon-192.png',
    '/assets/icons/icon-512.png',
];

function isPublicDocument(url) {
    return url.origin === self.location.origin
        && url.pathname === '/'
        && PUBLIC_PAGES.has(url.searchParams.get('page') || '');
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    const isSameOrigin = url.origin === self.location.origin;
    const isDocument = request.mode === 'navigate';
    const isStatic = isSameOrigin && (
        url.pathname.startsWith('/assets/')
        || url.pathname === '/manifest.webmanifest'
    );

    if (isDocument) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (isPublicDocument(url)) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(async () => {
                    if (isPublicDocument(url)) {
                        const cached = await caches.match(request);
                        if (cached) {
                            return cached;
                        }
                    }

                    return caches.match('/?page=login') || caches.match('/?page=register');
                })
        );
        return;
    }

    if (isStatic) {
        event.respondWith(
            caches.match(request).then((cached) => {
                const networkFetch = fetch(request)
                    .then((response) => {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                        return response;
                    })
                    .catch(() => cached);

                return cached || networkFetch;
            })
        );
    }
});
