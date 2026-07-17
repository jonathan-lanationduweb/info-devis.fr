/**
 * CRM : clients, demandes de devis (Kanban + liste), rendez-vous (calendrier).
 */

import { api, tryApi, esc } from '../api.js';
import { icon } from '../icons.js';
import {
  pageHead, mountTable, badge, toast, skeletonRows, openDrawer, openMenu,
  field, input, textarea, select, readForm, emptyState,
} from '../ui.js';
import { store, navigate, createThenEdit } from '../app.js';

/* ── Clients ────────────────────────────────────────────────────────────── */

export async function viewClients(el) {
  el.innerHTML = pageHead({ title: 'Clients', sub: 'Comptes clients du site (demandes, avis, rendez-vous).' }) + '<div data-list></div>';

  mountTable(el.querySelector('[data-list]'), {
    fetch: (state) => api.get('/list/client?' + new URLSearchParams({ page: state.page, s: state.s || '' })),
    searchPlaceholder: 'Nom, email…',
    columns: [
      { label: '', render: (r) => `<img class="cell-thumb" style="width:36px;height:36px;border-radius:50%;" src="${esc(r.avatar)}" alt="">`, cls: 'hide-sm' },
      { label: 'Client', render: (r) => `<div class="cell-main">${esc(r.title)}</div><div class="cell-sub">${esc(r.email)}</div>` },
      { label: 'Téléphone', render: (r) => esc(r.phone || '—'), cls: 'hide-sm' },
      { label: 'Ville', render: (r) => esc(r.ville || '—'), cls: 'hide-sm' },
      { label: 'Demandes', render: (r) => `<strong>${r.demandes}</strong>` },
      { label: 'Inscrit le', render: (r) => esc(r.registered), cls: 'hide-sm' },
    ],
    onRow: (row) => navigate(`/clients/${row.id}`),
    empty: { iconName: 'users', title: 'Aucun client', text: 'Les comptes clients créés sur le site apparaîtront ici.' },
  });
}

export async function viewClientDetail(el, params) {
  el.innerHTML = skeletonRows(5);
  const c = await api.get(`/clients/${params.id}`);

  el.innerHTML = `
    ${pageHead({ breadcrumb: '<a href="#/clients">Clients</a> › ' + esc(c.name), title: '' })}
    <div class="ida-dash-grid">
      <div class="ida-dash-col">
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Demandes de devis (${c.demandes.length})</h2>
          ${c.demandes.length ? `
            <div class="ida-table-scroll"><table class="ida-table">
              <thead><tr><th>Référence</th><th>Projet</th><th>Ville</th><th>Statut</th></tr></thead>
              <tbody>${c.demandes.map((d) => `
                <tr onclick="location.hash='/demandes/${d.id}'">
                  <td class="cell-main">${esc(d.reference || '—')}</td>
                  <td>${esc(d.title)}</td>
                  <td>${esc(d.ville || '—')}</td>
                  <td>${badge((store.enums.demande_status || {})[d.dstatus] || d.dstatus, d.dstatus)}</td>
                </tr>`).join('')}</tbody>
            </table></div>`
          : '<p class="ida-card__sub">Aucune demande.</p>'}
        </div>
      </div>
      <div class="ida-dash-col">
        <div class="ida-card ida-card--pad" style="text-align:center;">
          <img src="${esc(c.avatar)}" alt="" style="width:72px;height:72px;border-radius:50%;margin-bottom:10px;">
          <h2 class="ida-card__title">${esc(c.name)}</h2>
          <div class="ida-card__sub">Inscrit le ${esc(c.registered)}</div>
        </div>
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Coordonnées</h2>
          <p style="margin:6px 0;display:flex;gap:8px;align-items:center;">${icon('mail', 14)} <a href="mailto:${esc(c.email)}">${esc(c.email)}</a></p>
          ${c.phone ? `<p style="margin:6px 0;display:flex;gap:8px;align-items:center;">${icon('phone', 14)} ${esc(c.phone)}</p>` : ''}
          ${c.ville ? `<p style="margin:6px 0;display:flex;gap:8px;align-items:center;">${icon('map-pin', 14)} ${esc(c.ville)} ${esc(c.cp || '')}</p>` : ''}
        </div>
      </div>
    </div>`;
}

/* ── Demandes : Kanban + liste ──────────────────────────────────────────── */

const KANBAN_COLUMNS = [
  { key: 'pending', label: 'Nouvelle', statuses: ['pending', 'sent'] },
  { key: 'in_progress', label: 'En cours', statuses: ['in_progress'] },
  { key: 'accepted', label: 'Acceptée', statuses: ['accepted'] },
  { key: 'completed', label: 'Terminée', statuses: ['completed', 'cancelled', 'refused'] },
];

export async function viewDemandes(el, params = {}) {
  const mode = params.vue === 'liste' ? 'liste' : 'kanban';

  el.innerHTML = pageHead({
    title: 'Demandes de devis',
    actions: `
      <div class="ida-tabs" style="border:none;margin:0;">
        <button class="${mode === 'kanban' ? 'is-active' : ''}" data-mode="kanban">${icon('columns', 14)} Kanban</button>
        <button class="${mode === 'liste' ? 'is-active' : ''}" data-mode="liste">${icon('list', 14)} Liste</button>
      </div>
      <button class="ida-btn ida-btn--primary" data-add>${icon('plus', 15)} Demande</button>`,
  }) + '<div data-body></div>';

  el.querySelector('[data-add]').addEventListener('click', () => createThenEdit('demande', 'demandes'));
  el.querySelectorAll('[data-mode]').forEach((b) =>
    b.addEventListener('click', () => navigate('/demandes?vue=' + b.dataset.mode)));

  const body = el.querySelector('[data-body]');
  if (mode === 'liste') {
    renderDemandesList(body, params);
  } else {
    await renderKanban(body);
  }
}

function renderDemandesList(el, params) {
  const enums = store.enums;
  el.innerHTML = '<div data-list></div>';
  mountTable(el.querySelector('[data-list]'), {
    initialParams: { demande_status: params.statut || '' },
    fetch: (state) => api.get('/list/demande?' + new URLSearchParams({
      page: state.page, s: state.s || '', demande_status: state.demande_status || '', metier: state.metier || '',
    })),
    searchPlaceholder: 'Référence, ville, email…',
    filters: [
      { name: 'demande_status', label: 'Statut', options: enums.demande_status || {} },
      { name: 'metier', label: 'Métier', options: Object.fromEntries(store.metiers.map((m) => [m.slug, m.name])) },
    ],
    columns: [
      { label: 'Référence', render: (r) => `<div class="cell-main" style="color:var(--ida-primary);">${esc(r.reference || '—')}</div><div class="cell-sub">${esc(r.ago)}</div>` },
      { label: 'Projet', render: (r) => `<div class="cell-main">${esc(r.title)}</div><div class="cell-sub">${esc(r.metier)}</div>` },
      { label: 'Contact', render: (r) => `<div>${esc(r.contact || '—')}</div><div class="cell-sub">${esc(r.email || '')}</div>`, cls: 'hide-sm' },
      { label: 'Ville', render: (r) => esc(r.ville || '—'), cls: 'hide-sm' },
      { label: 'Urgence', render: (r) => badge((store.enums.urgency || {})[r.urgency] || r.urgency, r.urgency), cls: 'hide-sm' },
      { label: 'Statut', render: (r) => badge((store.enums.demande_status || {})[r.dstatus] || r.dstatus, r.dstatus) },
    ],
    onRow: (row) => navigate(`/demandes/${row.id}`),
    rowActions: (row, reload) => [
      { label: 'Ouvrir', icon: 'pencil', onClick: () => navigate(`/demandes/${row.id}`) },
      'hr',
      ...Object.entries(store.enums.demande_status || {}).map(([value, label]) => ({
        label: 'Statut : ' + label,
        icon: 'chevron-right',
        onClick: async () => {
          await tryApi(api.post(`/item/demande/${row.id}/action`, { action: 'demande_status', value }));
          toast('Statut mis à jour : ' + label);
          reload();
        },
      })),
    ],
    empty: { iconName: 'file-text', title: 'Aucune demande', text: 'Les demandes de devis du site apparaîtront ici.' },
  });
}

async function renderKanban(el) {
  el.innerHTML = skeletonRows(4);
  const data = await api.get('/list/demande?per_page=100');
  const rows = data.rows;

  const render = () => {
    el.innerHTML = `<div class="ida-kanban">
      ${KANBAN_COLUMNS.map((col) => {
        const cards = rows.filter((r) => col.statuses.includes(r.dstatus));
        return `
          <div class="ida-kanban__col" data-col="${col.key}">
            <div class="ida-kanban__head"><span>${esc(col.label)}</span><span class="n">${cards.length}</span></div>
            <div class="ida-kanban__cards">
              ${cards.map((r) => `
                <div class="ida-kanban-card" draggable="true" data-id="${r.id}" tabindex="0" role="button"
                     aria-label="Demande ${esc(r.reference)} — ${esc(col.label)}">
                  <div class="ref">${esc(r.reference || '#' + r.id)}</div>
                  <div class="title">${esc(r.metier !== '—' ? r.metier : r.title)}</div>
                  <div class="meta">
                    ${r.ville ? `<span>${esc(r.ville)}</span>` : ''}
                    ${r.contact ? `<span>${esc(r.contact)}</span>` : ''}
                    ${r.urgency !== 'normal' ? badge((store.enums.urgency || {})[r.urgency] || r.urgency, r.urgency) : ''}
                  </div>
                </div>`).join('')}
            </div>
          </div>`;
      }).join('')}
    </div>
    <p class="ida-card__sub" style="margin-top:10px;">Déplacez une carte pour changer son statut. Clic : ouvrir la demande.</p>`;

    let dragId = null;
    el.querySelectorAll('.ida-kanban-card').forEach((card) => {
      card.addEventListener('dragstart', () => { dragId = Number(card.dataset.id); card.classList.add('is-dragging'); });
      card.addEventListener('dragend', () => card.classList.remove('is-dragging'));
      card.addEventListener('click', () => navigate(`/demandes/${card.dataset.id}`));
      card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') navigate(`/demandes/${card.dataset.id}`);
        // Alternative clavier au drag & drop.
        if (e.key === 'm' || e.key === 'M') {
          openMenu(card, KANBAN_COLUMNS.map((col) => ({
            label: 'Déplacer vers : ' + col.label,
            icon: 'chevron-right',
            onClick: () => moveCard(Number(card.dataset.id), col.key),
          })));
        }
      });
    });
    el.querySelectorAll('.ida-kanban__col').forEach((col) => {
      col.addEventListener('dragover', (e) => { e.preventDefault(); col.classList.add('is-over'); });
      col.addEventListener('dragleave', () => col.classList.remove('is-over'));
      col.addEventListener('drop', (e) => {
        e.preventDefault();
        col.classList.remove('is-over');
        if (dragId !== null) moveCard(dragId, col.dataset.col);
      });
    });
  };

  const moveCard = async (id, colKey) => {
    const row = rows.find((r) => r.id === id);
    const col = KANBAN_COLUMNS.find((c) => c.key === colKey);
    if (!row || !col || col.statuses.includes(row.dstatus)) return;
    const newStatus = col.statuses[0];
    const old = row.dstatus;
    row.dstatus = newStatus;
    render();
    try {
      await api.post(`/item/demande/${id}/action`, { action: 'demande_status', value: newStatus });
      toast(`${row.reference || 'Demande'} → ${col.label}`);
    } catch (e) {
      row.dstatus = old;
      render();
      toast(e.message, 'error');
    }
  };

  render();
}

/* ── Détail d'une demande ───────────────────────────────────────────────── */

export async function viewDemandeDetail(el, params) {
  el.innerHTML = skeletonRows(5);
  const item = await api.get(`/item/demande/${params.id}`);
  const m = item.metas;
  const enums = item.enums;

  el.innerHTML = `
    ${pageHead({
      breadcrumb: '<a href="#/demandes">Demandes</a> › ' + esc(m.reference || item.title),
      title: '',
      actions: '<button class="ida-btn ida-btn--primary" data-save>Enregistrer</button>',
    })}
    <div class="ida-editor">
      <div>
        <input class="ida-title-input" data-title value="${esc(item.title)}" placeholder="Intitulé du projet…" aria-label="Intitulé">
        <div class="ida-card ida-card--pad" style="margin-bottom:14px;">
          <h2 class="ida-card__title">Description du projet</h2>
          ${textarea('content', item.content.replace(/<[^>]+>/g, ''), 8)}
        </div>
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Contact</h2>
          <div class="ida-form-grid">
            ${field('Nom', input('m_contact_name', m.contact_name))}
            ${field('Email', input('m_contact_email', m.contact_email, 'type="email"'))}
            ${field('Téléphone', input('m_contact_phone', m.contact_phone))}
            ${field('ID compte client', input('m_client_user_id', m.client_user_id, 'type="number"'))}
          </div>
        </div>
      </div>
      <div class="ida-editor__side">
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Suivi</h2>
          ${field('Référence', input('m_reference', m.reference))}
          ${field('Statut', select('m_status', enums.demande_status, m.status || 'pending'))}
          ${field('Urgence', select('m_urgency', enums.urgency, m.urgency || 'normal'))}
        </div>
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Localisation & budget</h2>
          <div class="ida-form-grid">
            ${field('Ville', input('m_ville', m.ville))}
            ${field('Code postal', input('m_code_postal', m.code_postal))}
            ${field('Budget min (€)', input('m_budget_min', m.budget_min, 'type="number"'))}
            ${field('Budget max (€)', input('m_budget_max', m.budget_max, 'type="number"'))}
          </div>
          ${m.budget_range ? `<div class="ida-card__sub">Fourchette indiquée : ${esc(m.budget_range)}</div>` : ''}
        </div>
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Infos</h2>
          <div class="ida-card__sub">Reçue le ${esc(item.date)}</div>
          ${m.consent_at ? `<div class="ida-card__sub">Consentement : ${esc(m.consent_at)}</div>` : ''}
        </div>
      </div>
    </div>`;

  el.querySelector('[data-save]').addEventListener('click', async () => {
    const form = readForm(el);
    const metas = {};
    Object.keys(form).forEach((k) => { if (k.startsWith('m_')) metas[k.slice(2)] = form[k]; });
    await tryApi(api.post(`/item/demande/${params.id}`, {
      title: el.querySelector('[data-title]').value,
      content: form.content,
      metas,
    }), 'Enregistrement');
    toast('Demande enregistrée ✓');
  });
}

/* ── Rendez-vous : calendrier ───────────────────────────────────────────── */

const DOW = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
const MONTHS = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

export async function viewRdv(el, params = {}) {
  // #/rdv/123 → ouvrir la fiche dans un drawer par-dessus le calendrier.
  const openId = params.id ? Number(params.id) : null;
  const now = new Date();
  let month = /^\d{4}-\d{2}$/.test(params.mois || '') ? params.mois : `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
  let mode = params.vue === 'liste' ? 'liste' : 'mois';

  el.innerHTML = pageHead({
    title: 'Rendez-vous',
    actions: `
      <div class="ida-tabs" style="border:none;margin:0;">
        <button class="${mode === 'mois' ? 'is-active' : ''}" data-vue="mois">${icon('calendar', 14)} Mois</button>
        <button class="${mode === 'liste' ? 'is-active' : ''}" data-vue="liste">${icon('list', 14)} Liste</button>
      </div>
      <button class="ida-btn ida-btn--primary" data-add>${icon('plus', 15)} Rendez-vous</button>`,
  }) + '<div data-body></div>';

  el.querySelector('[data-add]').addEventListener('click', () => createThenEdit('rdv', 'rdv'));
  el.querySelectorAll('[data-vue]').forEach((b) =>
    b.addEventListener('click', () => navigate(`/rdv?vue=${b.dataset.vue}&mois=${month}`)));

  const body = el.querySelector('[data-body]');

  if (mode === 'liste') {
    renderRdvList(body);
  } else {
    await renderCalendar(body, month);
  }

  if (openId) {
    openRdvDrawer(openId, () => navigate('/rdv'));
  }
}

function renderRdvList(el) {
  const enums = store.enums;
  el.innerHTML = '<div data-list></div>';
  mountTable(el.querySelector('[data-list]'), {
    fetch: (state) => api.get('/list/rdv?' + new URLSearchParams({
      page: state.page, s: state.s || '', rdv_status: state.rdv_status || '',
    })),
    searchPlaceholder: 'Rechercher un rendez-vous…',
    filters: [{ name: 'rdv_status', label: 'Statut', options: enums.rdv_status || {} }],
    columns: [
      { label: 'Date', render: (r) => `<div class="cell-main">${esc(r.date_rdv || '—')}</div><div class="cell-sub">${r.duree ? r.duree + ' min' : ''}</div>` },
      { label: 'Objet', render: (r) => `<div class="cell-main">${esc(r.title)}</div><div class="cell-sub">${esc(r.adresse || '')}</div>` },
      { label: 'Artisan', render: (r) => esc(r.artisan), cls: 'hide-sm' },
      { label: 'Client', render: (r) => esc(r.client), cls: 'hide-sm' },
      { label: 'Statut', render: (r) => badge((enums.rdv_status || {})[r.rstatus] || r.rstatus, r.rstatus) },
    ],
    onRow: (row) => openRdvDrawer(row.id),
    empty: { iconName: 'calendar', title: 'Aucun rendez-vous', text: 'Créez un rendez-vous avec le bouton ci-dessus.' },
  });
}

async function renderCalendar(el, month) {
  el.innerHTML = skeletonRows(4);
  const data = await api.get(`/rdv/calendar?month=${month}`);
  const [year, mon] = month.split('-').map(Number);

  const prev = new Date(year, mon - 2, 1);
  const next = new Date(year, mon, 1);
  const fmt = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;

  const first = new Date(year, mon - 1, 1);
  const startOffset = (first.getDay() + 6) % 7; // lundi = 0
  const daysInMonth = new Date(year, mon, 0).getDate();
  const today = new Date();
  const isToday = (d) => today.getFullYear() === year && today.getMonth() === mon - 1 && today.getDate() === d;

  const byDay = {};
  data.events.forEach((ev) => {
    const day = parseInt((ev.date || '').slice(8, 10), 10);
    if (!day) return;
    (byDay[day] = byDay[day] || []).push(ev);
  });

  const cells = [];
  for (let i = 0; i < startOffset; i++) cells.push('<div class="ida-cal__day is-out"></div>');
  for (let d = 1; d <= daysInMonth; d++) {
    const events = (byDay[d] || []).map((ev) => `
      <button class="ida-cal-event ida-cal-event--${esc(ev.status)}" data-rdv="${ev.id}"
              title="${esc(ev.title)} — ${esc(ev.artisan)} / ${esc(ev.client)}">
        ${esc((ev.date || '').slice(11, 16))} ${esc(ev.artisan || ev.title)}
      </button>`).join('');
    cells.push(`<div class="ida-cal__day ${isToday(d) ? 'is-today' : ''}"><div class="num">${d}</div>${events}</div>`);
  }
  while (cells.length % 7 !== 0) cells.push('<div class="ida-cal__day is-out"></div>');

  el.innerHTML = `
    <div class="ida-cal-head">
      <button class="ida-icon-btn" data-nav="${fmt(prev)}" aria-label="Mois précédent">${icon('chevron-left', 18)}</button>
      <h2>${MONTHS[mon - 1]} ${year}</h2>
      <button class="ida-icon-btn" data-nav="${fmt(next)}" aria-label="Mois suivant">${icon('chevron-right', 18)}</button>
      <span class="ida-card__sub">${data.events.length} rendez-vous ce mois</span>
    </div>
    <div class="ida-cal">
      ${DOW.map((d) => `<div class="ida-cal__dow">${d}</div>`).join('')}
      ${cells.join('')}
    </div>`;

  el.querySelectorAll('[data-nav]').forEach((b) =>
    b.addEventListener('click', () => navigate(`/rdv?mois=${b.dataset.nav}`)));
  el.querySelectorAll('[data-rdv]').forEach((b) =>
    b.addEventListener('click', () => openRdvDrawer(Number(b.dataset.rdv))));
}

async function openRdvDrawer(id, onClose) {
  let item;
  try {
    item = await api.get(`/item/rdv/${id}`);
  } catch (e) {
    toast(e.message, 'error');
    return;
  }
  const m = item.metas;
  const enums = item.enums;

  openDrawer({
    title: item.title || 'Rendez-vous',
    body: `
      ${field('Objet', input('title', item.title))}
      <div class="ida-form-grid">
        ${field('Date et heure', input('m_date_rdv', m.date_rdv, 'type="datetime-local"'))}
        ${field('Durée (minutes)', input('m_duree_min', m.duree_min, 'type="number"'))}
      </div>
      ${field('Adresse', input('m_adresse', m.adresse))}
      ${field('Statut', select('m_statut', enums.rdv_status, m.statut || 'propose'))}
      <div class="ida-form-grid">
        ${field('ID fiche artisan', input('m_artisan_post_id', m.artisan_post_id, 'type="number"'))}
        ${field('ID compte client', input('m_client_user_id', m.client_user_id, 'type="number"'))}
      </div>
      ${field('Notes', textarea('content', item.content.replace(/<[^>]+>/g, ''), 3))}`,
    footer: `
      <button class="ida-btn ida-btn--secondary" data-cancel>Fermer</button>
      <button class="ida-btn ida-btn--primary" data-save>${icon('save', 15)} Enregistrer</button>`,
    onMount: (drawer, close) => {
      // datetime-local attend « AAAA-MM-JJTHH:MM » ; les données stockent « AAAA-MM-JJ HH:MM ».
      const dateInput = drawer.querySelector('[name=m_date_rdv]');
      if (dateInput.value.includes(' ')) dateInput.value = dateInput.value.replace(' ', 'T');
      const done = () => { close(); onClose?.(); };
      drawer.querySelector('[data-cancel]').addEventListener('click', done);
      drawer.querySelector('[data-save]').addEventListener('click', async () => {
        const form = readForm(drawer.querySelector('.ida-drawer__body'));
        const metas = {};
        Object.keys(form).forEach((k) => { if (k.startsWith('m_')) metas[k.slice(2)] = form[k]; });
        metas.date_rdv = (metas.date_rdv || '').replace('T', ' ');
        await tryApi(api.post(`/item/rdv/${id}`, {
          title: form.title,
          content: form.content,
          status: 'publish',
          metas,
        }), 'Enregistrement');
        toast('Rendez-vous enregistré ✓');
        done();
        // Recharge la vue calendrier.
        if (location.hash.startsWith('#/rdv')) window.dispatchEvent(new HashChangeEvent('hashchange'));
      });
    },
  });
}
