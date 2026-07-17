/**
 * Centre de gestion des emails — cartes (test, délivrabilité, journaux,
 * erreurs, modèles, SMTP) + écrans journaux / erreurs / modèles.
 * Routes : #/parametres/emails · #/parametres/emails/journaux ·
 *          #/parametres/emails/erreurs · #/parametres/emails/modeles
 */

import { api, esc, tryApi, debounce } from '../api.js';
import { icon } from '../icons.js';
import { openDrawer, toast, skeletonRows, pageHead, field, input, textarea, select, switchInput, emptyState } from '../ui.js';

const STATUS_BADGE = {
  sent:    ['Envoyé', 'success'],
  failed:  ['Échec', 'danger'],
  pending: ['En attente', 'muted'],
};

function statusBadge(status) {
  const [label, kind] = STATUS_BADGE[status] || [status, 'muted'];
  const styles = {
    success: '',
    danger: '',
    muted: 'background:#f1f5f9;color:#475569;',
  };
  const cls = kind === 'muted' ? 'ida-badge' : `ida-badge ida-badge--${kind}`;
  return `<span class="${cls}" style="${styles[kind] || ''}">${esc(label)}</span>`;
}

function stateBadge(state) {
  const map = {
    ok:            ['Opérationnel', 'success', ''],
    erreur:        ['Erreur', 'danger', ''],
    absent:        ['Non configuré', 'danger', ''],
    inconnu:       ['Attention', '', 'background:#fef3c7;color:#92400e;'],
    non_configure: ['Non configuré', '', 'background:#f1f5f9;color:#475569;'],
    attente:       ['En attente', '', 'background:#f1f5f9;color:#475569;'],
  };
  const [label, kind, style] = map[state] || map.attente;
  return `<span class="ida-badge ${kind ? 'ida-badge--' + kind : ''}" style="${style}">${esc(label)}</span>`;
}

const fmtDate = (d) => (d ? esc(String(d).replace(/:\d\d$/, '')) : '—');

/* ── Vue principale ─────────────────────────────────────────────────────── */

export async function viewEmails(el, params = {}) {
  const tab = params.tab || '';
  if (tab === 'journaux') return screenLogs(el, false);
  if (tab === 'erreurs') return screenLogs(el, true);
  if (tab === 'modeles') return screenTemplates(el);
  return screenOverview(el);
}

/* ── Écran 1 : cartes ───────────────────────────────────────────────────── */

async function screenOverview(el) {
  el.innerHTML = skeletonRows(7);
  const d = await tryApi(api.get('/emails/overview'), 'Chargement impossible');
  const s = d.stats;
  const deliv = d.deliverability;

  const card = (iconName, title, badgeHtml, bodyHtml, footHtml) => `
    <div class="ida-card ida-card--pad" style="display:flex;flex-direction:column;gap:10px;">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
        <h2 class="ida-card__title" style="display:flex;align-items:center;gap:8px;margin:0;">${icon(iconName, 17)} ${esc(title)}</h2>
        ${badgeHtml}
      </div>
      <div style="flex:1;font-size:13px;color:#475569;line-height:1.55;">${bodyHtml}</div>
      <div>${footHtml}</div>
    </div>`;

  el.innerHTML = `
    ${pageHead({ title: 'Emails', sub: 'Centre de gestion : tests, délivrabilité, journaux, modèles et configuration SMTP.' })}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;">

      ${card('send', 'Test d\'email',
        stateBadge(d.connection.state === 'ok' ? 'ok' : d.connection.state),
        `Envoyez un email de test pour vérifier que le site expédie correctement.
         <div style="margin-top:8px;">
           <div><strong>Expéditeur :</strong> ${esc(d.smtp.from_name)} &lt;${esc(d.smtp.from_email)}&gt;</div>
           <div><strong>Dernier test :</strong> ${d.last_test ? `${fmtDate(d.last_test.date)} — ${d.last_test.sent ? '✅ réussi en ' + d.last_test.duration_ms + ' ms' : '❌ échec'}` : 'jamais'}</div>
         </div>`,
        `<button class="ida-btn" data-test-email>${icon('send', 15)} Tester un email</button>`)}

      ${card('shield-check', 'Analyse de délivrabilité',
        deliv ? stateBadge(deliv.score >= 70 ? 'ok' : (deliv.score >= 40 ? 'inconnu' : 'erreur')) : stateBadge('attente'),
        deliv ? `
          <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;">
            <span>SPF ${stateBadge(deliv.spf.state)}</span>
            <span>DKIM ${stateBadge(deliv.dkim.state)}</span>
            <span>DMARC ${stateBadge(deliv.dmarc.state)}</span>
          </div>
          <div><strong>Score : ${deliv.score}/100</strong> · domaine ${esc(deliv.domain)} · analysé le ${fmtDate(deliv.checked_at)}</div>
          ${deliv.recommendations.length ? `<ul style="margin:8px 0 0 16px;">${deliv.recommendations.slice(0, 2).map((r) => `<li>${esc(r)}</li>`).join('')}</ul>` : ''}`
        : 'Vérifie les enregistrements DNS réels (SPF, DKIM, DMARC, MX) du domaine d\'expédition et calcule un score de délivrabilité.',
        `<button class="ida-btn ida-btn--secondary" data-deliverability>${icon('shield-check', 15)} Analyser la configuration</button>`)}

      ${card('list', 'Journaux des emails',
        s.success_rate === null ? stateBadge('attente') : stateBadge(s.success_rate >= 90 ? 'ok' : 'inconnu'),
        `<div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 12px;">
           <div><strong>${s.total}</strong> au total</div>
           <div><strong>${s.sent}</strong> envoyés</div>
           <div><strong>${s.failed}</strong> échoués</div>
           <div><strong>${s.pending}</strong> en attente</div>
         </div>
         <div style="margin-top:8px;"><strong>Taux de réussite :</strong> ${s.success_rate === null ? '—' : s.success_rate + ' %'}</div>
         <div style="margin-top:4px;color:#94a3b8;">Rétention : ${d.retention_days} jours <a href="#" data-retention>modifier</a></div>`,
        `<a class="ida-btn ida-btn--secondary" href="#/parametres/emails/journaux">${icon('list', 15)} Voir les journaux</a>`)}

      ${card('alert-triangle', 'Erreurs d\'envoi',
        stateBadge(s.recent_errors === 0 ? 'ok' : 'erreur'),
        s.last_error ? `
          <div><strong>${s.recent_errors}</strong> erreur(s) non résolue(s) sur 7 jours.</div>
          <div style="margin-top:8px;padding:8px 10px;background:#fef2f2;border-radius:8px;color:#991b1b;">
            <div style="font-weight:600;">${esc(s.last_error.subject || '(sans sujet)')}</div>
            <div>${esc((s.last_error.error_message || '').slice(0, 120))}</div>
            <div style="color:#b91c1c;opacity:.8;">${esc(s.last_error.provider)} · ${fmtDate(s.last_error.created_at)}</div>
          </div>`
        : 'Aucune erreur enregistrée. Les échecs d\'envoi apparaîtront ici avec leur message SMTP exact.',
        `<a class="ida-btn ida-btn--secondary" href="#/parametres/emails/erreurs">${icon('alert-triangle', 15)} Voir les erreurs</a>`)}

      ${card('mail', 'Modèles d\'emails',
        stateBadge('ok'),
        `<div><strong>${d.templates}</strong> modèles administrables : devis, comptes, rendez-vous, avis, paiements, validation artisan…</div>
         <div style="margin-top:8px;color:#94a3b8;">Sujet, contenu, activation, variables documentées, aperçu et envoi de test pour chaque modèle.</div>`,
        `<a class="ida-btn ida-btn--secondary" href="#/parametres/emails/modeles">${icon('mail', 15)} Gérer les modèles</a>`)}

      ${card('settings', 'Configuration SMTP',
        stateBadge(d.connection.state),
        `<div style="display:grid;grid-template-columns:auto 1fr;gap:3px 10px;">
           <span>Méthode</span><strong>${esc(d.smtp.method)}</strong>
           <span>Serveur</span><strong>${esc(d.smtp.host || '—')}${d.smtp.port ? ':' + d.smtp.port : ''}</strong>
           <span>Chiffrement</span><strong>${esc(d.smtp.encryption || '—')}</strong>
           <span>Expéditeur</span><strong>${esc(d.smtp.from_email)}</strong>
           <span>Connexion</span><span>${esc(d.connection.detail)}</span>
         </div>
         ${d.smtp.plugin ? `<div style="margin-top:8px;color:#94a3b8;">Transport géré par ${esc(d.smtp.plugin)} — aucun mot de passe stocké en double.</div>` : ''}`,
        `<button class="ida-btn ida-btn--secondary" data-test-conn>${icon('activity', 15)} Tester la connexion</button>
         <button class="ida-btn ida-btn--secondary" data-test-email>${icon('send', 15)} Email de test</button>
         <a class="ida-btn ida-btn--secondary" href="${esc(window.IDA.adminUrl || '/wp-admin/')}admin.php?page=wp-mail-smtp&classic=1" target="_blank" rel="noopener">${icon('external-link', 15)} Réglages SMTP</a>`)}
    </div>`;

  el.querySelectorAll('[data-test-email]').forEach((b) => b.addEventListener('click', openTestDrawer));
  el.querySelector('[data-deliverability]')?.addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    btn.disabled = true;
    btn.innerHTML = 'Analyse DNS en cours…';
    try {
      await tryApi(api.post('/emails/deliverability'), 'Analyse impossible');
      toast('Analyse terminée');
      screenOverview(el);
    } catch (err) { btn.disabled = false; btn.innerHTML = 'Analyser la configuration'; }
  });
  el.querySelector('[data-test-conn]')?.addEventListener('click', async () => {
    const d2 = await tryApi(api.get('/emails/overview'), 'Test impossible');
    toast(d2.connection.state === 'ok' ? d2.connection.detail : d2.connection.detail, d2.connection.state === 'ok' ? 'success' : 'error');
  });
  el.querySelector('[data-retention]')?.addEventListener('click', async (e) => {
    e.preventDefault();
    const days = prompt('Durée de conservation des journaux (jours, 7 à 730) :', d.retention_days);
    if (!days) return;
    await tryApi(api.post('/emails/settings', { retention_days: parseInt(days, 10) }), 'Enregistrement impossible');
    toast('Rétention mise à jour');
    screenOverview(el);
  });
}

/* ── Tiroir : test d'email ──────────────────────────────────────────────── */

function openTestDrawer() {
  const { root, close } = openDrawer({
    title: 'Tester un email',
    body: `
      ${field('Destinataire *', input('to', window.IDA.user.email || '', 'type="email" required'))}
      ${field('Sujet', input('subject', '[Test] InfoDevis — email de test'))}
      ${field('Message', textarea('message', 'Ceci est un email de test envoyé depuis InfoDevis Admin.', 5))}
      ${field('Format', select('html', [{ value: '', label: 'Texte brut' }, { value: '1', label: 'HTML' }]))}
      <div data-result style="margin-top:12px;"></div>`,
    footer: `<button class="ida-btn" data-send>${icon('send', 15)} Envoyer</button>`,
  });

  root.querySelector('[data-send]').addEventListener('click', async () => {
    const btn = root.querySelector('[data-send]');
    const out = root.querySelector('[data-result]');
    const payload = {
      to: root.querySelector('[name=to]').value.trim(),
      subject: root.querySelector('[name=subject]').value,
      message: root.querySelector('[name=message]').value,
      html: root.querySelector('[name=html]').value === '1',
    };
    if (!payload.to) { toast('Indiquez un destinataire', 'error'); return; }
    btn.disabled = true;
    btn.innerHTML = 'Envoi…';
    try {
      const r = await api.post('/emails/test', payload);
      out.innerHTML = r.sent
        ? `<div class="ida-badge ida-badge--success">${icon('check', 12)} Envoyé en ${r.duration_ms} ms</div>
           <p style="font-size:12px;color:#64748b;margin-top:8px;">Transport : ${esc(r.provider)} · destinataire : ${esc(r.to)}</p>`
        : `<div class="ida-badge ida-badge--danger">${icon('x', 12)} Échec en ${r.duration_ms} ms</div>
           <pre style="font-size:12px;background:#fef2f2;color:#991b1b;padding:10px;border-radius:8px;white-space:pre-wrap;margin-top:8px;">${esc(r.error ? r.error.message : 'Erreur inconnue')}</pre>`;
    } catch (e) {
      out.innerHTML = `<div class="ida-badge ida-badge--danger">${esc(e.message)}</div>`;
    }
    btn.disabled = false;
    btn.innerHTML = 'Envoyer';
  });
}

/* ── Écran 2/3 : journaux (et erreurs) ─────────────────────────────────── */

async function screenLogs(el, onlyErrors) {
  const state = { page: 1, status: onlyErrors ? 'failed' : '', type: '', recipient: '', search: '', date_from: '', date_to: '' };

  const load = async () => {
    const q = new URLSearchParams({
      page: state.page, per_page: 20, status: state.status, type: state.type,
      recipient: state.recipient, search: state.search, date_from: state.date_from, date_to: state.date_to,
    });
    return api.get('/emails/logs?' + q.toString());
  };

  const render = (d) => {
    const rows = d.rows.map((r) => `
      <tr>
        <td style="white-space:nowrap;">${fmtDate(r.created_at)}</td>
        <td>${esc(r.recipient_email)}</td>
        <td>${esc(r.subject || '(sans sujet)')}
          ${onlyErrors && r.error_message ? `<div style="font-size:11px;color:#991b1b;">${esc(r.error_message.slice(0, 110))}${r.error_code ? ' · ' + esc(r.error_code) : ''}</div>` : ''}
        </td>
        <td><code style="font-size:11px;">${esc(r.message_type)}</code></td>
        <td>${statusBadge(r.status)}${r.resolved_at ? ' <span class="ida-badge" style="background:#f0fdf4;color:#166534;">résolu</span>' : ''}</td>
        <td style="text-align:center;">${r.attempts}</td>
        <td style="white-space:nowrap;text-align:right;">
          <button class="ida-btn ida-btn--secondary" data-view="${r.id}" title="Aperçu">${icon('eye', 14)}</button>
          ${r.status === 'failed' && r.has_body ? `<button class="ida-btn ida-btn--secondary" data-resend="${r.id}" title="Renvoyer">${icon('refresh-cw', 14)}</button>` : ''}
          ${r.status === 'failed' && !r.resolved_at ? `<button class="ida-btn ida-btn--secondary" data-resolve="${r.id}" title="Marquer résolu">${icon('check', 14)}</button>` : ''}
        </td>
      </tr>`).join('');

    el.innerHTML = `
      ${pageHead({
        title: onlyErrors ? 'Erreurs d\'envoi' : 'Journaux des emails',
        count: d.total,
        breadcrumb: `<a href="#/parametres/emails">Emails</a>`,
        actions: `<a class="ida-btn ida-btn--secondary" href="#/parametres/emails">${icon('arrow-left', 15)} Retour aux cartes</a>`,
      })}
      <div class="ida-card ida-card--pad" style="margin-bottom:14px;">
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
          ${onlyErrors ? '' : field('Statut', select('f_status', [
            { value: '', label: 'Tous' }, { value: 'sent', label: 'Envoyés' },
            { value: 'failed', label: 'Échoués' }, { value: 'pending', label: 'En attente' },
          ], state.status))}
          ${field('Type', select('f_type', [{ value: '', label: 'Tous' }].concat((d.types || []).map((t) => ({ value: t, label: t }))), state.type))}
          ${field('Destinataire', input('f_recipient', state.recipient, 'placeholder="email@…"'))}
          ${field('Sujet', input('f_search', state.search, 'placeholder="Rechercher…"'))}
          ${field('Du', input('f_from', state.date_from, 'type="date"'))}
          ${field('Au', input('f_to', state.date_to, 'type="date"'))}
        </div>
      </div>
      <div class="ida-card">
        <div style="overflow-x:auto;">
          <table style="width:100%;border-collapse:collapse;font-size:13px;" class="ida-table">
            <thead><tr style="text-align:left;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.04em;">
              <th style="padding:10px 12px;">Date</th><th>Destinataire</th><th>Sujet</th><th>Type</th><th>Statut</th><th>Essais</th><th style="text-align:right;padding-right:12px;">Actions</th>
            </tr></thead>
            <tbody>${rows || `<tr><td colspan="7">${emptyState({ title: onlyErrors ? 'Aucune erreur 🎉' : 'Aucun email journalisé', text: 'Les envois apparaîtront ici automatiquement.' })}</td></tr>`}</tbody>
          </table>
        </div>
        ${d.pages > 1 ? `
          <div style="display:flex;justify-content:center;gap:8px;padding:12px;">
            <button class="ida-btn ida-btn--secondary" data-prev ${state.page <= 1 ? 'disabled' : ''}>←</button>
            <span style="align-self:center;font-size:13px;">Page ${d.page} / ${d.pages}</span>
            <button class="ida-btn ida-btn--secondary" data-next ${state.page >= d.pages ? 'disabled' : ''}>→</button>
          </div>` : ''}
      </div>
      <style>.ida-table td{padding:9px 12px;border-top:1px solid #f1f5f9;vertical-align:top;}</style>`;

    const reload = debounce(async () => { state.page = 1; render(await load()); }, 350);
    const bind = (name, key, immediate) => {
      const inp = el.querySelector(`[name=${name}]`);
      if (!inp) return;
      inp.addEventListener(immediate ? 'change' : 'input', () => { state[key] = inp.value; immediate ? load().then(render) : reload(); });
    };
    bind('f_status', 'status', true);
    bind('f_type', 'type', true);
    bind('f_recipient', 'recipient', false);
    bind('f_search', 'search', false);
    bind('f_from', 'date_from', true);
    bind('f_to', 'date_to', true);
    el.querySelector('[data-prev]')?.addEventListener('click', async () => { state.page--; render(await load()); });
    el.querySelector('[data-next]')?.addEventListener('click', async () => { state.page++; render(await load()); });

    el.querySelectorAll('[data-view]').forEach((b) => b.addEventListener('click', () => {
      const r = d.rows.find((x) => String(x.id) === b.dataset.view);
      openDrawer({
        title: 'Aperçu — ' + (r.subject || '(sans sujet)'),
        body: `
          <div style="font-size:13px;line-height:1.7;">
            <div><strong>Destinataire :</strong> ${esc(r.recipient_email)}</div>
            <div><strong>Type :</strong> ${esc(r.message_type)} · <strong>Transport :</strong> ${esc(r.provider)}</div>
            <div><strong>Créé :</strong> ${fmtDate(r.created_at)} · <strong>Envoyé :</strong> ${fmtDate(r.sent_at)}</div>
            <div><strong>Statut :</strong> ${statusBadge(r.status)} · <strong>Tentatives :</strong> ${r.attempts}</div>
            ${r.error_message ? `<pre style="background:#fef2f2;color:#991b1b;padding:10px;border-radius:8px;white-space:pre-wrap;font-size:12px;">${esc(r.error_code ? r.error_code + ' — ' : '')}${esc(r.error_message)}</pre>` : ''}
            <div style="margin-top:10px;"><strong>Extrait :</strong></div>
            <div style="background:#f8fafc;padding:10px;border-radius:8px;white-space:pre-wrap;">${esc(r.failed_body || r.excerpt || '(contenu non conservé)')}</div>
          </div>`,
      });
    }));
    el.querySelectorAll('[data-resend]').forEach((b) => b.addEventListener('click', async () => {
      b.disabled = true;
      try {
        const r = await api.post(`/emails/logs/${b.dataset.resend}/resend`);
        toast(r.sent ? 'Email renvoyé ✅' : 'Nouvel échec : ' + (r.error || 'erreur inconnue'), r.sent ? 'success' : 'error');
        render(await load());
      } catch (e) { toast(e.message, 'error'); b.disabled = false; }
    }));
    el.querySelectorAll('[data-resolve]').forEach((b) => b.addEventListener('click', async () => {
      await tryApi(api.post(`/emails/logs/${b.dataset.resolve}/resolve`), 'Action impossible');
      toast('Marqué comme résolu');
      render(await load());
    }));
  };

  el.innerHTML = skeletonRows(6);
  render(await load());
}

/* ── Écran 4 : modèles ─────────────────────────────────────────────────── */

async function screenTemplates(el) {
  el.innerHTML = skeletonRows(6);
  const d = await tryApi(api.get('/emails'), 'Chargement impossible');

  el.innerHTML = `
    ${pageHead({
      title: 'Modèles d\'emails',
      count: d.templates.length,
      breadcrumb: `<a href="#/parametres/emails">Emails</a>`,
      actions: `<a class="ida-btn ida-btn--secondary" href="#/parametres/emails">${icon('arrow-left', 15)} Retour aux cartes</a>`,
    })}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:12px;">
      ${d.templates.map((t) => `
        <div class="ida-card ida-card--pad" style="display:flex;flex-direction:column;gap:8px;">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
            <strong style="font-size:14px;">${esc(t.label)}</strong>
            ${t.enabled ? '<span class="ida-badge ida-badge--success">Actif</span>' : '<span class="ida-badge" style="background:#f1f5f9;color:#475569;">Désactivé</span>'}
          </div>
          <code style="font-size:11px;color:#94a3b8;">${esc(t.key)}</code>
          <div style="font-size:12px;color:#475569;flex:1;">${esc(t.subject)}</div>
          <div><button class="ida-btn ida-btn--secondary" data-edit="${esc(t.key)}">${icon('pencil', 14)} Modifier</button></div>
        </div>`).join('')}
    </div>
    <p style="font-size:12px;color:#94a3b8;margin-top:14px;">
      Variables acceptées dans tous les modèles (les deux syntaxes fonctionnent) : {site} / {{site_name}}, {nom} / {{user_name}} ou {{client_name}},
      {email} / {{user_email}}, {entreprise} / {{artisan_name}}, {reference} / {{quote_reference}}, {date} / {{appointment_date}},
      {montant} / {{payment_amount}}, {plan} / {{subscription_name}}, {lien_connexion} / {{login_url}}, {lien_reset} / {{reset_url}}, {lien_espace}, {lien_admin}.
    </p>`;

  el.querySelectorAll('[data-edit]').forEach((b) => b.addEventListener('click', () => {
    const t = d.templates.find((x) => x.key === b.dataset.edit);
    const { root, close } = openDrawer({
      title: t.label,
      width: 560,
      body: `
        ${field('Identifiant technique', `<code>${esc(t.key)}</code>`)}
        ${field('Sujet', input('subject', t.subject))}
        ${field('Contenu (texte — les sauts de ligne sont conservés)', textarea('body', t.body, 10))}
        ${field('', switchInput('enabled', t.enabled, 'Modèle actif'))}
        ${field('Variables disponibles pour ce modèle', `<code style="font-size:12px;">${esc(t.vars)}</code>`, 'Syntaxe {{double_accolade}} également acceptée — voir la liste en bas de page.')}
        <div style="border-top:1px solid #f1f5f9;margin:14px 0;padding-top:14px;">
          <strong style="font-size:13px;">Aperçu</strong>
          <div data-preview style="background:#f8fafc;border-radius:8px;padding:12px;font-size:13px;white-space:pre-wrap;margin-top:6px;"></div>
        </div>
        ${field('Envoi de test à', input('test_to', window.IDA.user.email || '', 'type="email"'))}
      `,
      footer: `
        <button class="ida-btn ida-btn--secondary" data-testsend>${icon('send', 14)} Envoyer un test</button>
        <button class="ida-btn" data-save>${icon('check', 14)} Enregistrer</button>`,
    });

    const samples = {
      '{site}': 'InfoDevis', '{nom}': 'Jean Exemple', '{email}': 'jean@example.com',
      '{entreprise}': 'Entreprise Exemple', '{reference}': 'DV000000-TEST', '{metier}': 'Plomberie',
      '{ville}': 'Paris', '{date}': '2026-07-20 10:00', '{note}': '5', '{plan}': 'Gold',
      '{montant}': '14,00 €', '{lien_espace}': '/espace-membre/', '{lien_admin}': '/infodevis-admin/',
      '{lien_connexion}': '/connexion/', '{lien_reset}': '/connexion/',
    };
    const renderPreview = () => {
      let txt = 'Sujet : ' + root.querySelector('[name=subject]').value + '\n\n' + root.querySelector('[name=body]').value;
      Object.entries(samples).forEach(([k, v]) => { txt = txt.split(k).join(v); });
      root.querySelector('[data-preview]').textContent = txt;
    };
    renderPreview();
    root.querySelector('[name=subject]').addEventListener('input', renderPreview);
    root.querySelector('[name=body]').addEventListener('input', renderPreview);

    root.querySelector('[data-save]').addEventListener('click', async () => {
      t.subject = root.querySelector('[name=subject]').value;
      t.body = root.querySelector('[name=body]').value;
      t.enabled = root.querySelector('[name=enabled]').checked;
      await tryApi(api.post('/emails', { templates: d.templates }), 'Enregistrement impossible');
      toast('Modèle enregistré');
      close();
      screenTemplates(el);
    });
    root.querySelector('[data-testsend]').addEventListener('click', async () => {
      const to = root.querySelector('[name=test_to]').value.trim();
      if (!to) { toast('Indiquez une adresse de test', 'error'); return; }
      // Enregistre d'abord la version en cours pour tester ce qui est affiché.
      t.subject = root.querySelector('[name=subject]').value;
      t.body = root.querySelector('[name=body]').value;
      t.enabled = root.querySelector('[name=enabled]').checked;
      await tryApi(api.post('/emails', { templates: d.templates }), 'Enregistrement impossible');
      const r = await tryApi(api.post('/emails/template-test', { key: t.key, to }), 'Envoi impossible');
      toast(r.sent ? 'Test envoyé à ' + to : 'Échec de l\'envoi (voir les journaux)', r.sent ? 'success' : 'error');
    });
  }));
}
