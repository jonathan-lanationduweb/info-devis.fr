/**
 * service-worker.js — PWA InfoDevis (section 22.3)
 * Servi depuis la racine du domaine (scope "/").
 *
 * Fraîcheur du cache : 1 heure. Chaque réponse mise en cache est horodatée
 * (en-tête `x-sw-cached`). Au-delà d'1 h, le SW privilégie le réseau et ne
 * ressert le cache « périmé » qu'en secours (hors ligne).
 *
 * Stratégies :
 *   - network-first  : navigations HTML (fallback cache puis offline.html)
 *   - cache-first+TTL : icônes / ressources statiques versionnées du thème
 *   - stale-while-revalidate+TTL : images / médias
 *
 * Exclusions strictes (jamais interceptées / mises en cache) :
 *   /wp-admin/, /wp-login.php, /wp-json/, aperçus, requêtes non-GET,
 *   panier / commande / paiement, espaces privés, et toute URL avec nonce.
 */

var VERSION = 'idv-v1.3.0';
var STATIC_CACHE = VERSION + '-static';
var PAGE_CACHE = VERSION + '-pages';
var MEDIA_CACHE = VERSION + '-media';
var OFFLINE_URL = '/offline.html';
var MAX_AGE = 60 * 60 * 1000; // fraîcheur du cache : 1 heure

var PRECACHE = [
  OFFLINE_URL,
  '/assets/icons/icon-192x192.png',
  '/assets/icons/icon-512x512.png'
];

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(function (cache) {
      return cache.addAll(PRECACHE).catch(function () {});
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

self.addEventListener('message', function (event) {
  if (event.data && event.data.type === 'SKIP_WAITING') self.skipWaiting();
});

/* ── Fraîcheur : horodatage + contrôle de l'âge ────────────────────────── */
function putStamped(cacheName, req, res) {
  if (!res || res.status !== 200) return;
  var copy = res.clone();
  copy.blob().then(function (body) {
    var headers = new Headers(copy.headers);
    headers.set('x-sw-cached', Date.now().toString());
    var stamped = new Response(body, { status: copy.status, statusText: copy.statusText, headers: headers });
    caches.open(cacheName).then(function (c) { c.put(req, stamped); });
  }).catch(function () {});
}
function isFresh(res) {
  if (!res) return false;
  var t = parseInt(res.headers.get('x-sw-cached') || '0', 10);
  return t > 0 && (Date.now() - t) < MAX_AGE;
}

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
  if (p.indexOf('/mon-compte') === 0 || p.indexOf('/dashboard/') === 0) return true;
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
  if (isExcluded(url, req)) return;

  // Navigations HTML → network-first ; cache = secours hors ligne (âge ignoré).
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).then(function (res) {
        putStamped(PAGE_CACHE, req, res);
        return res;
      }).catch(function () {
        return caches.match(req).then(function (cached) {
          return cached || caches.match(OFFLINE_URL);
        });
      })
    );
    return;
  }

  // Ressources statiques versionnées → cache-first + TTL 1 h.
  if (isStaticAsset(url)) {
    event.respondWith(
      caches.match(req).then(function (cached) {
        if (isFresh(cached)) return cached;
        return fetch(req).then(function (res) {
          putStamped(STATIC_CACHE, req, res);
          return res;
        }).catch(function () { return cached; }); // périmé toléré si réseau KO
      })
    );
    return;
  }

  // Images / médias → stale-while-revalidate + TTL 1 h.
  if (isMedia(url)) {
    event.respondWith(
      caches.match(req).then(function (cached) {
        var network = fetch(req).then(function (res) {
          putStamped(MEDIA_CACHE, req, res);
          return res;
        }).catch(function () { return cached; });
        // Frais (< 1 h) : on sert le cache tout de suite ; sinon on attend le réseau.
        return isFresh(cached) ? cached : (network.then(function (r) { return r || cached; }));
      })
    );
  }
});
