const CACHE_NAME = "staroil-voucher-pwa-v3";
const CORE_ASSETS = [
  "./pwa/manifest.json",
  "./pwa/icon.svg"
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(CORE_ASSETS))
      .then(() => self.skipWaiting())
      .catch(() => self.skipWaiting())
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", (event) => {
  const request = event.request;
  if (request.method !== "GET") return;
  const url = new URL(request.url);

  if (request.mode === "navigate") {
    event.respondWith(
      fetch(request, { cache: "no-store" })
        .catch(() => new Response("You are offline. Please reconnect to continue using StarOil Vouchers.", {
          status: 503,
          headers: { "Content-Type": "text/plain; charset=utf-8" }
        }))
    );
    return;
  }

  if (url.origin === location.origin && (url.pathname.endsWith(".js") || !url.pathname.match(/\.(css|svg|png|jpg|jpeg|webp|gif|ico|json)$/i))) {
    event.respondWith(fetch(request, { cache: "no-store" }));
    return;
  }

  event.respondWith(
    caches.match(request).then((cached) => cached || fetch(request).then((response) => {
      if (response.ok && url.origin === location.origin) {
        const copy = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
      }
      return response;
    }))
  );
});
