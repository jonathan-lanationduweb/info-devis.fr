/**
 * Tableau de bord.
 */

import { api, esc } from '../api.js';
import { icon } from '../icons.js';
import { barChart, skeletonRows } from '../ui.js';
import { createThenEdit } from '../app.js';

export async function viewDashboard(el) {
  el.innerHTML = skeletonRows(7);
  const d = await api.get('/dashboard');

  el.innerHTML = `
    <div class="ida-greeting">
      <h1>Bonjour ${esc(d.greeting.name)} 👋</h1>
      <p>${esc(d.greeting.date)}</p>
    </div>

    <div class="ida-stats">
      ${d.stats.map((s) => `
        <a class="ida-stat" href="${esc(s.link)}">
          <div class="ida-stat__icon">${icon(s.icon, 17)}</div>
          <div class="ida-stat__value">${s.value}</div>
          <div class="ida-stat__label">${esc(s.label)}</div>
          <div class="ida-stat__sub">${esc(s.sub)}</div>
        </a>`).join('')}
    </div>

    <div class="ida-dash-grid">
      <div class="ida-dash-col">
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">${esc(d.chart.title)}</h2>
          ${barChart(d.chart.data, 170)}
        </div>
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">Activité récente</h2>
          ${d.activity.length ? `
            <ul class="ida-activity">
              ${d.activity.map((a) => `
                <li>
                  <span class="dot">${icon(a.icon, 15)}</span>
                  <div class="txt">
                    <strong><a href="${esc(a.link)}" style="color:inherit;">${esc(a.title)}</a></strong>
                    <small>${esc(a.type)} · ${esc(a.status)} · ${esc(a.ago)}</small>
                  </div>
                </li>`).join('')}
            </ul>` : '<p class="ida-card__sub">Aucune activité récente.</p>'}
        </div>
      </div>

      <div class="ida-dash-col">
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">À traiter</h2>
          <ul class="ida-todo">
            ${d.todo.map((t) => `
              <li><a href="${esc(t.link)}">
                <span class="n ${t.count === 0 ? 'is-zero' : ''}">${t.count}</span>
                <span>${esc(t.label)}</span>
                ${icon('chevron-right', 15)}
              </a></li>`).join('')}
          </ul>
        </div>
        <div class="ida-card ida-card--pad">
          <h2 class="ida-card__title">État du système</h2>
          <div class="ida-health">
            ${d.health.map((h) => `
              <span class="ida-badge ida-badge--${h.ok ? 'success' : 'danger'}">
                ${icon(h.ok ? 'check' : 'x', 12)} ${esc(h.label)}
              </span>`).join('')}
          </div>
          <p class="ida-card__sub" style="margin-top:10px;">
            <a href="#/parametres">Détails dans les réglages →</a>
          </p>
        </div>
      </div>
    </div>

    <div class="ida-card ida-card--pad" style="margin-top:16px;">
      <h2 class="ida-card__title">Accès rapides</h2>
      <div class="ida-quick">
        <button class="ida-btn ida-btn--secondary" data-quick="artisan:artisans">${icon('hard-hat', 15)} Ajouter un artisan</button>
        <button class="ida-btn ida-btn--secondary" data-quick="article:articles">${icon('newspaper', 15)} Créer un article</button>
        <button class="ida-btn ida-btn--secondary" data-quick="guide:guides">${icon('book-open', 15)} Créer un guide</button>
        <button class="ida-btn ida-btn--secondary" data-quick="faq:faq">${icon('help-circle', 15)} Créer une FAQ</button>
        <button class="ida-btn ida-btn--secondary" data-quick="demande:demandes">${icon('file-text', 15)} Créer une demande</button>
        <a class="ida-btn ida-btn--secondary" href="#/paiements">${icon('euro', 15)} Voir les abonnements</a>
        <a class="ida-btn ida-btn--secondary" href="#/seo">${icon('trending-up', 15)} Statistiques SEO</a>
      </div>
    </div>`;

  el.querySelectorAll('[data-quick]').forEach((b) => {
    b.addEventListener('click', () => {
      const [type, route] = b.dataset.quick.split(':');
      createThenEdit(type, route);
    });
  });
}
