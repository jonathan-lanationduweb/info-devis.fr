/**
 * mobile.js — Interactions mobiles « type application » (sections 21→24).
 * Menu plein écran accessible, accordéons, galeries swipe, favoris (localStorage),
 * toasts. Tout reste utilisable au clavier et avec un lecteur d'écran.
 */
(function () {
  'use strict';

  var doc = document;
  var body = doc.body;

  /* Classes de mise en page selon les barres fixes présentes. */
  if (doc.querySelector('.idv-bottomnav')) body.classList.add('idv-has-bottomnav');
  if (doc.querySelector('.idv-actionbar')) body.classList.add('idv-actionbar-on');

  /* ── Utilitaires ─────────────────────────────────────────── */
  function lockScroll(on) { body.classList.toggle('idv-noscroll', on); }

  var toastTimer;
  function toast(msg) {
    var el = doc.getElementById('idv-toast');
    if (!el) { el = doc.createElement('div'); el.id = 'idv-toast'; el.className = 'idv-toast'; el.setAttribute('role', 'status'); el.setAttribute('aria-live', 'polite'); body.appendChild(el); }
    el.textContent = msg;
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { el.classList.remove('show'); }, 2200);
  }
  window.idvToast = toast;

  /* ── 1. Menu plein écran ─────────────────────────────────── */
  var menu = doc.getElementById('idv-mmenu');
  var burger = doc.getElementById('hamburger');
  var lastFocus = null;

  function focusables(container) {
    return Array.prototype.slice.call(
      container.querySelectorAll('a[href],button:not([disabled]),input,select,textarea,[tabindex]:not([tabindex="-1"])')
    ).filter(function (el) { return el.offsetParent !== null; });
  }
  function openMenu() {
    if (!menu) return;
    lastFocus = doc.activeElement;
    menu.classList.add('open');
    menu.setAttribute('aria-hidden', 'false');
    if (burger) burger.setAttribute('aria-expanded', 'true');
    lockScroll(true);
    var f = focusables(menu);
    if (f.length) f[0].focus();
  }
  function closeMenu() {
    if (!menu) return;
    menu.classList.remove('open');
    menu.setAttribute('aria-hidden', 'true');
    if (burger) burger.setAttribute('aria-expanded', 'false');
    lockScroll(false);
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }
  if (menu) {
    // Boutons « Menu » (hamburger header + barre de navigation inférieure).
    doc.querySelectorAll('#hamburger, [data-open-menu]').forEach(function (b) {
      b.setAttribute('aria-controls', 'idv-mmenu');
      if (b === burger) b.setAttribute('aria-expanded', 'false');
      b.addEventListener('click', function () {
        menu.classList.contains('open') ? closeMenu() : openMenu();
      });
    });
    menu.addEventListener('click', function (e) {
      if (e.target.closest('[data-mmenu-close]')) closeMenu();
    });
    doc.addEventListener('keydown', function (e) {
      if (!menu.classList.contains('open')) return;
      if (e.key === 'Escape') { closeMenu(); return; }
      if (e.key === 'Tab') { // piège de focus
        var f = focusables(menu);
        if (!f.length) return;
        var first = f[0], last = f[f.length - 1];
        if (e.shiftKey && doc.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && doc.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });
  }

  /* ── 2. Accordéons ───────────────────────────────────────── */
  doc.querySelectorAll('[data-accordion] .idv-accordion__trigger').forEach(function (trigger) {
    var panel = trigger.nextElementSibling;
    trigger.setAttribute('aria-expanded', 'false');
    trigger.addEventListener('click', function () {
      var open = trigger.getAttribute('aria-expanded') === 'true';
      trigger.setAttribute('aria-expanded', String(!open));
      panel.style.maxHeight = open ? '0px' : (panel.scrollHeight + 'px');
    });
  });

  /* ── 3. Galeries swipe (indicateurs + points cliquables) ─── */
  doc.querySelectorAll('[data-swipe]').forEach(function (gal) {
    var track = gal.querySelector('.idv-swipe');
    var dotsWrap = gal.querySelector('.idv-swipe__dots');
    if (!track) return;
    var slides = track.querySelectorAll('.idv-swipe__slide');
    if (slides.length < 2) { if (dotsWrap) dotsWrap.style.display = 'none'; return; }
    var dots = [];
    if (dotsWrap) {
      dotsWrap.innerHTML = '';
      slides.forEach(function (_, i) {
        var d = doc.createElement('button');
        d.type = 'button';
        d.className = 'idv-swipe__dot' + (i === 0 ? ' idv-swipe__dot--active' : '');
        d.setAttribute('aria-label', 'Image ' + (i + 1));
        d.addEventListener('click', function () { track.scrollTo({ left: track.clientWidth * i, behavior: 'smooth' }); });
        dotsWrap.appendChild(d);
        dots.push(d);
      });
    }
    var raf;
    track.addEventListener('scroll', function () {
      cancelAnimationFrame(raf);
      raf = requestAnimationFrame(function () {
        var idx = Math.round(track.scrollLeft / track.clientWidth);
        dots.forEach(function (d, i) { d.classList.toggle('idv-swipe__dot--active', i === idx); });
      });
    }, { passive: true });
  });

  /* ── 4. Favoris (localStorage, hors-ligne, sans compte) ──── */
  var FAV_KEY = 'idv_favoris';
  function favGet() {
    try { return JSON.parse(localStorage.getItem(FAV_KEY) || '[]'); } catch (e) { return []; }
  }
  function favSet(list) {
    try { localStorage.setItem(FAV_KEY, JSON.stringify(list)); } catch (e) {}
    updateFavBadge(list.length);
    window.dispatchEvent(new CustomEvent('idv:favoris-change', { detail: list }));
  }
  function favHas(list, id, type) {
    return list.some(function (f) { return String(f.id) === String(id) && f.type === type; });
  }
  function updateFavBadge(n) {
    var badge = doc.getElementById('idv-fav-badge');
    if (!badge) return;
    badge.textContent = n > 9 ? '9+' : String(n);
    badge.classList.toggle('show', n > 0);
  }
  window.idvFavoris = { get: favGet, has: favHas };

  function syncFavButtons() {
    var list = favGet();
    doc.querySelectorAll('[data-fav]').forEach(function (btn) {
      var on = favHas(list, btn.getAttribute('data-fav-id'), btn.getAttribute('data-fav-type') || 'artisan');
      btn.setAttribute('aria-pressed', String(on));
      var label = btn.querySelector('[data-fav-label]');
      if (label) label.textContent = on ? 'Dans vos favoris' : 'Ajouter aux favoris';
    });
    updateFavBadge(list.length);
  }

  doc.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-fav]');
    if (!btn) return;
    e.preventDefault();
    var id = btn.getAttribute('data-fav-id');
    if (!id) return;
    var type = btn.getAttribute('data-fav-type') || 'artisan';
    var list = favGet();
    if (favHas(list, id, type)) {
      list = list.filter(function (f) { return !(String(f.id) === String(id) && f.type === type); });
      favSet(list);
      btn.setAttribute('aria-pressed', 'false');
      toast('Retiré des favoris');
    } else {
      list.push({
        id: id, type: type,
        title: btn.getAttribute('data-fav-title') || '',
        url: btn.getAttribute('data-fav-url') || '',
        img: btn.getAttribute('data-fav-img') || ''
      });
      favSet(list);
      btn.setAttribute('aria-pressed', 'true');
      btn.classList.remove('idv-fav-pop'); void btn.offsetWidth; btn.classList.add('idv-fav-pop');
      toast('Ajouté aux favoris');
    }
    var label = btn.querySelector('[data-fav-label]');
    if (label) label.textContent = btn.getAttribute('aria-pressed') === 'true' ? 'Dans vos favoris' : 'Ajouter aux favoris';
  });

  syncFavButtons();

  /* ── 5. Rendu de la page Favoris (si présente) ───────────── */
  var favContainer = doc.getElementById('idv-favoris-list');
  if (favContainer) {
    renderFavPage();
    window.addEventListener('idv:favoris-change', renderFavPage);
  }
  function renderFavPage() {
    var list = favGet();
    var empty = doc.getElementById('idv-favoris-empty');
    if (!list.length) {
      favContainer.innerHTML = '';
      if (empty) empty.style.display = '';
      return;
    }
    if (empty) empty.style.display = 'none';
    favContainer.innerHTML = list.map(function (f) {
      var img = f.img
        ? '<img src="' + encodeURI(f.img) + '" alt="" loading="lazy" style="width:100%;height:160px;object-fit:cover;">'
        : '<div style="width:100%;height:160px;background:#edeeed;display:flex;align-items:center;justify-content:center;color:#aeb3b2;">Aucune image</div>';
      return '<article style="background:#fff;border:1px solid #e6e9e8;border-radius:16px;overflow:hidden;">'
        + '<a href="' + encodeURI(f.url || '#') + '" style="display:block;text-decoration:none;color:inherit;">' + img
        + '<div style="padding:14px;"><h3 style="font-family:Newsreader,serif;font-size:1.05rem;font-weight:600;color:#2f3333;margin:0 0 4px;">' + escapeHtml(f.title || 'Sans titre') + '</h3>'
        + '<span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:#5b605f;">' + escapeHtml(f.type) + '</span></div></a>'
        + '<div style="padding:0 14px 14px;"><button type="button" data-fav data-fav-id="' + escapeHtml(f.id) + '" data-fav-type="' + escapeHtml(f.type) + '" aria-pressed="true" class="idv-tap" style="display:inline-flex;align-items:center;gap:6px;background:none;border:1px solid #e6e9e8;border-radius:10px;padding:8px 12px;color:#9f403d;font-weight:600;cursor:pointer;">'
        + '<svg width="18" height="18" viewBox="0 0 24 24" fill="#9f403d" stroke="#9f403d" stroke-width="2"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>'
        + 'Retirer</button></div></article>';
    }).join('');
  }
  function escapeHtml(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
})();
