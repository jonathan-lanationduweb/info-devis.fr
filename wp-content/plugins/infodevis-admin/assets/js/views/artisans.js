/**
 * Artisans (liste, fiche, vérifications) + modération des avis.
 */

import { api, tryApi, esc } from '../api.js';
import { icon } from '../icons.js';
import {
  pageHead, mountTable, badge, stars, toast, confirmModal, skeletonRows,
  field, input, textarea, select, switchInput, imagePickerHtml,
  mountImagePickers, readForm, richTextHtml, mountRichText,
} from '../ui.js';
import { store, navigate, createThenEdit } from '../app.js';

/* ── Liste des artisans ─────────────────────────────────────────────────── */

export async function viewArtisans(el, params = {}) {
  const verifOnly = !!params.verifications;

  el.innerHTML = pageHead({
    title: verifOnly ? 'Vérifications' : 'Artisans',
    sub: verifOnly ? 'Artisans en attente de validation (fiche, SIRET, badge).' : '',
    actions: `
      ${verifOnly ? '<a class="ida-btn ida-btn--secondary" href="#/artisans">Tous les artisans</a>'
                  : '<a class="ida-btn ida-btn--secondary" href="#/artisans/verifications">Vérifications</a>'}
      <button class="ida-btn ida-btn--primary" data-add>${icon('plus', 15)} Ajouter</button>`,
  }) + '<div data-list></div>';
  el.querySelector('[data-add]').addEventListener('click', () => createThenEdit('artisan', 'artisans'));

  const enums = store.enums;

  mountTable(el.querySelector('[data-list]'), {
    initialParams: verifOnly ? { verification: 'pending' } : {},
    fetch: (state) => api.get('/list/artisan?' + new URLSearchParams({
      page: state.page, s: state.s || '', status: state.status || 'any',
      plan: state.plan || '', badge: state.badge || '', verification: state.verification || '',
      metier: state.metier || '',
    })),
    searchPlaceholder: 'Nom, ville, SIRET…',
    filters: [
      { name: 'verification', label: 'Vérification', options: enums.verification || {} },
      { name: 'plan', label: 'Abonnement', options: enums.plan || {} },
      { name: 'badge', label: 'Badge', options: enums.badge || {} },
      { name: 'metier', label: 'Métier', options: Object.fromEntries(store.metiers.map((m) => [m.slug, m.name])) },
    ],
    columns: [
      { label: '', render: (r) => r.thumb
          ? `<img class="cell-thumb" style="border-radius:50%;" src="${esc(r.thumb)}" alt="" loading="lazy">`
          : `<span class="cell-thumb cell-thumb--placeholder" style="border-radius:50%;">${icon('hard-hat', 16)}</span>`, cls: 'hide-sm' },
      { label: 'Nom', render: (r) => `
          <div class="cell-main">${esc(r.title)} ${r.siret_ok ? `<span title="SIRET vérifié" style="color:var(--ida-success);">${icon('check-circle', 13)}</span>` : ''}</div>
          <div class="cell-sub">${esc(r.company || '')}</div>` },
      { label: 'Ville', render: (r) => esc(r.ville || '—'), cls: 'hide-sm' },
      { label: 'Ancienneté', render: (r) => r.experience ? `${r.experience} ans` : '—', cls: 'hide-sm' },
      { label: 'Note', render: (r) => r.rating ? `${esc(r.rating)}/5 <span class="cell-sub">(${r.rating_count})</span>` : '—', cls: 'hide-sm' },
      { label: 'Badge', render: (r) => badge((enums.badge || {})[r.badge] || r.badge, r.badge) },
      { label: 'Abonnement', render: (r) => badge((enums.plan || {})[r.plan] || r.plan, r.plan) },
      { label: 'Statut', render: (r) => badge((enums.verification || {})[r.verification] || r.verification, r.verification) },
    ],
    onRow: (row) => navigate(`/artisans/${row.id}`),
    rowActions: (row, reload) => [
      { label: 'Modifier', icon: 'pencil', onClick: () => navigate(`/artisans/${row.id}`) },
      ...(row.view ? [{ label: 'Voir la fiche', icon: 'external-link', href: row.view, blank: true }] : []),
      'hr',
      { label: 'Valider la vérification', icon: 'shield-check', onClick: async () => {
        await tryApi(api.post(`/item/artisan/${row.id}/action`, { action: 'verify', value: 'validated' }));
        toast('Artisan vérifié et publié');
        reload();
      }},
      { label: 'Refuser', icon: 'x-circle', onClick: async () => {
        await tryApi(api.post(`/item/artisan/${row.id}/action`, { action: 'verify', value: 'refused' }));
        toast('Vérification refusée');
        reload();
      }},
      { label: 'Suspendre (brouillon)', icon: 'eye-off', danger: true, onClick: async () => {
        if (!await confirmModal({ title: 'Suspendre cet artisan ?', message: 'La fiche ne sera plus visible sur le site (statut brouillon).', confirmLabel: 'Suspendre' })) return;
        await tryApi(api.post(`/item/artisan/${row.id}/action`, { action: 'suspend' }));
        toast('Artisan suspendu');
        reload();
      }},
    ],
    empty: { iconName: 'hard-hat', title: 'Aucun artisan', text: verifOnly ? 'Aucune vérification en attente. 🎉' : 'Ajoutez le premier artisan.' },
  });
}

/* ── Fiche artisan ──────────────────────────────────────────────────────── */

export async function viewArtisanEdit(el, params) {
  el.innerHTML = skeletonRows(6);
  const item = await api.get(`/item/artisan/${params.id}`);
  const m = item.metas;
  const enums = item.enums;
  const metierOptions = Object.fromEntries(store.metiers.map((t) => [t.id, t.name]));

  el.innerHTML = `
    ${pageHead({
      breadcrumb: '<a href="#/artisans">Artisans</a> › ' + esc(item.title || 'Nouveau'),
      title: '',
      actions: `
        ${item.view ? `<a class="ida-btn ida-btn--ghost" href="${esc(item.view)}" target="_blank" rel="noopener">${icon('external-link', 15)} Voir la fiche</a>` : ''}
        <button class="ida-btn ida-btn--secondary" data-save>Enregistrer</button>
        <button class="ida-btn ida-btn--primary" data-publish>${item.status === 'publish' ? 'Mettre à jour' : 'Publier'}</button>`,
    })}
    <div class="ida-editor">
      <div>
        <input class="ida-title-input" data-title value="${esc(item.title)}" placeholder="Nom de l'artisan…" aria-label="Nom">
        <div class="ida-card ida-card--pad" style="margin-bottom:14px;">
          <h2 class="ida-card__title">Présentation (visible sur la fiche publique)</h2>
          ${richTextHtml(item.content)}
        </div>
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Entreprise</h2>
          <div class="ida-form-grid">
            ${field('Raison sociale', input('m_company_name', m.company_name))}
            ${field('SIRET', input('m_siret', m.siret), m.siret_company ? `INSEE : ${m.siret_company}` : 'Vérifié automatiquement à l\'enregistrement.')}
            ${field('Téléphone', input('m_phone', m.phone))}
            ${field('Années d\'expérience', input('m_annees_experience', m.annees_experience, 'type="number"'))}
            ${field('Ville', input('m_ville', m.ville))}
            ${field('Code postal', input('m_code_postal', m.code_postal))}
            ${field('Rayon d\'intervention (km)', input('m_radius_km', m.radius_km, 'type="number"'))}
          </div>
          ${field('', switchInput('m_siret_verified', m.siret_verified === '1', 'SIRET vérifié manuellement'))}
        </div>
      </div>
      <div class="ida-editor__side">
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Statut</h2>
          ${field('Publication', select('status', { publish: 'Publié', draft: 'Suspendu (brouillon)', pending: 'En attente' }, item.status === 'auto-draft' ? 'draft' : item.status))}
          ${field('Vérification', select('m_verification_status', enums.verification, m.verification_status || 'pending'))}
          ${field('Badge', select('m_badge_level', enums.badge, m.badge_level || 'referenced'))}
          ${field('Abonnement', select('m_plan', enums.plan, m.plan || 'gratuit'))}
          ${m.rating_avg ? `<div class="ida-card__sub" style="margin-top:6px;">Note : <strong>${esc(m.rating_avg)}/5</strong> (${esc(m.rating_count || 0)} avis)</div>` : ''}
        </div>
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Photo</h2>
          ${imagePickerHtml('thumbnail', item.thumbnail_url)}
        </div>
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Métiers</h2>
          <div style="display:flex;flex-direction:column;gap:7px;max-height:220px;overflow-y:auto;">
            ${Object.entries(metierOptions).map(([id, name]) => `
              <label style="display:flex;gap:8px;align-items:center;font-size:13px;cursor:pointer;">
                <input type="checkbox" data-metier value="${id}" ${item.metier.includes(Number(id)) ? 'checked' : ''}> ${esc(name)}
              </label>`).join('')}
          </div>
        </div>
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Compte lié</h2>
          ${field('ID utilisateur WordPress', input('m_user_id', m.user_id, 'type="number"'), 'Compte de connexion de l\'artisan.')}
        </div>
      </div>
    </div>`;

  const rich = mountRichText(el);
  mountImagePickers(el);

  async function save(publish) {
    const form = readForm(el);
    const metas = {};
    Object.keys(form).forEach((k) => {
      if (k.startsWith('m_')) metas[k.slice(2)] = typeof form[k] === 'boolean' ? (form[k] ? '1' : '0') : form[k];
    });
    const thumbEl = el.querySelector('[name=thumbnail]');
    const metier = [...el.querySelectorAll('[data-metier]:checked')].map((c) => Number(c.value));
    await tryApi(api.post(`/item/artisan/${params.id}`, {
      title: el.querySelector('[data-title]').value,
      content: rich.get(),
      status: publish ? 'publish' : form.status,
      metas,
      metier,
      thumbnail_id: thumbEl.dataset.mediaId ? Number(thumbEl.dataset.mediaId) : (thumbEl.value ? item.thumbnail_id : 0),
    }), 'Enregistrement');
    toast(publish ? 'Fiche publiée ✓' : 'Fiche enregistrée ✓');
  }
  el.querySelector('[data-save]').addEventListener('click', () => save(false));
  el.querySelector('[data-publish]').addEventListener('click', () => save(true));
}

/* ── Avis (modération) ──────────────────────────────────────────────────── */

export async function viewAvis(el, params = {}) {
  el.innerHTML = pageHead({
    title: 'Avis clients',
    sub: 'Seuls les avis approuvés sont visibles sur le site et comptent dans les notes.',
  }) + '<div data-list></div>';

  const enums = store.enums;

  mountTable(el.querySelector('[data-list]'), {
    initialParams: { avis_status: params.statut || '' },
    fetch: (state) => api.get('/list/avis?' + new URLSearchParams({
      page: state.page, s: state.s || '', avis_status: state.avis_status || '',
    })),
    searchPlaceholder: 'Rechercher un avis…',
    filters: [{ name: 'avis_status', label: 'Statut', options: enums.avis_status || {} }],
    columns: [
      { label: 'Avis', render: (r) => `
          <div class="cell-main">${esc(r.title)}</div>
          <div class="cell-sub">${esc(r.excerpt)}</div>` },
      { label: 'Note', render: (r) => stars(r.rating) },
      { label: 'Artisan', render: (r) => r.artisan_id
          ? `<a href="#/artisans/${r.artisan_id}">${esc(r.artisan)}</a>` : '—', cls: 'hide-sm' },
      { label: 'Vérifié', render: (r) => r.verified ? badge('Vérifié', 'verified') : '—', cls: 'hide-sm' },
      { label: 'Statut', render: (r) => badge((enums.avis_status || {})[r.astatus] || r.astatus, r.astatus) },
      { label: 'Date', render: (r) => esc(r.date), cls: 'hide-sm' },
    ],
    rowActions: (row, reload) => {
      const setStatus = (value, label) => async () => {
        await tryApi(api.post(`/item/avis/${row.id}/action`, { action: 'avis_status', value }));
        toast(label);
        reload();
      };
      return [
        { label: 'Approuver (publier)', icon: 'check-circle', onClick: setStatus('approved', 'Avis approuvé — note recalculée') },
        { label: 'Refuser', icon: 'x-circle', onClick: setStatus('refused', 'Avis refusé') },
        { label: 'Masquer', icon: 'eye-off', onClick: setStatus('hidden', 'Avis masqué') },
        'hr',
        { label: 'Mettre à la corbeille', icon: 'trash', danger: true, onClick: async () => {
          if (!await confirmModal({ title: 'Supprimer cet avis ?', message: 'L\'avis sera mis à la corbeille et la note de l\'artisan recalculée.' })) return;
          await tryApi(api.post(`/item/avis/${row.id}/action`, { action: 'trash' }));
          toast('Avis supprimé');
          reload();
        }},
      ];
    },
    empty: { iconName: 'star', title: 'Aucun avis', text: 'Les avis déposés par les clients apparaîtront ici.' },
  });
}
