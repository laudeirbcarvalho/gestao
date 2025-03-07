self.addEventListener('install', function(event) {
    event.waitUntil(
      caches.open('gestao-adlux-cache').then(function(cache) {
        return cache.addAll([
          '/login.php',
          '/painel.php',
          '/icone.png',
          '/manifest.json'
        ]);
      })
    );
  });
  
  self.addEventListener('fetch', function(event) {
    event.respondWith(
      caches.match(event.request).then(function(response) {
        return response || fetch(event.request);
      })
    );
  });
  