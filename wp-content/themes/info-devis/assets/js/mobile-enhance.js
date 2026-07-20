/**
 * mobile-enhance.js — Raffinements « type application » (fluidité, interactions).
 * Header auto-masquant, feedback tactile (ripple + haptique), pull-to-refresh,
 * bottom-sheet, partage natif, lazy-loading. Tout est mobile-only et dégradé
 * proprement (respect prefers-reduced-motion, aucune dépendance).
 */
(function () {
  'use strict';

  var doc = document, body = doc.body;
  var isMobile = function () { return window.matchMedia('(max-width: 767px)').matches; };
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Haptique légère (ignorée si non supportée). */
  function haptic(ms) { try { if (navigator.vibrate) navigator.vibrate(ms || 8); } catch (e) {} }
  window.idvHaptic = haptic;

  /* ── 1. Header qui se masque au défilement (mobile) ──────── */
  (function () {
    var header = doc.getElementById('site-header');
    if (!header) return;
    var last = 0;
    function onScroll() {
      if (!isMobile()) { body.classList.remove('idv-head-hidden'); return; }
      var menu = doc.getElementById('idv-mmenu');
      if (menu && menu.classList.contains('open')) return;
      var y = window.scrollY || 0;
      if (y > last && y > 90) body.classList.add('idv-head-hidden');
      else if (y < last - 4) body.classList.remove('idv-head-hidden');
      last = y;
    }
    // Exécution directe (le toggle de classe est peu coûteux) — fiable même
    // quand requestAnimationFrame est suspendu (onglet non visible).
    window.addEventListener('scroll', onScroll, { passive: true });
  })();

  /* ── 2. Feedback tactile : ripple + haptique ─────────────── */
  var RIPPLE_SEL = '.idv-tap, [data-fav], .btn-primary-pill, .btn-outline-pill, .idv-bottomnav__item, .idv-mmenu__link, .idv-mmenu__cta, .expert-card__cta, .idv-sheet__filter';
  if (!reduced) {
    doc.addEventListener('pointerdown', function (e) {
      var el = e.target.closest(RIPPLE_SEL);
      if (!el || !isMobile()) return;
      if (getComputedStyle(el).position === 'static') el.classList.add('idv-ripple-host');
      var rect = el.getBoundingClientRect();
      var size = Math.max(rect.width, rect.height);
      var span = doc.createElement('span');
      span.className = 'idv-ripple';
      span.style.width = span.style.height = size + 'px';
      span.style.left = (e.clientX - rect.left - size / 2) + 'px';
      span.style.top = (e.clientY - rect.top - size / 2) + 'px';
      el.appendChild(span);
      setTimeout(function () { span.remove(); }, 560);
    }, { passive: true });
  }
  // Haptique sur les actions clés.
  doc.addEventListener('click', function (e) {
    if (e.target.closest('[data-fav], [data-open-menu], #hamburger, .idv-bottomnav__item')) haptic(8);
  });

  /* ── 3. Pull-to-refresh ──────────────────────────────────── */
  (function () {
    if (reduced) return;
    var ptr = doc.createElement('div');
    ptr.className = 'idv-ptr';
    ptr.innerHTML = '<span class="idv-ptr__spin"></span>';
    body.appendChild(ptr);
    var startY = 0, pulling = false, dist = 0, THRESHOLD = 72;
    doc.addEventListener('touchstart', function (e) {
      if (!isMobile() || (window.scrollY || 0) > 0) { pulling = false; return; }
      if (body.classList.contains('idv-noscroll')) { pulling = false; return; }
      startY = e.touches[0].clientY; pulling = true; dist = 0;
    }, { passive: true });
    doc.addEventListener('touchmove', function (e) {
      if (!pulling) return;
      dist = e.touches[0].clientY - startY;
      if (dist > 0 && (window.scrollY || 0) <= 0) {
        var d = Math.min(dist, 120);
        ptr.style.transform = 'translateY(' + (d - 56) + 'px)';
        ptr.style.opacity = Math.min(d / THRESHOLD, 1);
        ptr.classList.add('visible');
        if (d > 6 && e.cancelable) e.preventDefault();
      }
    }, { passive: false });
    doc.addEventListener('touchend', function () {
      if (!pulling) return;
      pulling = false;
      if (dist > THRESHOLD) {
        ptr.classList.add('spinning');
        ptr.style.transform = 'translateY(6px)';
        haptic(12);
        setTimeout(function () { window.location.reload(); }, 350);
      } else {
        ptr.style.transform = 'translateY(-56px)';
        ptr.style.opacity = '0';
        ptr.classList.remove('visible');
      }
    }, { passive: true });
  })();

  /* ── 4. Bottom-sheet générique ───────────────────────────── */
  (function () {
    var backdrop = null;
    function ensureBackdrop() {
      if (backdrop) return backdrop;
      backdrop = doc.createElement('div');
      backdrop.className = 'idv-sheet-backdrop';
      backdrop.addEventListener('click', closeAll);
      body.appendChild(backdrop);
      return backdrop;
    }
    function openSheet(sheet) {
      ensureBackdrop().classList.add('open');
      sheet.classList.add('open');
      sheet.setAttribute('aria-hidden', 'false');
      body.classList.add('idv-noscroll');
      haptic(8);
    }
    function closeAll() {
      doc.querySelectorAll('.idv-sheet.open').forEach(function (s) { s.classList.remove('open'); s.setAttribute('aria-hidden', 'true'); });
      if (backdrop) backdrop.classList.remove('open');
      body.classList.remove('idv-noscroll');
    }
    doc.addEventListener('click', function (e) {
      var opener = e.target.closest('[data-sheet-open]');
      if (opener) {
        var sheet = doc.getElementById(opener.getAttribute('data-sheet-open'));
        if (sheet) { e.preventDefault(); openSheet(sheet); }
        return;
      }
      if (e.target.closest('[data-sheet-close]')) closeAll();
    });
    doc.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && doc.querySelector('.idv-sheet.open')) closeAll();
    });
    window.idvCloseSheet = closeAll;
  })();

  /* ── 5. Partage natif (Web Share API) ────────────────────── */
  doc.querySelectorAll('[data-share]').forEach(function (btn) {
    if (!navigator.share) return;
    btn.classList.add('available');
    btn.addEventListener('click', function () {
      navigator.share({
        title: btn.getAttribute('data-share-title') || doc.title,
        text: btn.getAttribute('data-share-text') || '',
        url: btn.getAttribute('data-share-url') || location.href
      }).catch(function () {});
    });
  });

  /* ── 6. Lazy-loading + décodage asynchrone des images ────── */
  doc.querySelectorAll('img:not([loading]):not([data-no-lazy])').forEach(function (img) {
    img.loading = 'lazy';
    img.decoding = 'async';
  });
})();
