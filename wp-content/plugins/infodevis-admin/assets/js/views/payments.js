/**
 * Paiements : abonnements des artisans, état Stripe.
 */

import { api, esc } from '../api.js';
import { icon } from '../icons.js';
import { pageHead, badge, skeletonRows, emptyState } from '../ui.js';
import { store, navigate } from '../app.js';

export async function viewPayments(el) {
  el.innerHTML = pageHead({ title: 'Paiements & abonnements' }) + skeletonRows(5);
  const d = await api.get('/payments');
  const plans = store.enums.plan || {};

  el.querySelectorAll('.ida-skeleton').forEach((s) => s.remove());
  el.insertAdjacentHTML('beforeend', `
    <div class="ida-stats" style="grid-template-columns:repeat(4,1fr);">
      <div class="ida-stat">
        <div class="ida-stat__icon">${icon('credit-card', 17)}</div>
        <div class="ida-stat__value">${d.paying}</div>
        <div class="ida-stat__label">Abonnements payants</div>
        <div class="ida-stat__sub">sur ${d.total} artisans</div>
      </div>
      <div class="ida-stat">
        <div class="ida-stat__icon">${icon(d.stripe_on ? 'check-circle' : 'x-circle', 17)}</div>
        <div class="ida-stat__value" style="font-size:18px;">${d.stripe_on ? 'Actif' : 'Inactif'}</div>
        <div class="ida-stat__label">Stripe</div>
        <div class="ida-stat__sub">mode ${esc(d.mode)}</div>
      </div>
      <div class="ida-stat">
        <div class="ida-stat__icon">${icon(d.webhook_on ? 'check-circle' : 'alert-triangle', 17)}</div>
        <div class="ida-stat__value" style="font-size:18px;">${d.webhook_on ? 'OK' : 'Manquant'}</div>
        <div class="ida-stat__label">Webhook Stripe</div>
        <div class="ida-stat__sub">${d.events} événements traités</div>
      </div>
      <div class="ida-stat">
        <div class="ida-stat__icon">${icon('external-link', 17)}</div>
        <div class="ida-stat__value" style="font-size:18px;"><a href="${esc(d.dashboard)}" target="_blank" rel="noopener">Ouvrir</a></div>
        <div class="ida-stat__label">Dashboard Stripe</div>
        <div class="ida-stat__sub">factures & paiements</div>
      </div>
    </div>

    ${!d.stripe_on ? `
      <div class="ida-card ida-card--pad" style="border-left:3px solid var(--ida-warning);margin-bottom:16px;">
        <strong>Stripe n'est pas configuré.</strong>
        <p class="ida-card__sub" style="margin:4px 0 0;">
          Les clés (IDC_STRIPE_PUBLIC, IDC_STRIPE_SECRET, price IDs, webhook) se définissent
          dans <code>wp-config.php</code> — jamais en base de données.
        </p>
      </div>` : ''}

    <div class="ida-table-wrap">
      <div class="ida-table-scroll">
        <table class="ida-table">
          <thead><tr><th>Artisan</th><th>Abonnement</th><th class="hide-sm">Email</th><th class="hide-sm">Abonnement Stripe</th><th></th></tr></thead>
          <tbody>
            ${d.rows.map((r) => `
              <tr data-id="${r.id}">
                <td class="cell-main">${esc(r.title)}</td>
                <td>${badge(plans[r.plan] || r.plan, r.plan)}</td>
                <td class="hide-sm">${esc(r.email || '—')}</td>
                <td class="hide-sm">${r.subscription ? `<code style="font-size:11px;">${esc(r.subscription)}</code>` : '—'}</td>
                <td style="width:60px;">
                  ${r.stripe_url ? `<a class="ida-icon-btn" href="${esc(r.stripe_url)}" target="_blank" rel="noopener" title="Client Stripe" aria-label="Client Stripe" onclick="event.stopPropagation()">${icon('external-link', 15)}</a>` : ''}
                </td>
              </tr>`).join('')}
          </tbody>
        </table>
      </div>
    </div>
    <p class="ida-card__sub" style="margin-top:10px;">
      Le plan d'un artisan se modifie sur sa fiche (les paiements réels restent gérés par Stripe).
    </p>`);

  el.querySelectorAll('tbody tr').forEach((tr) =>
    tr.addEventListener('click', () => navigate(`/artisans/${tr.dataset.id}`)));
}
