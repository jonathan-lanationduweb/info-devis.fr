/**
 * Composants UI réutilisables — aucun framework.
 * Chaque composant rend du HTML (chaîne) ou monte un comportement sur un nœud.
 */

import { icon } from './icons.js';
import { esc, debounce, uploadMedia } from './api.js';

/* ── Toasts ─────────────────────────────────────────────────────────────── */

let toastRoot = null;
export function toast(message, type = 'success') {
  if (!toastRoot) {
    toastRoot = document.createElement('div');
    toastRoot.className = 'ida-toasts';
    document.body.appendChild(toastRoot);
  }
  const el = document.createElement('div');
  el.className = `ida-toast ida-toast--${type}`;
  el.setAttribute('role', 'status');
  el.innerHTML = `${icon(type === 'success' ? 'check-circle' : 'alert-triangle', 17)}<span>${esc(message)}</span>`;
  toastRoot.appendChild(el);
  setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; }, 3200);
  setTimeout(() => el.remove(), 3600);
}

/* ── Badges ─────────────────────────────────────────────────────────────── */

const TONES = {
  // statuts de publication
  publish: 'success', draft: 'neutral', pending: 'warning', trash: 'danger', future: 'info', private: 'neutral',
  // demandes
  sent: 'info', accepted: 'info', in_progress: 'info', completed: 'success', cancelled: 'neutral', refused: 'danger',
  // avis
  approved: 'success', reported: 'danger', hidden: 'neutral',
  // vérification
  validated: 'success',
  // rdv
  propose: 'warning', confirme: 'success', annule: 'neutral', termine: 'info',
  // plans
  gratuit: 'neutral', starter: 'info', pro: 'info', illimite: 'navy', silver: 'info', gold: 'gold',
  // badges artisan
  referenced: 'neutral', verified: 'success', verified_pro: 'info', premium: 'gold',
  // urgence
  normal: 'neutral', urgent: 'warning', tres_urgent: 'danger',
};

export function badge(label, key = '') {
  const tone = TONES[key] || TONES[label] || 'neutral';
  return `<span class="ida-badge ida-badge--${tone}">${esc(label)}</span>`;
}

export function stars(n) {
  n = parseInt(n, 10) || 0;
  return `<span class="ida-stars" title="${n}/5" aria-label="${n} sur 5">${'★'.repeat(n)}${'☆'.repeat(5 - n)}</span>`;
}

/* ── Overlays (drawer, modal, confirm) ──────────────────────────────────── */

function trapFocus(container) {
  const selector = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
  container.addEventListener('keydown', (e) => {
    if (e.key !== 'Tab') return;
    const nodes = [...container.querySelectorAll(selector)].filter((n) => !n.disabled && n.offsetParent !== null);
    if (!nodes.length) return;
    const first = nodes[0];
    const last = nodes[nodes.length - 1];
    if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
    else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
  });
}

/**
 * Drawer latéral. openDrawer({title, body, footer, onMount, onClose})
 * body/footer : HTML. onMount(bodyEl, close) branche les comportements.
 */
export function openDrawer({ title, body = '', footer = '', onMount, width }) {
  const previous = document.activeElement;
  const overlay = document.createElement('div');
  overlay.className = 'ida-overlay';
  const drawer = document.createElement('aside');
  drawer.className = 'ida-drawer';
  if (width) drawer.style.width = `min(${width}px, 100vw)`;
  drawer.setAttribute('role', 'dialog');
  drawer.setAttribute('aria-modal', 'true');
  drawer.setAttribute('aria-label', title);
  drawer.innerHTML = `
    <div class="ida-drawer__head">
      <h2>${esc(title)}</h2>
      <button class="ida-icon-btn" data-close aria-label="Fermer">${icon('x', 18)}</button>
    </div>
    <div class="ida-drawer__body">${body}</div>
    ${footer ? `<div class="ida-drawer__foot">${footer}</div>` : ''}`;

  const close = () => {
    overlay.remove();
    drawer.remove();
    document.removeEventListener('keydown', onKey);
    if (previous) previous.focus();
  };
  const onKey = (e) => { if (e.key === 'Escape') close(); };

  overlay.addEventListener('click', close);
  drawer.querySelector('[data-close]').addEventListener('click', close);
  document.addEventListener('keydown', onKey);
  document.body.append(overlay, drawer);
  trapFocus(drawer);
  const focusable = drawer.querySelector('input, textarea, select, button:not([data-close])');
  if (focusable) focusable.focus();
  if (onMount) onMount(drawer, close);
  return close;
}

/** Confirmation destructive. */
export function confirmModal({ title, message, confirmLabel = 'Supprimer', danger = true }) {
  return new Promise((resolve) => {
    const previous = document.activeElement;
    const overlay = document.createElement('div');
    overlay.className = 'ida-overlay';
    const modal = document.createElement('div');
    modal.className = 'ida-modal';
    modal.innerHTML = `
      <div class="ida-modal__box" role="dialog" aria-modal="true" aria-label="${esc(title)}">
        <h2>${esc(title)}</h2>
        <p>${esc(message)}</p>
        <div class="ida-modal__actions">
          <button class="ida-btn ida-btn--secondary" data-cancel>Annuler</button>
          <button class="ida-btn ${danger ? 'ida-btn--danger' : 'ida-btn--primary'}" data-ok>${esc(confirmLabel)}</button>
        </div>
      </div>`;
    const done = (result) => {
      overlay.remove(); modal.remove();
      if (previous) previous.focus();
      resolve(result);
    };
    overlay.addEventListener('click', () => done(false));
    modal.querySelector('[data-cancel]').addEventListener('click', () => done(false));
    modal.querySelector('[data-ok]').addEventListener('click', () => done(true));
    modal.addEventListener('keydown', (e) => { if (e.key === 'Escape') done(false); });
    document.body.append(overlay, modal);
    trapFocus(modal);
    modal.querySelector('[data-ok]').focus();
  });
}

/* ── Menu contextuel ────────────────────────────────────────────────────── */

/**
 * openMenu(anchorEl, items) — items : [{label, icon, danger, href, onClick}] ou 'hr'.
 */
export function openMenu(anchor, items) {
  closeMenus();
  const menu = document.createElement('div');
  menu.className = 'ida-menu';
  menu.setAttribute('role', 'menu');
  menu.innerHTML = items.map((item, i) => {
    if (item === 'hr') return '<hr>';
    const inner = `${item.icon ? icon(item.icon, 15) : ''}<span>${esc(item.label)}</span>`;
    return item.href
      ? `<a role="menuitem" href="${esc(item.href)}" ${item.blank ? 'target="_blank" rel="noopener"' : ''} class="${item.danger ? 'is-danger' : ''}">${inner}</a>`
      : `<button role="menuitem" data-i="${i}" class="${item.danger ? 'is-danger' : ''}">${inner}</button>`;
  }).join('');

  document.body.appendChild(menu);
  const r = anchor.getBoundingClientRect();
  const mw = menu.offsetWidth;
  menu.style.top = `${Math.min(r.bottom + 4 + window.scrollY, window.scrollY + window.innerHeight - menu.offsetHeight - 12)}px`;
  menu.style.left = `${Math.max(8, r.right - mw + window.scrollX)}px`;

  menu.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-i]');
    if (btn) {
      closeMenus();
      items[Number(btn.dataset.i)].onClick?.();
    }
  });
  setTimeout(() => {
    document.addEventListener('click', closeMenus, { once: true });
    document.addEventListener('keydown', escMenus);
  });
}
function escMenus(e) { if (e.key === 'Escape') closeMenus(); }
export function closeMenus() {
  document.querySelectorAll('.ida-menu').forEach((m) => m.remove());
  document.removeEventListener('keydown', escMenus);
}

/* ── États ──────────────────────────────────────────────────────────────── */

export function emptyState({ iconName = 'inbox', title, text = '', action = '' }) {
  return `<div class="ida-empty">${icon(iconName, 40)}<h3>${esc(title)}</h3><p>${esc(text)}</p>${action}</div>`;
}

export function skeletonRows(n = 5) {
  return Array.from({ length: n }, () =>
    '<div class="ida-skeleton" style="height:52px;margin-bottom:8px;"></div>').join('');
}

/* ── DataTable ──────────────────────────────────────────────────────────── */

/**
 * Table générique avec recherche/filtres/pagination côté serveur.
 * mountTable(el, {
 *   fetch(params) → {rows,total,pages,page},
 *   columns: [{label, render(row), cls}],
 *   filters: [{name, label, options:{value:label}}],
 *   searchPlaceholder, rowActions(row) → items de menu,
 *   onRow(row), empty: {title, text, action}, initialParams
 * })
 */
export function mountTable(el, cfg) {
  const state = { page: 1, s: '', ...(cfg.initialParams || {}) };

  el.innerHTML = `
    <div class="ida-toolbar">
      <div class="ida-search-input">${icon('search', 16)}
        <input type="search" placeholder="${esc(cfg.searchPlaceholder || 'Rechercher…')}" aria-label="Rechercher">
      </div>
      ${(cfg.filters || []).map((f) => `
        <select class="ida-select" data-filter="${f.name}" aria-label="${esc(f.label)}">
          <option value="">${esc(f.label)}</option>
          ${Object.entries(f.options).map(([v, l]) =>
            `<option value="${esc(v)}" ${state[f.name] === v ? 'selected' : ''}>${esc(l)}</option>`).join('')}
        </select>`).join('')}
    </div>
    <div data-table></div>`;

  const tableEl = el.querySelector('[data-table]');
  const searchInput = el.querySelector('input[type=search]');
  if (state.s) searchInput.value = state.s;

  async function load() {
    tableEl.innerHTML = skeletonRows(6);
    let data;
    try {
      data = await cfg.fetch(state);
    } catch (e) {
      tableEl.innerHTML = emptyState({ iconName: 'alert-triangle', title: 'Erreur de chargement', text: e.message });
      return;
    }
    if (!data.rows.length) {
      tableEl.innerHTML = `<div class="ida-table-wrap">${emptyState(cfg.empty || { title: 'Aucun résultat', text: 'Modifiez votre recherche ou vos filtres.' })}</div>`;
      return;
    }
    tableEl.innerHTML = `
      <div class="ida-table-wrap"><div class="ida-table-scroll">
      <table class="ida-table">
        <thead><tr>${cfg.columns.map((c) => `<th scope="col" class="${c.cls || ''}">${esc(c.label)}</th>`).join('')}${cfg.rowActions ? '<th></th>' : ''}</tr></thead>
        <tbody>
          ${data.rows.map((row, i) => `
            <tr data-row="${i}">
              ${cfg.columns.map((c) => `<td class="${c.cls || ''}">${c.render(row)}</td>`).join('')}
              ${cfg.rowActions ? `<td class="ida-row-actions" style="width:40px;"><button class="ida-icon-btn" data-actions="${i}" aria-label="Actions">${icon('more-horizontal', 17)}</button></td>` : ''}
            </tr>`).join('')}
        </tbody>
      </table></div>
      ${data.pages > 1 ? `
        <div class="ida-pagination">
          <span>${data.total} élément${data.total > 1 ? 's' : ''}</span>
          <div class="pages">
            <button data-page="${data.page - 1}" ${data.page <= 1 ? 'disabled' : ''} aria-label="Précédent">‹</button>
            ${Array.from({ length: data.pages }, (unused, p) => p + 1)
              .filter((p) => Math.abs(p - data.page) < 3 || p === 1 || p === data.pages)
              .map((p) => `<button data-page="${p}" class="${p === data.page ? 'is-active' : ''}">${p}</button>`).join('')}
            <button data-page="${data.page + 1}" ${data.page >= data.pages ? 'disabled' : ''} aria-label="Suivant">›</button>
          </div>
        </div>` : ''}
      </div>`;

    tableEl.querySelectorAll('[data-page]').forEach((b) =>
      b.addEventListener('click', () => { state.page = Number(b.dataset.page); load(); }));

    tableEl.querySelectorAll('tbody tr').forEach((tr) => {
      tr.addEventListener('click', (e) => {
        if (e.target.closest('[data-actions]') || e.target.closest('a')) return;
        cfg.onRow?.(data.rows[Number(tr.dataset.row)]);
      });
    });
    if (cfg.rowActions) {
      tableEl.querySelectorAll('[data-actions]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          openMenu(btn, cfg.rowActions(data.rows[Number(btn.dataset.actions)], load));
        });
      });
    }
  }

  searchInput.addEventListener('input', debounce(() => {
    state.s = searchInput.value.trim();
    state.page = 1;
    load();
  }, 250));
  el.querySelectorAll('[data-filter]').forEach((sel) => {
    sel.addEventListener('change', () => {
      state[sel.dataset.filter] = sel.value;
      state.page = 1;
      load();
    });
  });

  load();
  return { reload: load, state };
}

/* ── Champs de formulaire ───────────────────────────────────────────────── */

export function field(label, inputHtml, hint = '') {
  return `<div class="ida-field"><label>${esc(label)}</label>${inputHtml}${hint ? `<div class="hint">${esc(hint)}</div>` : ''}</div>`;
}

export function input(name, value = '', attrs = '') {
  return `<input class="ida-input" name="${esc(name)}" value="${esc(value)}" ${attrs}>`;
}

export function textarea(name, value = '', rows = 3) {
  return `<textarea class="ida-textarea" name="${esc(name)}" rows="${rows}">${esc(value)}</textarea>`;
}

export function select(name, options, value = '') {
  return `<select class="ida-select" name="${esc(name)}" style="width:100%;">
    ${Object.entries(options).map(([v, l]) =>
      `<option value="${esc(v)}" ${String(value) === String(v) ? 'selected' : ''}>${esc(l)}</option>`).join('')}
  </select>`;
}

export function switchInput(name, checked, label = '') {
  return `<label class="ida-field--row" style="cursor:pointer;">
    ${label ? `<span style="font-size:13px;font-weight:600;">${esc(label)}</span>` : ''}
    <span class="ida-switch"><input type="checkbox" name="${esc(name)}" ${checked ? 'checked' : ''}><span class="track"></span></span>
  </label>`;
}

/** Lit tous les champs nommés d'un conteneur → objet. */
export function readForm(container) {
  const data = {};
  container.querySelectorAll('[name]').forEach((n) => {
    data[n.name] = n.type === 'checkbox' ? n.checked : n.value;
  });
  return data;
}

/* ── Sélecteur d'image (upload wp/v2/media) ─────────────────────────────── */

export function imagePickerHtml(name, url = '') {
  return `
    <div class="ida-image-picker" data-picker="${esc(name)}">
      ${url
        ? `<img class="ida-image-picker__preview" src="${esc(url)}" alt="">`
        : `<span class="ida-image-picker__empty">${icon('image', 20)}</span>`}
      <input type="hidden" name="${esc(name)}" value="${esc(url)}">
      <div style="display:flex;gap:6px;flex-direction:column;">
        <button type="button" class="ida-btn ida-btn--secondary ida-btn--sm" data-upload>${icon('image', 14)} Choisir</button>
        <button type="button" class="ida-btn ida-btn--ghost ida-btn--sm" data-clear ${url ? '' : 'style="display:none;"'}>Retirer</button>
      </div>
    </div>`;
}

export function mountImagePickers(container, onChange) {
  container.querySelectorAll('[data-picker]').forEach((picker) => {
    const hidden = picker.querySelector('input[type=hidden]');
    const clearBtn = picker.querySelector('[data-clear]');
    const setPreview = (url) => {
      const old = picker.querySelector('.ida-image-picker__preview, .ida-image-picker__empty');
      const el = document.createElement(url ? 'img' : 'span');
      if (url) { el.className = 'ida-image-picker__preview'; el.src = url; el.alt = ''; }
      else { el.className = 'ida-image-picker__empty'; el.innerHTML = icon('image', 20); }
      old.replaceWith(el);
      clearBtn.style.display = url ? '' : 'none';
    };
    picker.querySelector('[data-upload]').addEventListener('click', () => {
      const file = document.createElement('input');
      file.type = 'file';
      file.accept = 'image/*';
      file.onchange = async () => {
        if (!file.files[0]) return;
        toast('Envoi de l\'image…');
        try {
          const media = await uploadMedia(file.files[0]);
          hidden.value = media.url;
          hidden.dataset.mediaId = media.id;
          setPreview(media.url);
          onChange?.(media);
        } catch (e) {
          toast(e.message, 'error');
        }
      };
      file.click();
    });
    clearBtn.addEventListener('click', () => {
      hidden.value = '';
      hidden.dataset.mediaId = '';
      setPreview('');
      onChange?.(null);
    });
  });
}

/* ── Éditeur riche léger ────────────────────────────────────────────────── */

export function richTextHtml(content = '') {
  return `
    <div class="ida-richtext">
      <div class="ida-richtext__bar" role="toolbar" aria-label="Mise en forme">
        <button type="button" data-cmd="bold" title="Gras"><b>B</b></button>
        <button type="button" data-cmd="italic" title="Italique"><i>I</i></button>
        <button type="button" data-block="h2" title="Titre 2">H2</button>
        <button type="button" data-block="h3" title="Titre 3">H3</button>
        <button type="button" data-block="p" title="Paragraphe">¶</button>
        <button type="button" data-cmd="insertUnorderedList" title="Liste">${icon('list', 15)}</button>
        <button type="button" data-link title="Lien">${icon('link', 15)}</button>
        <button type="button" data-img title="Image">${icon('image', 15)}</button>
      </div>
      <div class="ida-richtext__body" contenteditable="true" data-rich>${content}</div>
    </div>`;
}

export function mountRichText(container) {
  const root = container.querySelector('.ida-richtext');
  if (!root) return null;
  const body = root.querySelector('[data-rich]');
  root.querySelectorAll('[data-cmd]').forEach((b) =>
    b.addEventListener('click', () => { document.execCommand(b.dataset.cmd); body.focus(); }));
  root.querySelectorAll('[data-block]').forEach((b) =>
    b.addEventListener('click', () => { document.execCommand('formatBlock', false, b.dataset.block); body.focus(); }));
  root.querySelector('[data-link]').addEventListener('click', () => {
    const url = prompt('URL du lien :');
    if (url) document.execCommand('createLink', false, url);
    body.focus();
  });
  root.querySelector('[data-img]').addEventListener('click', () => {
    const file = document.createElement('input');
    file.type = 'file';
    file.accept = 'image/*';
    file.onchange = async () => {
      if (!file.files[0]) return;
      toast('Envoi de l\'image…');
      try {
        const media = await uploadMedia(file.files[0]);
        document.execCommand('insertImage', false, media.url);
      } catch (e) {
        toast(e.message, 'error');
      }
    };
    file.click();
  });
  return { get: () => body.innerHTML };
}

/* ── Graphique barres SVG ───────────────────────────────────────────────── */

export function barChart(data, height = 150) {
  const width = 600;
  const max = Math.max(1, ...data.map((d) => d.value));
  const barW = width / data.length;
  const bars = data.map((d, i) => {
    const h = Math.max(3, (d.value / max) * (height - 46));
    const x = i * barW + barW * 0.2;
    const cx = i * barW + barW / 2;
    return `
      <rect class="bar" x="${x}" y="${height - 24 - h}" width="${barW * 0.6}" height="${h}" rx="5">
        <title>${esc(d.label)} : ${d.value}</title>
      </rect>
      <text x="${cx}" y="${height - 7}" text-anchor="middle">${esc(d.label)}</text>
      <text x="${cx}" y="${height - 31 - h}" text-anchor="middle" font-weight="700" fill="#1a2433">${d.value || ''}</text>`;
  }).join('');
  return `<svg class="ida-chart-svg" viewBox="0 0 ${width} ${height}" role="img" aria-label="Graphique">${bars}</svg>`;
}

/* ── En-tête de page ────────────────────────────────────────────────────── */

export function pageHead({ title, count, sub = '', actions = '', breadcrumb = '' }) {
  return `
    ${breadcrumb ? `<div class="ida-breadcrumb">${breadcrumb}</div>` : ''}
    <div class="ida-page-head">
      <div>
        <h1>${esc(title)}${count !== undefined ? `<span class="count">${count}</span>` : ''}</h1>
        ${sub ? `<p class="ida-page-head__sub">${esc(sub)}</p>` : ''}
      </div>
      <div class="ida-page-head__actions">${actions}</div>
    </div>`;
}

/* ── Onglets ────────────────────────────────────────────────────────────── */

export function mountTabs(el, tabs, activeKey, onChange) {
  el.innerHTML = `<div class="ida-tabs" role="tablist">
    ${tabs.map((t) => `<button role="tab" data-tab="${t.key}" aria-selected="${t.key === activeKey}" class="${t.key === activeKey ? 'is-active' : ''}">${esc(t.label)}</button>`).join('')}
  </div>`;
  el.querySelectorAll('[data-tab]').forEach((b) => {
    b.addEventListener('click', () => {
      el.querySelectorAll('[data-tab]').forEach((x) => { x.classList.remove('is-active'); x.setAttribute('aria-selected', 'false'); });
      b.classList.add('is-active');
      b.setAttribute('aria-selected', 'true');
      onChange(b.dataset.tab);
    });
  });
}
