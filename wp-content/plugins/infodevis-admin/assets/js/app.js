/**
 * InfoDevis Admin — application (layout, routeur hash, recherche globale).
 */

import { icon } from './icons.js';
import { api, esc, debounce } from './api.js';
import { openMenu, toast } from './ui.js';

import { viewDashboard } from './views/dashboard.js';
import { viewSite, viewHomeEditor } from './views/site.js';
import { viewList, viewEditor, viewMetiers } from './views/content.js';
import { viewArtisans, viewArtisanEdit, viewAvis } from './views/artisans.js';
import { viewClients, viewClientDetail, viewDemandes, viewDemandeDetail, viewRdv } from './views/crm.js';
import { viewPayments } from './views/payments.js';
import { viewSeo } from './views/seo.js';
import { viewSettings } from './views/settings.js';
import { viewEmails } from './views/emails.js';

const CFG = window.IDA;
export const store = { badges: {}, enums: {}, metiers: [] };

/* ── Navigation ─────────────────────────────────────────────────────────── */

const NAV = [
  { items: [{ label: 'Tableau de bord', icon: 'home', href: '#/dashboard' }] },
  { section: 'Site', items: [
    { label: 'Gestion du site', icon: 'panels-top-left', href: '#/site' },
    { label: 'Accueil', icon: 'layout-grid', href: '#/site/accueil' },
    { label: 'Métiers', icon: 'wrench', href: '#/metiers' },
    { label: 'Pages', icon: 'file-text', href: '#/pages' },
  ]},
  { section: 'Contenus', items: [
    { label: 'Articles', icon: 'newspaper', href: '#/articles' },
    { label: 'Guides', icon: 'book-open', href: '#/guides' },
    { label: 'FAQ', icon: 'help-circle', href: '#/faq' },
  ]},
  { section: 'Artisans', items: [
    { label: 'Artisans', icon: 'hard-hat', href: '#/artisans' },
    { label: 'Vérifications', icon: 'shield-check', href: '#/artisans/verifications', badge: 'artisans' },
    { label: 'Réalisations', icon: 'image', href: '#/realisations' },
    { label: 'Avis', icon: 'star', href: '#/avis', badge: 'avis' },
  ]},
  { section: 'Clients', items: [
    { label: 'Clients', icon: 'users', href: '#/clients' },
    { label: 'Demandes', icon: 'file-text', href: '#/demandes', badge: 'demandes' },
    { label: 'Rendez-vous', icon: 'calendar', href: '#/rdv' },
  ]},
  { section: 'Paiements', items: [
    { label: 'Abonnements', icon: 'credit-card', href: '#/paiements' },
  ]},
  { section: 'SEO', items: [
    { label: 'Vue d\'ensemble', icon: 'trending-up', href: '#/seo' },
  ]},
  { section: 'Paramètres', items: [
    { label: 'Réglages', icon: 'settings', href: '#/parametres' },
    { label: 'Emails', icon: 'mail', href: '#/parametres/emails' },
    { label: 'Sauvegardes', icon: 'database', href: '#/parametres/sauvegardes' },
  ]},
];

/* ── Routes ─────────────────────────────────────────────────────────────── */

const ROUTES = [
  ['/dashboard', viewDashboard],
  ['/site', viewSite],
  ['/site/accueil', viewHomeEditor],
  ['/metiers', viewMetiers],
  ['/categories', viewMetiers],

  ['/pages', (el, p) => viewList(el, 'page', p)],
  ['/pages/:id', (el, p) => viewEditor(el, 'page', p)],
  ['/articles', (el, p) => viewList(el, 'article', p)],
  ['/articles/:id', (el, p) => viewEditor(el, 'article', p)],
  ['/guides', (el, p) => viewList(el, 'guide', p)],
  ['/guides/:id', (el, p) => viewEditor(el, 'guide', p)],
  ['/faq', (el, p) => viewList(el, 'faq', p)],
  ['/faq/:id', (el, p) => viewEditor(el, 'faq', p)],
  ['/realisations', (el, p) => viewList(el, 'realisation', p)],
  ['/realisations/:id', (el, p) => viewEditor(el, 'realisation', p)],

  ['/artisans', viewArtisans],
  ['/artisans/verifications', (el, p) => viewArtisans(el, { ...p, verifications: true })],
  ['/artisans/:id', viewArtisanEdit],
  ['/avis', viewAvis],
  ['/avis/:id', viewAvis],

  ['/clients', viewClients],
  ['/clients/:id', viewClientDetail],
  ['/demandes', viewDemandes],
  ['/demandes/:id', viewDemandeDetail],
  ['/rdv', viewRdv],
  ['/rdv/:id', viewRdv],

  ['/paiements', viewPayments],
  ['/paiements/:tab', viewPayments],
  ['/seo', viewSeo],
  ['/seo/:tab', viewSeo],
  ['/parametres', viewSettings],
  ['/parametres/emails', viewEmails],
  ['/parametres/emails/:tab', viewEmails],
  ['/parametres/:tab', viewSettings],
];

function matchRoute(path) {
  for (const [pattern, view] of ROUTES) {
    const patternParts = pattern.split('/');
    const pathParts = path.split('/');
    if (patternParts.length !== pathParts.length) continue;
    const params = {};
    let ok = true;
    for (let i = 0; i < patternParts.length; i++) {
      if (patternParts[i].startsWith(':')) params[patternParts[i].slice(1)] = decodeURIComponent(pathParts[i]);
      else if (patternParts[i] !== pathParts[i]) { ok = false; break; }
    }
    if (ok) return { view, params };
  }
  return null;
}

export function navigate(hash) {
  location.hash = hash.startsWith('#') ? hash : '#' + hash;
}

/* ── Layout ─────────────────────────────────────────────────────────────── */

function renderLayout() {
  const app = document.getElementById('app');
  const collapsed = localStorage.getItem('ida-collapsed') === '1';
  app.classList.toggle('is-collapsed', collapsed);
  app.innerHTML = `
    <div class="ida-layout">
      <div class="ida-nav-scrim" hidden></div>
      <nav class="ida-sidebar" aria-label="Navigation principale">
        <a class="ida-sidebar__brand" href="#/dashboard">
          <span class="ida-sidebar__brand-mark">ID</span>
          <span class="brand-text">Info<span class="accent">Devis</span>&nbsp;Admin</span>
        </a>
        <div class="ida-sidebar__nav" data-nav></div>
        <div class="ida-sidebar__foot">v${esc(CFG.version)} · WordPress en moteur</div>
      </nav>
      <div class="ida-main">
        <header class="ida-topbar">
          <button class="ida-topbar__toggle" data-toggle-nav aria-label="Basculer le menu">${icon('menu', 19)}</button>
          <button class="ida-topbar__search" data-open-search aria-label="Recherche globale">
            ${icon('search', 15)}<span>Rechercher…</span><kbd>Ctrl K</kbd>
          </button>
          <div class="ida-topbar__spacer"></div>
          <button class="ida-btn ida-btn--primary ida-btn--sm" data-new>${icon('plus', 15)} Nouveau</button>
          <a class="ida-icon-btn" href="${esc(CFG.homeUrl)}" target="_blank" rel="noopener" title="Voir le site" aria-label="Voir le site">${icon('globe', 18)}</a>
          <img class="ida-topbar__avatar" src="${esc(CFG.user.avatar)}" alt="" data-user-menu tabindex="0" role="button" aria-label="Menu utilisateur">
        </header>
        <main class="ida-view" id="view" tabindex="-1"></main>
      </div>
    </div>`;

  renderNav();

  // Sidebar : repli / mobile.
  app.querySelector('[data-toggle-nav]').addEventListener('click', () => {
    if (window.innerWidth < 768) {
      app.classList.toggle('is-nav-open');
      app.querySelector('.ida-nav-scrim').hidden = !app.classList.contains('is-nav-open');
    } else {
      const isCollapsed = app.classList.toggle('is-collapsed');
      localStorage.setItem('ida-collapsed', isCollapsed ? '1' : '0');
    }
  });
  app.querySelector('.ida-nav-scrim').addEventListener('click', () => {
    app.classList.remove('is-nav-open');
    app.querySelector('.ida-nav-scrim').hidden = true;
  });

  // Menu « + Nouveau ».
  app.querySelector('[data-new]').addEventListener('click', (e) => {
    openMenu(e.currentTarget, [
      { label: 'Article', icon: 'newspaper', onClick: () => createThenEdit('article', 'articles') },
      { label: 'Guide', icon: 'book-open', onClick: () => createThenEdit('guide', 'guides') },
      { label: 'Question FAQ', icon: 'help-circle', onClick: () => createThenEdit('faq', 'faq') },
      'hr',
      { label: 'Artisan', icon: 'hard-hat', onClick: () => createThenEdit('artisan', 'artisans') },
      { label: 'Demande de devis', icon: 'file-text', onClick: () => createThenEdit('demande', 'demandes') },
      { label: 'Rendez-vous', icon: 'calendar', onClick: () => createThenEdit('rdv', 'rdv') },
    ]);
  });

  // Menu utilisateur.
  const userBtn = app.querySelector('[data-user-menu]');
  const openUserMenu = () => openMenu(userBtn, [
    { label: CFG.user.name + ' — profil', icon: 'user', href: CFG.adminUrl + 'profile.php?classic=1' },
    ...(CFG.isAdmin ? [{ label: 'Admin WordPress classique', icon: 'settings', href: CFG.adminUrl + '?classic=1' }] : []),
    'hr',
    { label: 'Déconnexion', icon: 'log-out', href: CFG.logoutUrl, danger: true },
  ]);
  userBtn.addEventListener('click', openUserMenu);
  userBtn.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openUserMenu(); } });

  // Recherche globale.
  app.querySelector('[data-open-search]').addEventListener('click', openPalette);
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openPalette(); }
  });
}

export async function createThenEdit(type, route) {
  try {
    const res = await api.post(`/item/${type}`, { title: '' });
    navigate(`/${route}/${res.id}`);
  } catch (e) {
    toast(e.message, 'error');
  }
}

function renderNav() {
  const nav = document.querySelector('[data-nav]');
  const current = location.hash.replace(/\?.*$/, '') || '#/dashboard';
  const wpMenu = CFG.wpAdminMenu || [];
  const wpOpen = localStorage.getItem('ida-wp-open') === '1';

  nav.innerHTML = NAV.map((group) => `
    ${group.section ? `<div class="ida-nav-section">${esc(group.section)}</div>` : '<div style="height:8px;"></div>'}
    ${group.items.map((item) => {
      const active = current === item.href ||
        (item.href !== '#/site' && item.href !== '#/dashboard' && current.startsWith(item.href + '/')) ||
        (item.href === '#/demandes' && current.startsWith('#/demandes'));
      const badgeCount = item.badge ? (store.badges[item.badge] || 0) : 0;
      return `<a class="ida-nav-item ${active ? 'is-active' : ''}" href="${item.href}" ${active ? 'aria-current="page"' : ''} title="${esc(item.label)}">
        ${icon(item.icon, 18)}<span>${esc(item.label)}</span>
        ${badgeCount > 0 ? `<span class="ida-nav-badge">${badgeCount}</span>` : ''}
      </a>`;
    }).join('')}
  `).join('')
  // Groupe repliable « Administration WordPress » (administrateurs uniquement) :
  // les écrans natifs restent pleinement fonctionnels, rien n'est supprimé.
  + (wpMenu.length ? `
    <button class="ida-nav-wp-toggle" data-wp-toggle aria-expanded="${wpOpen}" title="Administration WordPress">
      ${icon('wordpress', 17)}<span>Administration WordPress</span>${icon('chevron-down', 14, 'chev')}
    </button>
    <div class="ida-nav-wp" data-wp-group ${wpOpen ? '' : 'hidden'}>
      ${wpMenu.map((item) => `
        <a class="ida-nav-item" href="${esc(item.url)}" title="${esc(item.label)}">
          ${icon(item.icon, 18)}<span>${esc(item.label)}</span>
          ${item.badge > 0 ? `<span class="ida-nav-badge ida-nav-badge--info">${item.badge}</span>` : ''}
        </a>`).join('')}
    </div>` : '');

  const toggle = nav.querySelector('[data-wp-toggle]');
  if (toggle) {
    toggle.addEventListener('click', () => {
      const group = nav.querySelector('[data-wp-group]');
      const open = group.hidden;
      group.hidden = !open;
      toggle.setAttribute('aria-expanded', String(open));
      localStorage.setItem('ida-wp-open', open ? '1' : '0');
      if (open) group.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    });
  }
}

/* ── Recherche globale (Ctrl+K) ─────────────────────────────────────────── */

function openPalette() {
  if (document.querySelector('.ida-palette')) return;
  const wrap = document.createElement('div');
  wrap.className = 'ida-palette';
  wrap.innerHTML = `
    <div class="ida-palette__box" role="dialog" aria-modal="true" aria-label="Recherche globale">
      <div class="ida-palette__input">
        ${icon('search', 18)}
        <input type="text" placeholder="Pages, artisans, clients, demandes, guides…" aria-label="Rechercher">
      </div>
      <div class="ida-palette__results"></div>
      <div class="ida-palette__hint"><span>↑↓ naviguer</span><span>↵ ouvrir</span><span>Échap fermer</span></div>
    </div>`;
  document.body.appendChild(wrap);
  const inputEl = wrap.querySelector('input');
  const resultsEl = wrap.querySelector('.ida-palette__results');
  let flat = [];
  let active = 0;

  const close = () => wrap.remove();
  wrap.addEventListener('click', (e) => { if (e.target === wrap) close(); });

  const renderResults = (groups) => {
    flat = [];
    if (!groups.length) {
      resultsEl.innerHTML = '<div style="padding:22px;text-align:center;color:var(--ida-text-faint);font-size:13px;">Aucun résultat</div>';
      return;
    }
    resultsEl.innerHTML = groups.map((g) => `
      <div class="ida-palette__group">${esc(g.label)}</div>
      ${g.items.map((item) => {
        flat.push(item);
        const idx = flat.length - 1;
        return `<div class="ida-palette__item ${idx === 0 ? 'is-active' : ''}" data-idx="${idx}" role="option">
          <div><div class="t">${esc(item.title)}</div>${item.sub ? `<div class="s">${esc(item.sub)}</div>` : ''}</div>
        </div>`;
      }).join('')}`).join('');
    active = 0;
    resultsEl.querySelectorAll('[data-idx]').forEach((el) => {
      el.addEventListener('click', () => { navigate(flat[Number(el.dataset.idx)].link); close(); });
    });
  };

  const search = debounce(async () => {
    const q = inputEl.value.trim();
    if (q.length < 2) { resultsEl.innerHTML = ''; flat = []; return; }
    resultsEl.innerHTML = '<div style="padding:18px;"><div class="ida-skeleton" style="height:38px;"></div></div>';
    try {
      const res = await api.get(`/search?q=${encodeURIComponent(q)}`);
      renderResults(res.groups);
    } catch (e) {
      resultsEl.innerHTML = '';
    }
  }, 200);

  inputEl.addEventListener('input', search);
  inputEl.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { close(); return; }
    if (!flat.length) return;
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      active = (active + (e.key === 'ArrowDown' ? 1 : -1) + flat.length) % flat.length;
      resultsEl.querySelectorAll('[data-idx]').forEach((el, i) => el.classList.toggle('is-active', i === active));
      resultsEl.querySelectorAll('[data-idx]')[active]?.scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter') {
      navigate(flat[active].link);
      close();
    }
  });
  inputEl.focus();
}

/* ── Routeur ────────────────────────────────────────────────────────────── */

async function route() {
  const raw = location.hash.slice(1) || '/dashboard';
  const [path, queryString] = raw.split('?');
  const query = Object.fromEntries(new URLSearchParams(queryString || ''));
  const match = matchRoute(path) || { view: viewDashboard, params: {} };

  renderNav();
  const app = document.getElementById('app');
  app.classList.remove('is-nav-open');
  const scrim = app.querySelector('.ida-nav-scrim');
  if (scrim) scrim.hidden = true;

  const view = document.getElementById('view');
  view.innerHTML = '';
  view.focus({ preventScroll: true });
  window.scrollTo(0, 0);
  try {
    await match.view(view, { ...match.params, ...query });
  } catch (e) {
    view.innerHTML = `<div class="ida-card ida-card--pad"><h2>Une erreur est survenue</h2><p style="color:var(--ida-text-soft);">${esc(e.message)}</p></div>`;
    console.error(e);
  }
}

/* ── Démarrage ──────────────────────────────────────────────────────────── */

async function boot() {
  renderLayout();
  window.addEventListener('hashchange', route);
  try {
    const data = await api.get('/bootstrap');
    store.badges = data.badges || {};
    store.enums = data.enums || {};
    store.metiers = data.metiers || [];
  } catch (e) {
    console.error('Bootstrap :', e);
  }
  route();
  // Rafraîchit les badges toutes les 2 minutes.
  setInterval(async () => {
    try {
      const data = await api.get('/bootstrap');
      store.badges = data.badges || {};
      renderNav();
    } catch (e) { /* silencieux */ }
  }, 120000);
}

boot();
