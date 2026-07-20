/**
 * consent.js — Consentement cookies (RGPD / CNIL) + Consent Mode v2 + GA4.
 * Aucun script Google n'est chargé avant acceptation. Le choix est mémorisé
 * (cookie idc_consent) ; les conversions ne partent qu'après consentement.
 */
(function () {
  'use strict';

  var CFG = window.IDC_CONSENT || {};
  var doc = document;
  if (!CFG.ga4) return; // aucune mesure configurée → pas de bandeau ni de tracking

  var COOKIE = 'idc_consent';
  var gaLoaded = false;

  function gtag() { window.dataLayer = window.dataLayer || []; window.dataLayer.push(arguments); }

  function getConsent() {
    var m = doc.cookie.match(/(?:^|;\s*)idc_consent=([^;]+)/);
    if (!m) return null;
    try { return JSON.parse(decodeURIComponent(m[1])); } catch (e) { return null; }
  }
  function saveConsent(c) {
    c.ts = Date.now();
    var v = encodeURIComponent(JSON.stringify(c));
    // 6 mois (recommandation CNIL : re-demander au bout de 6 mois maxi)
    doc.cookie = COOKIE + '=' + v + ';path=/;max-age=' + (60 * 60 * 24 * 182) + ';SameSite=Lax';
  }

  function loadGA() {
    if (gaLoaded || !CFG.ga4) return;
    gaLoaded = true;
    var s = doc.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(CFG.ga4);
    doc.head.appendChild(s);
    gtag('js', new Date());
    gtag('config', CFG.ga4, { anonymize_ip: true });
  }

  function fireConversions() {
    (CFG.conversions || []).forEach(function (c) {
      gtag('event', c.name, c.params || {});
    });
  }

  function apply(c) {
    // Met à jour Consent Mode selon les choix.
    gtag('consent', 'update', {
      analytics_storage: c.analytics ? 'granted' : 'denied',
      ad_storage: c.ads ? 'granted' : 'denied',
      ad_user_data: c.ads ? 'granted' : 'denied',
      ad_personalization: c.ads ? 'granted' : 'denied'
    });
    if (c.analytics) {
      loadGA();
      fireConversions();
    }
  }

  // API publique pour des événements custom (déclenchés seulement si mesure active).
  window.idcTrack = function (name, params) {
    var c = getConsent();
    if (c && c.analytics && gaLoaded) gtag('event', name, params || {});
  };

  /* ── Bandeau ─────────────────────────────────────────────── */
  var banner = doc.getElementById('idc-consent');
  function show() { if (banner) banner.classList.add('open'); }
  function hide() { if (banner) banner.classList.remove('open'); }

  function attachHandlers() {
    if (!banner) return;
    banner.addEventListener('click', function (e) {
      var act = e.target.closest('[data-consent]');
      if (!act) return;
      var a = act.getAttribute('data-consent');
      if (a === 'accept') {
        var c = { analytics: true, ads: true };
        saveConsent(c); apply(c); hide();
      } else if (a === 'refuse') {
        var r = { analytics: false, ads: false };
        saveConsent(r); apply(r); hide();
      } else if (a === 'customize') {
        banner.classList.add('idc-consent--custom');
      } else if (a === 'save') {
        var cc = {
          analytics: !!(banner.querySelector('#idc-cat-analytics') || {}).checked,
          ads: !!(banner.querySelector('#idc-cat-ads') || {}).checked
        };
        saveConsent(cc); apply(cc); hide();
      }
    });
  }

  function init() {
    attachHandlers(); // toujours actifs (y compris pour la réouverture « Gérer les cookies »)
    var stored = getConsent();
    if (stored) { apply(stored); return; } // choix déjà fait → pas de bandeau
    show();
  }

  // Réouverture depuis un lien « Gérer les cookies » (footer / page confidentialité).
  doc.addEventListener('click', function (e) {
    if (e.target.closest('[data-open-consent]')) {
      e.preventDefault();
      show();
    }
  });

  if (doc.readyState === 'loading') doc.addEventListener('DOMContentLoaded', init);
  else init();
})();
