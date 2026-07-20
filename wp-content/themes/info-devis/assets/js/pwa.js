/**
 * pwa.js — Progressive Web App (section 22).
 * Enregistre le service worker, gère l'invite d'installation personnalisée
 * (beforeinstallprompt), l'instruction iOS discrète, et le mode standalone.
 * L'URL du SW est fournie par window.IDV_PWA (localisée par WordPress).
 */
(function () {
  'use strict';

  var CFG = window.IDV_PWA || {};
  var doc = document;

  /* ── Mode standalone (lancé depuis l'écran d'accueil) ────── */
  var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  if (isStandalone) doc.body.classList.add('idv-standalone');

  /* ── Enregistrement du service worker ────────────────────── */
  if ('serviceWorker' in navigator && CFG.swUrl) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register(CFG.swUrl, { scope: '/' }).then(function (reg) {
        // Recharge en douceur quand une nouvelle version prend le contrôle.
        if (reg.waiting) reg.waiting.postMessage({ type: 'SKIP_WAITING' });
        reg.addEventListener('updatefound', function () {
          var sw = reg.installing;
          if (!sw) return;
          sw.addEventListener('statechange', function () {
            if (sw.state === 'installed' && navigator.serviceWorker.controller) {
              sw.postMessage({ type: 'SKIP_WAITING' });
            }
          });
        });
      }).catch(function () { /* silencieux : le site reste fonctionnel sans SW */ });
    });
    var refreshing = false;
    navigator.serviceWorker.addEventListener('controllerchange', function () {
      if (refreshing) return;
      refreshing = true;
      window.location.reload();
    });
  }

  /* ── Invite d'installation personnalisée ─────────────────── */
  var DISMISS_KEY = 'idv_install_dismissed';
  var banner = doc.getElementById('idv-install');
  var deferredPrompt = null;

  function dismissed() {
    try { return localStorage.getItem(DISMISS_KEY) === '1'; } catch (e) { return false; }
  }
  function remember() { try { localStorage.setItem(DISMISS_KEY, '1'); } catch (e) {} }
  function hide() { if (banner) banner.classList.remove('show'); }

  if (banner) {
    var closeBtn = banner.querySelector('[data-install-close]');
    var actionBtn = banner.querySelector('[data-install-action]');
    if (closeBtn) closeBtn.addEventListener('click', function () { hide(); remember(); });

    // Android / Chrome : vraie installation.
    window.addEventListener('beforeinstallprompt', function (e) {
      e.preventDefault();
      deferredPrompt = e;
      if (isStandalone || dismissed()) return;
      banner.querySelector('[data-install-mode="android"]').style.display = '';
      var ios = banner.querySelector('[data-install-mode="ios"]');
      if (ios) ios.style.display = 'none';
      banner.classList.add('show');
    });
    if (actionBtn) {
      actionBtn.addEventListener('click', function () {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.finally(function () { deferredPrompt = null; hide(); remember(); });
      });
    }
    window.addEventListener('appinstalled', function () { hide(); remember(); });

    // iOS / iPadOS : pas de beforeinstallprompt → instruction discrète.
    var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent) ||
      (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    if (isIOS && !isStandalone && !dismissed()) {
      var iosEl = banner.querySelector('[data-install-mode="ios"]');
      var androidEl = banner.querySelector('[data-install-mode="android"]');
      if (androidEl) androidEl.style.display = 'none';
      if (iosEl) iosEl.style.display = '';
      banner.classList.add('show');
    }
  }
})();
