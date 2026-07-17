/**
 * SEO : vue d'ensemble (score, contenus à corriger, sitemap, Yoast).
 */

import { api, esc } from '../api.js';
import { icon } from '../icons.js';
import { pageHead, badge, skeletonRows, emptyState } from '../ui.js';
import { navigate } from '../app.js';

export async function viewSeo(el) {
  el.innerHTML = pageHead({
    title: 'SEO',
    sub: 'Synthèse du référencement : métadonnées, indexation, sitemap.',
  }) + skeletonRows(5);
  const d = await api.get('/seo');

  el.querySelectorAll('.ida-skeleton').forEach((s) => s.remove());
  el.insertAdjacentHTML('beforeend', `
    <div class="ida-stats" style="grid-template-columns:repeat(4,1fr);">
      <div class="ida-stat">
        <div class="ida-stat__icon">${icon('trending-up', 17)}</div>
        <div class="ida-stat__value" style="color:${d.score >= 80 ? 'var(--ida-success)' : d.score >= 50 ? 'var(--ida-warning)' : 'var(--ida-danger)'};">${d.score}%</div>
        <div class="ida-stat__label">Score global</div>
        <div class="ida-stat__sub">metas renseignées</div>
      </div>
      <div class="ida-stat">
        <div class="ida-stat__icon">${icon('globe', 17)}</div>
        <div class="ida-stat__value">${d.indexables}</div>
        <div class="ida-stat__label">Contenus indexables</div>
        <div class="ida-stat__sub">pages, articles, guides, fiches</div>
      </div>
      <div class="ida-stat">
        <div class="ida-stat__icon">${icon('alert-triangle', 17)}</div>
        <div class="ida-stat__value">${d.missing}</div>
        <div class="ida-stat__label">À corriger</div>
        <div class="ida-stat__sub">metas manquantes / titres longs</div>
      </div>
      <div class="ida-stat">
        <div class="ida-stat__icon">${icon('link', 17)}</div>
        <div class="ida-stat__value" style="font-size:16px;"><a href="${esc(d.sitemap)}" target="_blank" rel="noopener">sitemap.xml</a></div>
        <div class="ida-stat__label">Sitemap</div>
        <div class="ida-stat__sub">généré par Yoast</div>
      </div>
    </div>

    <div class="ida-page-head" style="margin-top:8px;">
      <h1 style="font-size:16px;">Contenus à corriger</h1>
      <div class="ida-page-head__actions">
        ${d.yoast_on ? `<a class="ida-btn ida-btn--secondary" href="${esc(d.yoast_admin)}&classic=1" target="_blank" rel="noopener">${icon('external-link', 14)} Ouvrir Yoast</a>` : badge('Yoast inactif', 'pending')}
      </div>
    </div>

    ${d.issues.length ? `
      <div class="ida-table-wrap"><div class="ida-table-scroll">
        <table class="ida-table">
          <thead><tr><th>Type</th><th>Titre</th><th>Problème</th><th></th></tr></thead>
          <tbody>
            ${d.issues.map((issue) => `
              <tr data-link="${esc(issue.link)}">
                <td>${badge(issue.type, 'draft')}</td>
                <td class="cell-main">${esc(issue.title)}</td>
                <td style="color:var(--ida-text-soft);">${esc(issue.problem)}</td>
                <td style="width:100px;"><span class="ida-btn ida-btn--secondary ida-btn--sm">Corriger</span></td>
              </tr>`).join('')}
          </tbody>
        </table>
      </div></div>`
    : `<div class="ida-card">${emptyState({ iconName: 'check-circle', title: 'Tout est en ordre', text: 'Tous les contenus publiés ont leurs métadonnées SEO.' })}</div>`}`);

  el.querySelectorAll('tr[data-link]').forEach((tr) =>
    tr.addEventListener('click', () => navigate(tr.dataset.link.replace('#', ''))));
}
