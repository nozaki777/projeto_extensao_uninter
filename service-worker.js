self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open('sgbd-store').then((cache) => cache.addAll([
      '/sgbd/',
      '/sgbd/index.php',
      '/sgbd/dashboard.php'
    ])),
  );
});

self.addEventListener('fetch', (e) => {
  console.log(e.request.url);
  e.respondWith(
    caches.match(e.request).then((response) => response || fetch(e.request)),
  );
});
