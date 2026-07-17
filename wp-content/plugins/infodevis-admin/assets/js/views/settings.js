/**
 * Paramètres : société, coordonnées, réseaux, intégrations, santé,
 * emails (9 modèles + journal), sauvegardes.
 */

import { api, tryApi, esc } from '../api.js';
import { icon } from '../icons.js';
import {
  pageHead, mountTabs, badge, toast, skeletonRows,
  field, input, textarea, switchInput, imagePickerHtml, mountImagePickers, readForm,
} from '../ui.js';

export async function viewSettings(el, params = {}) {
  const tab = params.tab || 'reglages';

  el.innerHTML = pageHead({ title: 'Paramètres' }) + '<div data-tabs></div><div data-body></div>';

  mountTabs(el.querySelector('[data-tabs]'), [
    { key: 'reglages', label: 'Société & coordonnées' },
    { key: 'api', label: 'Intégrations & API' },
    { key: 'emails', label: 'Emails' },
    { key: 'sante', label: 'Santé système' },
    { key: 'sauvegardes', label: 'Sauvegardes' },
  ], tab, (key) => { location.hash = `/parametres/${key}`; });

  const body = el.querySelector('[data-body]');
  if (tab === 'emails') return renderEmails(body);
  if (tab === 'sauvegardes') return renderBackups(body);
  if (tab === 'api') return renderIntegrations(body);
  if (tab === 'sante') return renderHealth(body);
  return renderGeneral(body);
}

/* ── Société & coordonnées ─────────────────────────────────────────────── */

async function renderGeneral(el) {
  el.innerHTML = skeletonRows(5);
  const d = await api.get('/settings');
  const s = d.settings;
  const c = d.contact;

  el.innerHTML = `
    <div class="ida-dash-grid" style="grid-template-columns:1fr 1fr;">
      <div class="ida-card ida-card--pad">
        <h2 class="ida-card__title">Société</h2>
        ${field('Nom de la société', input('company_name', s.company_name))}
        ${field('Logo', imagePickerHtml('logo', s.logo))}
        <div class="ida-form-grid">
          ${field('Couleur principale', `<input class="ida-input" type="color" name="color_primary" value="${esc(s.color_primary)}" style="height:38px;padding:4px;">`)}
          ${field('Couleur bleu nuit', `<input class="ida-input" type="color" name="color_navy" value="${esc(s.color_navy)}" style="height:38px;padding:4px;">`)}
        </div>
        ${field('Texte du pied de page', textarea('footer_text', s.footer_text, 2))}
      </div>
      <div class="ida-dash-col">
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Coordonnées (affichées sur le site)</h2>
          ${field('Email', input('contact_email', c.email, 'type="email"'))}
          ${field('Téléphone', input('contact_phone', c.phone))}
          ${field('Adresse', input('contact_address', c.address))}
        </div>
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Réseaux sociaux & Google</h2>
          ${field('Fiche Google Business', input('google_business', s.google_business, 'placeholder="https://…"'))}
          <div class="ida-form-grid">
            ${field('Facebook', input('facebook', s.facebook))}
            ${field('Instagram', input('instagram', s.instagram))}
            ${field('LinkedIn', input('linkedin', s.linkedin))}
            ${field('X (Twitter)', input('twitter', s.twitter))}
          </div>
        </div>
      </div>
    </div>
    <div style="margin-top:16px;display:flex;justify-content:flex-end;">
      <button class="ida-btn ida-btn--primary" data-save>${icon('save', 15)} Enregistrer les réglages</button>
    </div>`;

  mountImagePickers(el);
  el.querySelector('[data-save]').addEventListener('click', async () => {
    const form = readForm(el);
    await tryApi(api.post('/settings', {
      settings: {
        company_name: form.company_name, logo: form.logo,
        color_primary: form.color_primary, color_navy: form.color_navy,
        footer_text: form.footer_text, google_business: form.google_business,
        facebook: form.facebook, instagram: form.instagram,
        linkedin: form.linkedin, twitter: form.twitter,
      },
      contact: { email: form.contact_email, phone: form.contact_phone, address: form.contact_address },
    }), 'Enregistrement');
    toast('Réglages enregistrés ✓');
  });
}

/* ── Intégrations ──────────────────────────────────────────────────────── */

async function renderIntegrations(el) {
  el.innerHTML = skeletonRows(4);
  const d = await api.get('/settings');

  el.innerHTML = `
    <div class="ida-cards-grid" style="grid-template-columns:repeat(2,1fr);">
      ${d.integrations.map((i) => `
        <div class="ida-card ida-card--pad" style="display:flex;gap:14px;align-items:flex-start;">
          <span class="ida-site-card__icon" style="background:${i.ok ? 'var(--ida-success-soft)' : 'var(--ida-danger-soft)'};color:${i.ok ? 'var(--ida-success)' : 'var(--ida-danger)'};margin:0;">
            ${icon(i.ok ? 'check-circle' : 'x-circle', 18)}
          </span>
          <div>
            <div class="ida-card__title">${esc(i.label)}</div>
            <div class="ida-card__sub">${esc(i.detail)}</div>
          </div>
        </div>`).join('')}
    </div>
    <div class="ida-card ida-card--pad" style="margin-top:16px;border-left:3px solid var(--ida-info);">
      <strong>Sécurité</strong>
      <p class="ida-card__sub" style="margin:4px 0 0;">
        Les clés secrètes (Stripe, SMTP) sont définies dans <code>wp-config.php</code> et ne
        transitent jamais par cette interface ni par la base de données.
      </p>
    </div>`;
}

/* ── Santé système ─────────────────────────────────────────────────────── */

async function renderHealth(el) {
  el.innerHTML = skeletonRows(5);
  const d = await api.get('/settings');

  el.innerHTML = `
    <div class="ida-table-wrap"><div class="ida-table-scroll">
      <table class="ida-table">
        <thead><tr><th>Élément</th><th>État</th><th style="width:60px;"></th></tr></thead>
        <tbody>
          ${d.health.map((h) => `
            <tr style="cursor:default;">
              <td class="cell-main">${esc(h.label)}</td>
              <td style="color:var(--ida-text-soft);">${esc(h.value)}</td>
              <td>${h.ok ? `<span style="color:var(--ida-success);">${icon('check-circle', 17)}</span>` : `<span style="color:var(--ida-danger);">${icon('x-circle', 17)}</span>`}</td>
            </tr>`).join('')}
        </tbody>
      </table>
    </div></div>`;
}

/* ── Emails ────────────────────────────────────────────────────────────── */

async function renderEmails(el) {
  el.innerHTML = skeletonRows(6);
  const d = await api.get('/emails');

  el.innerHTML = `
    <p class="ida-card__sub" style="margin:0 0 16px;">
      9 emails automatiques. Les variables entre accolades sont remplacées à l'envoi.
    </p>
    <div data-templates style="display:flex;flex-direction:column;gap:12px;">
      ${d.templates.map((t, i) => `
        <div class="ida-card ida-card--pad" data-key="${esc(t.key)}" style="padding:18px;">
          <div class="ida-field--row" style="margin-bottom:10px;">
            <strong style="font-size:14px;">${esc(t.label)}</strong>
            ${switchInput(`enabled_${i}`, t.enabled, '')}
          </div>
          ${field('Sujet', input(`subject_${i}`, t.subject))}
          ${field('Message', textarea(`body_${i}`, t.body, 5))}
          <div class="hint" style="font-size:11.5px;color:var(--ida-text-faint);">Variables : <code>${esc(t.vars)}</code></div>
        </div>`).join('')}
    </div>
    <div style="margin:16px 0;display:flex;justify-content:flex-end;">
      <button class="ida-btn ida-btn--primary" data-save>${icon('save', 15)} Enregistrer les modèles</button>
    </div>
    ${d.log.length ? `
      <h2 style="font-size:15px;">Derniers envois</h2>
      <div class="ida-table-wrap"><div class="ida-table-scroll">
        <table class="ida-table">
          <thead><tr><th>Date</th><th>Modèle</th><th>Destinataire</th><th>Statut</th></tr></thead>
          <tbody>
            ${d.log.map((entry) => `
              <tr style="cursor:default;">
                <td>${esc(entry.date || '')}</td>
                <td>${esc(entry.template || '')}</td>
                <td>${esc(entry.to || '')}</td>
                <td>${(entry.status || '') === 'envoye' ? badge('Envoyé', 'publish') : badge('Échec', 'refused')}</td>
              </tr>`).join('')}
          </tbody>
        </table>
      </div></div>` : ''}`;

  el.querySelector('[data-save]').addEventListener('click', async () => {
    const form = readForm(el);
    const templates = d.templates.map((t, i) => ({
      key: t.key,
      subject: form[`subject_${i}`],
      body: form[`body_${i}`],
      enabled: form[`enabled_${i}`],
    }));
    await tryApi(api.post('/emails', { templates }), 'Enregistrement');
    toast('Modèles d\'emails enregistrés ✓');
  });
}

/* ── Sauvegardes ───────────────────────────────────────────────────────── */

async function renderBackups(el) {
  el.innerHTML = skeletonRows(4);
  const d = await api.get('/backups');

  el.innerHTML = `
    <div class="ida-page-head">
      <div>
        <h1 style="font-size:16px;">Sauvegardes</h1>
        <p class="ida-page-head__sub">Dossier : <code>${esc(d.dir)}</code></p>
      </div>
      <button class="ida-btn ida-btn--primary" data-backup>${icon('database', 15)} Sauvegarder la base maintenant</button>
    </div>
    ${d.backups.length ? `
      <div class="ida-table-wrap"><div class="ida-table-scroll">
        <table class="ida-table">
          <thead><tr><th>Nom</th><th>Date</th><th>Taille</th></tr></thead>
          <tbody>
            ${d.backups.map((b) => `
              <tr style="cursor:default;">
                <td class="cell-main">${esc(b.name)}</td>
                <td>${esc(b.date)}</td>
                <td>${esc(b.size)}</td>
              </tr>`).join('')}
          </tbody>
        </table>
      </div></div>`
    : '<div class="ida-card ida-card--pad">Aucune sauvegarde pour le moment.</div>'}
    <p class="ida-card__sub" style="margin-top:10px;">
      La sauvegarde intégrée exporte la base de données en SQL. Pour une sauvegarde
      complète des fichiers, voir <code>backups/…/RESTAURATION.md</code>.
    </p>`;

  el.querySelector('[data-backup]').addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    btn.disabled = true;
    btn.innerHTML = `${icon('refresh', 15)} Sauvegarde en cours…`;
    try {
      const res = await api.post('/backups');
      toast(`Base sauvegardée (${res.size})`);
      renderBackups(el);
    } catch (err) {
      toast(err.message, 'error');
      btn.disabled = false;
      btn.innerHTML = `${icon('database', 15)} Sauvegarder la base maintenant`;
    }
  });
}
