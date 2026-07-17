/**
 * Contenus : listes génériques (pages, articles, guides, FAQ, réalisations),
 * éditeur 2 colonnes, écran Métiers (drag & drop).
 */

import { api, tryApi, esc, debounce } from '../api.js';
import { icon } from '../icons.js';
import {
  pageHead, mountTable, badge, toast, confirmModal, openDrawer, skeletonRows,
  field, input, textarea, select, switchInput, imagePickerHtml,
  mountImagePickers, readForm, richTextHtml, mountRichText, stars,
} from '../ui.js';
import { store, createThenEdit, navigate } from '../app.js';

const TYPES = {
  page:        { label: 'Pages', singular: 'page', route: 'pages', icon: 'file-text' },
  article:     { label: 'Articles', singular: 'article', route: 'articles', icon: 'newspaper' },
  guide:       { label: 'Guides & Prix', singular: 'guide', route: 'guides', icon: 'book-open' },
  faq:         { label: 'FAQ', singular: 'question', route: 'faq', icon: 'help-circle' },
  realisation: { label: 'Réalisations', singular: 'réalisation', route: 'realisations', icon: 'image' },
};

const STATUS_FILTER = { publish: 'Publié', draft: 'Brouillon', pending: 'En attente' };

function thumbCell(row) {
  return row.thumb
    ? `<img class="cell-thumb" src="${esc(row.thumb)}" alt="" loading="lazy">`
    : `<span class="cell-thumb cell-thumb--placeholder">${icon('image', 16)}</span>`;
}

/* ── Liste générique ────────────────────────────────────────────────────── */

export async function viewList(el, type, params = {}) {
  const cfg = TYPES[type];
  if (params.new === '1') {
    createThenEdit(type, cfg.route);
    return;
  }

  el.innerHTML = pageHead({
    title: cfg.label,
    actions: `<button class="ida-btn ida-btn--primary" data-add>${icon('plus', 15)} Nouveau</button>`,
  }) + '<div data-list></div>';
  el.querySelector('[data-add]').addEventListener('click', () => createThenEdit(type, cfg.route));

  const columns = {
    article: [
      { label: '', render: thumbCell, cls: 'hide-sm' },
      { label: 'Titre', render: (r) => `<div class="cell-main">${esc(r.title)}</div><div class="cell-sub">${esc(r.ago)}</div>` },
      { label: 'Catégorie', render: (r) => esc(r.category), cls: 'hide-sm' },
      { label: 'Lecture', render: (r) => esc(r.reading), cls: 'hide-sm' },
      { label: 'SEO', render: (r) => r.seo_ok ? badge('OK', 'publish') : badge('À faire', 'pending'), cls: 'hide-sm' },
      { label: 'Statut', render: (r) => badge(r.status_label, r.status) },
    ],
    guide: null, // = article
    page: [
      { label: 'Titre', render: (r) => `<div class="cell-main">${esc(r.title)}</div><div class="cell-sub">/${esc(r.slug)}/</div>` },
      { label: 'SEO', render: (r) => r.seo_ok ? badge('OK', 'publish') : badge('À faire', 'pending'), cls: 'hide-sm' },
      { label: 'Statut', render: (r) => badge(r.status_label, r.status) },
      { label: 'Date', render: (r) => esc(r.date), cls: 'hide-sm' },
    ],
    faq: [
      { label: 'Question', render: (r) => `<div class="cell-main">${esc(r.title)}</div><div class="cell-sub">${esc(r.excerpt)}</div>` },
      { label: 'Statut', render: (r) => badge(r.status_label, r.status) },
    ],
    realisation: [
      { label: '', render: thumbCell, cls: 'hide-sm' },
      { label: 'Titre', render: (r) => `<div class="cell-main">${esc(r.title)}</div><div class="cell-sub">${esc(r.artisan)}</div>` },
      { label: 'Ville', render: (r) => esc(r.ville || '—'), cls: 'hide-sm' },
      { label: 'Budget', render: (r) => r.budget ? esc(r.budget) + ' €' : '—', cls: 'hide-sm' },
      { label: 'Statut', render: (r) => badge(r.status_label, r.status) },
    ],
  };
  columns.guide = columns.article;

  mountTable(el.querySelector('[data-list]'), {
    initialParams: { status: params.statut || '' },
    fetch: (state) => api.get(`/list/${type}?` + new URLSearchParams({
      page: state.page, s: state.s || '', status: state.status || 'any', metier: state.metier || '',
    })),
    columns: columns[type],
    searchPlaceholder: `Rechercher ${cfg.singular === 'page' ? 'une' : 'un'} ${cfg.singular}…`,
    filters: [
      { name: 'status', label: 'Statut', options: STATUS_FILTER },
      ...(['article', 'guide', 'realisation'].includes(type)
        ? [{ name: 'metier', label: 'Métier', options: Object.fromEntries(store.metiers.map((m) => [m.slug, m.name])) }]
        : []),
    ],
    onRow: (row) => navigate(`/${cfg.route}/${row.id}`),
    rowActions: (row, reload) => [
      { label: 'Modifier', icon: 'pencil', onClick: () => navigate(`/${cfg.route}/${row.id}`) },
      ...(row.view ? [{ label: 'Aperçu', icon: 'external-link', href: row.view, blank: true }] : []),
      { label: 'Dupliquer', icon: 'copy', onClick: async () => {
        await tryApi(api.post(`/item/${type}/${row.id}/action`, { action: 'duplicate' }));
        toast('Élément dupliqué (brouillon)');
        reload();
      }},
      'hr',
      { label: 'Mettre à la corbeille', icon: 'trash', danger: true, onClick: async () => {
        if (!await confirmModal({ title: 'Mettre à la corbeille ?', message: `« ${row.title} » sera déplacé vers la corbeille WordPress.` })) return;
        await tryApi(api.post(`/item/${type}/${row.id}/action`, { action: 'trash' }));
        toast('Déplacé vers la corbeille');
        reload();
      }},
    ],
    empty: { title: `Aucun${cfg.singular === 'page' ? 'e' : ''} ${cfg.singular}`, text: 'Créez le premier contenu avec le bouton « Nouveau ».' },
  });
}

/* ── Éditeur 2 colonnes ─────────────────────────────────────────────────── */

export async function viewEditor(el, type, params) {
  const cfg = TYPES[type];
  el.innerHTML = skeletonRows(6);
  const item = await api.get(`/item/${type}/${params.id}`);

  const isRich = ['article', 'guide', 'page'].includes(type);
  const metierOptions = Object.fromEntries([['', '—'], ...store.metiers.map((m) => [m.id, m.name])]);

  el.innerHTML = `
    ${pageHead({
      breadcrumb: `<a href="#/${cfg.route}">${esc(cfg.label)}</a> › ${esc(item.title || 'Nouveau')}`,
      title: '',
      actions: `
        ${item.view ? `<a class="ida-btn ida-btn--ghost" href="${esc(item.view)}" target="_blank" rel="noopener">${icon('external-link', 15)} Aperçu</a>` : ''}
        ${isRich ? `<a class="ida-btn ida-btn--ghost" href="${esc(item.wp_edit)}&classic=1" target="_blank" rel="noopener" title="Ouvrir dans Gutenberg">Éditeur avancé</a>` : ''}
        <button class="ida-btn ida-btn--secondary" data-save-draft>Enregistrer</button>
        <button class="ida-btn ida-btn--primary" data-publish>${item.status === 'publish' ? 'Mettre à jour' : 'Publier'}</button>`,
    })}
    <div class="ida-editor">
      <div>
        <input class="ida-title-input" data-title value="${esc(item.title)}" placeholder="Titre…" aria-label="Titre">
        ${isRich || type === 'faq'
          ? richTextHtml(item.content)
          : `<div class="ida-card ida-card--pad">${textarea('content', item.content, 6)}</div>`}
      </div>
      <div class="ida-editor__side">
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Publication</h2>
          ${field('Statut', select('status', { publish: 'Publié', draft: 'Brouillon', pending: 'En attente' }, item.status === 'auto-draft' ? 'draft' : item.status))}
          <div class="ida-card__sub">Créé le ${esc(item.date)}</div>
        </div>
        ${isRich || type === 'realisation' ? `
          <div class="ida-card ida-card--pad">
            <h2 class="ida-card__title">Image à la une</h2>
            ${imagePickerHtml('thumbnail', item.thumbnail_url)}
          </div>` : ''}
        ${['article', 'guide', 'realisation'].includes(type) ? `
          <div class="ida-card ida-card--pad">
            <h2 class="ida-card__title">Métier</h2>
            ${field('', select('metier', metierOptions, item.metier[0] || ''))}
          </div>` : ''}
        ${type === 'realisation' ? `
          <div class="ida-card ida-card--pad">
            <h2 class="ida-card__title">Détails</h2>
            ${field('ID fiche artisan', input('m_artisan_post_id', item.metas.artisan_post_id, 'type="number"'))}
            ${field('Ville', input('m_ville', item.metas.ville))}
            ${field('Durée des travaux', input('m_duree', item.metas.duree))}
            ${field('Budget (€)', input('m_budget', item.metas.budget, 'type="number"'))}
          </div>` : ''}
        ${isRich ? `
          <div class="ida-card ida-card--pad">
            <h2 class="ida-card__title">SEO (Yoast)</h2>
            ${field('Titre SEO', input('yoast_title', item.yoast.title))}
            ${field('Meta description', textarea('yoast_description', item.yoast.description, 3))}
            <div class="ida-seo-counter" data-seo-counter>${item.yoast.description.length} / 156</div>
          </div>` : ''}
        ${type === 'article' ? `
          <div class="ida-card ida-card--pad">
            <h2 class="ida-card__title">Extrait</h2>
            ${textarea('excerpt', item.excerpt, 3)}
          </div>` : ''}
      </div>
    </div>`;

  const rich = mountRichText(el);
  mountImagePickers(el);

  const seoDesc = el.querySelector('[name=yoast_description]');
  if (seoDesc) {
    const counter = el.querySelector('[data-seo-counter]');
    seoDesc.addEventListener('input', () => {
      counter.textContent = `${seoDesc.value.length} / 156`;
      counter.classList.toggle('is-over', seoDesc.value.length > 156);
    });
  }

  async function save(statusOverride) {
    const form = readForm(el);
    const metas = {};
    Object.keys(form).forEach((k) => {
      if (k.startsWith('m_')) metas[k.slice(2)] = form[k];
    });
    const thumbEl = el.querySelector('[name=thumbnail]');
    const payload = {
      title: el.querySelector('[data-title]').value,
      content: rich ? rich.get() : (form.content ?? item.content),
      status: statusOverride || form.status,
      metas,
      ...(form.excerpt !== undefined ? { excerpt: form.excerpt } : {}),
      ...(form.metier !== undefined ? { metier: form.metier ? [Number(form.metier)] : [] } : {}),
      ...(thumbEl ? { thumbnail_id: thumbEl.dataset.mediaId ? Number(thumbEl.dataset.mediaId) : (thumbEl.value ? item.thumbnail_id : 0) } : {}),
      ...(form.yoast_title !== undefined ? { yoast: { title: form.yoast_title, description: form.yoast_description } } : {}),
    };
    await tryApi(api.post(`/item/${type}/${params.id}`, payload), 'Enregistrement');
    toast(statusOverride === 'publish' ? 'Publié ✓' : 'Enregistré ✓');
    if (statusOverride === 'publish') {
      el.querySelector('[name=status]').value = 'publish';
      el.querySelector('[data-publish]').textContent = 'Mettre à jour';
    }
  }

  el.querySelector('[data-save-draft]').addEventListener('click', () => save());
  el.querySelector('[data-publish]').addEventListener('click', () => save('publish'));

  // Ctrl+S
  el.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') { e.preventDefault(); save(); }
  });
}

/* ── Métiers (cartes + drag & drop) ─────────────────────────────────────── */

export async function viewMetiers(el) {
  el.innerHTML = pageHead({
    title: 'Métiers',
    sub: 'Glissez-déposez pour définir l\'ordre d\'affichage sur le site.',
    actions: `<button class="ida-btn ida-btn--primary" data-add>${icon('plus', 15)} Ajouter un métier</button>`,
  }) + `
    <div class="ida-toolbar">
      <div class="ida-search-input">${icon('search', 16)}<input type="search" placeholder="Rechercher un métier…" aria-label="Rechercher"></div>
    </div>
    <div data-rows style="display:flex;flex-direction:column;gap:8px;">${skeletonRows(6)}</div>`;

  let rows = (await api.get('/metiers')).rows;
  const rowsEl = el.querySelector('[data-rows]');
  let filter = '';

  const render = () => {
    const visible = rows.filter((r) => r.name.toLowerCase().includes(filter));
    rowsEl.innerHTML = visible.length ? visible.map((m) => `
      <div class="ida-metier-row" draggable="true" data-id="${m.id}">
        <span class="grip" aria-hidden="true">${icon('grip-vertical', 16)}</span>
        ${m.image ? `<img class="thumb" src="${esc(m.image)}" alt="" loading="lazy">` : `<span class="thumb" style="display:grid;place-items:center;color:var(--ida-text-faint);">${icon('wrench', 16)}</span>`}
        <div class="info">
          <strong>${esc(m.name)}</strong>
          <small>${m.artisans} artisan${m.artisans > 1 ? 's' : ''} · ${m.guides} guide${m.guides > 1 ? 's' : ''}${m.hidden ? ' · masqué' : ''}</small>
        </div>
        <div class="actions">
          ${m.seo_ok ? badge('SEO OK', 'publish') : badge('SEO à faire', 'pending')}
          <button class="ida-icon-btn" data-toggle="${m.id}" title="${m.hidden ? 'Rendre visible' : 'Masquer sur le site'}" aria-label="Visibilité">${icon(m.hidden ? 'eye-off' : 'eye', 16)}</button>
          <button class="ida-btn ida-btn--secondary ida-btn--sm" data-edit="${m.id}">${icon('pencil', 13)} Modifier</button>
          <a class="ida-icon-btn" href="${esc(m.view)}" target="_blank" rel="noopener" title="Aperçu" aria-label="Aperçu">${icon('external-link', 15)}</a>
        </div>
      </div>`).join('')
      : '<div class="ida-card ida-card--pad" style="text-align:center;color:var(--ida-text-soft);">Aucun métier trouvé.</div>';

    bindDnd();
    rowsEl.querySelectorAll('[data-edit]').forEach((b) =>
      b.addEventListener('click', () => openMetierDrawer(rows.find((r) => r.id === Number(b.dataset.edit)))));
    rowsEl.querySelectorAll('[data-toggle]').forEach((b) =>
      b.addEventListener('click', async () => {
        const m = rows.find((r) => r.id === Number(b.dataset.toggle));
        const res = await tryApi(api.post(`/metiers/${m.id}`, { hidden: !m.hidden }));
        Object.assign(m, res.row);
        toast(m.hidden ? 'Métier masqué sur le site' : 'Métier visible');
        render();
      }));
  };

  let dragId = null;
  const bindDnd = () => {
    rowsEl.querySelectorAll('.ida-metier-row').forEach((row) => {
      row.addEventListener('dragstart', () => { dragId = Number(row.dataset.id); row.classList.add('is-dragging'); });
      row.addEventListener('dragend', () => { row.classList.remove('is-dragging'); rowsEl.querySelectorAll('.is-over').forEach((r) => r.classList.remove('is-over')); });
      row.addEventListener('dragover', (e) => { e.preventDefault(); row.classList.add('is-over'); });
      row.addEventListener('dragleave', () => row.classList.remove('is-over'));
      row.addEventListener('drop', async (e) => {
        e.preventDefault();
        const targetId = Number(row.dataset.id);
        if (dragId === null || dragId === targetId) return;
        const from = rows.findIndex((r) => r.id === dragId);
        const to = rows.findIndex((r) => r.id === targetId);
        rows.splice(to, 0, rows.splice(from, 1)[0]);
        render();
        await tryApi(api.post('/metiers/reorder', { ids: rows.map((r) => r.id) }), 'Réordonnancement');
        toast('Ordre enregistré');
      });
    });
  };

  const openMetierDrawer = (m) => {
    const isNew = !m;
    m = m || { name: '', description: '', image: '', icon: '', prix_min: '', prix_max: '', meta_title: '', meta_description: '', hidden: false };
    openDrawer({
      title: isNew ? 'Ajouter un métier' : m.name,
      width: 520,
      body: `
        ${field('Nom', input('name', m.name))}
        ${field('Description', textarea('description', m.description, 3))}
        ${field('Image de couverture', imagePickerHtml('image', m.image))}
        ${field('Icône Font Awesome', input('icon', m.icon, 'placeholder="fa-solid fa-bolt"'), 'Classe CSS utilisée sur le site (facultatif).')}
        <div class="ida-form-grid">
          ${field('Prix min (€)', input('prix_min', m.prix_min, 'type="number"'))}
          ${field('Prix max (€)', input('prix_max', m.prix_max, 'type="number"'))}
        </div>
        <hr style="border:none;border-top:1px solid var(--ida-border);margin:16px 0;">
        <h3 style="font-size:13px;margin:0 0 12px;">SEO</h3>
        ${field('Meta title', input('meta_title', m.meta_title))}
        ${field('Meta description', textarea('meta_description', m.meta_description, 3))}
        ${field('', switchInput('hidden', m.hidden, 'Masquer ce métier sur le site'))}`,
      footer: `
        <button class="ida-btn ida-btn--secondary" data-cancel>Annuler</button>
        <button class="ida-btn ida-btn--primary" data-save>${icon('save', 15)} Enregistrer</button>`,
      onMount: (drawer, close) => {
        mountImagePickers(drawer);
        drawer.querySelector('[data-cancel]').addEventListener('click', close);
        drawer.querySelector('[data-save]').addEventListener('click', async () => {
          const form = readForm(drawer.querySelector('.ida-drawer__body'));
          if (!form.name.trim()) { toast('Le nom est obligatoire', 'error'); return; }
          if (isNew) {
            await tryApi(api.post('/metiers', form), 'Création');
            rows = (await api.get('/metiers')).rows;
          } else {
            const res = await tryApi(api.post(`/metiers/${m.id}`, form), 'Enregistrement');
            Object.assign(rows.find((r) => r.id === m.id), res.row);
          }
          toast('Métier enregistré');
          close();
          render();
        });
      },
    });
  };

  el.querySelector('[data-add]').addEventListener('click', () => openMetierDrawer(null));
  el.querySelector('input[type=search]').addEventListener('input', debounce((e) => {
    filter = e.target.value.trim().toLowerCase();
    render();
  }, 150));

  render();
}
