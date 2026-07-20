/**
 * service-worker.js — PWA InfoDevis (section 22.3)
 * Servi depuis la racine du domaine (scope "/").
 *
 * Stratégies :
 *   - network-first  : navigations HTML (fallback offline.html hors ligne)
 *   - cache-first    : icônes et ressources statiques versionnées du thème
 *   - stale-while-revalidate : images / médias
 *
 * Exclusions strictes (jamais interceptées / mises en cache) :
 *   /wp-admin/, /wp-login.php, /wp-json/, aperçus, requêtes non-GET,
 *   panier / commande / paiement, et toute URL contenant un nonce.
 */

var VERSION = 'idv-v1.1.0';
var STATIC_CACHE = VERSION + '-static';
var PAGE_CACHE = VERSION + '-pages';
var MEDIA_CACHE = VERSION + '-media';
var OFFLINE_URL = '/offline.html';

var PRECACHE = [
  OFFLINE_URL,
  '/assets/icons/icon-192x192.png',
  '/assets/icons/icon-512x512.png'
];

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(function (cache) {
      return cache.addAll(PRECACHE).catch(function () { /* tolérant si un asset manque */ });
    })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(keys.map(function (k) {
        if (k.indexOf(VERSION) !== 0) return caches.delete(k); // purge des anciennes versions
      }));
    }).then(function () { return self.clients.claim(); })
  );
});

// Mise à jour immédiate quand la page le demande.
self.addEventListener('message', function (event) {
  if (event.data && event.data.type === 'SKIP_WAITING') self.skipWaiting();
});

function isExcluded(url, req) {
  if (req.method !== 'GET') return true;
  if (url.origin !== self.location.origin) return true;
  var p = url.pathname;
  if (p.indexOf('/wp-admin/') === 0) return true;
  if (p === '/wp-login.php') return true;
  if (p.indexOf('/wp-json/') === 0) return true;
  if (p.indexOf('/wp-cron.php') === 0) return true;
  if (p.indexOf('/cart') === 0 || p.indexOf('/panier') === 0) return true;
  if (p.indexOf('/checkout') === 0 || p.indexOf('/commande') === 0 || p.indexOf('/paiement') === 0) return true;
  if (p.indexOf('/mon-compte') === 0 || p.indexOf('/dashboard/') === 0) return true; // espaces privés : jamais en cache
  var s = url.search;
  if (/(_wpnonce|nonce|preview|customize_changeset|action=)/i.test(s)) return true;
  return false;
}

function isStaticAsset(url) {
  return /\/wp-content\/themes\/.+\.(css|js|woff2?|ttf|svg)$/i.test(url.pathname) ||
         /\/assets\/icons\/.+\.(png|ico|svg)$/i.test(url.pathname);
}
function isMedia(url) {
  return /\.(png|jpe?g|webp|gif|avif)$/i.test(url.pathname);
}

self.addEventListener('fetch', function (event) {
  var req = event.request;
  var url = new URL(req.url);

  if (isExcluded(url, req)) return; // laisse passer au réseau, sans interception

  // Navigations HTML → network-first + fallback offline.
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).then(function (res) {
        var copy = res.clone();
        caches.open(PAGE_CACHE).then(function (c) { c.put(req, copy); });
        return res;
      }).catch(function () {
        return caches.match(req).then(function (cached) {
          return cached || caches.match(OFFLINE_URL);
        });
      })
    );
    return;
  }

  // Ressources statiques versionnées → cache-first.
  if (isStaticAsset(url)) {
    event.respondWith(
      caches.match(req).then(function (cached) {
        return cached || fetch(req).then(function (res) {
          var copy = res.clone();
          caches.open(STATIC_CACHE).then(function (c) { c.put(req, copy); });
          return res;
        });
      })
    );
    return;
  }

  // Images / médias → stale-while-revalidate.
  if (isMedia(url)) {
    event.respondWith(
      caches.open(MEDIA_CACHE).then(function (cache) {
        return cache.match(req).then(function (cached) {
          var network = fetch(req).then(function (res) {
            if (res && res.status === 200) cache.put(req, res.clone());
            return res;
          }).catch(function () { return cached; });
          return cached || network;
        });
      })
    );
  }
});
