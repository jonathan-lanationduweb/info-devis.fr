/**
 * Gestion du site (cartes) + éditeur de la page d'accueil par sections.
 */

import { api, tryApi, esc } from '../api.js';
import { icon } from '../icons.js';
import {
  pageHead, openDrawer, toast, skeletonRows, field, input, textarea,
  switchInput, imagePickerHtml, mountImagePickers, readForm,
} from '../ui.js';
import { store } from '../app.js';

const CFG = window.IDA;

/* ── Gestion du site ────────────────────────────────────────────────────── */

export async function viewSite(el) {
  el.innerHTML = pageHead({
    title: 'Gestion du site',
    sub: 'Toutes les pages et sections du site, modifiables sans passer par WordPress.',
    actions: `<a class="ida-btn ida-btn--secondary" href="${esc(CFG.homeUrl)}" target="_blank" rel="noopener">${icon('external-link', 15)} Voir le site</a>`,
  }) + skeletonRows(4);

  const [pages, articles, guides] = await Promise.all([
    api.get('/list/page?per_page=100'),
    api.get('/list/article?per_page=1'),
    api.get('/list/guide?per_page=1'),
  ]);

  const bySlug = {};
  pages.rows.forEach((p) => { bySlug[p.slug] = p; });
  const pageCard = (slug, title, sub, iconName) => {
    const p = bySlug[slug];
    return card({
      title, sub, iconName,
      actions: p
        ? `<a class="ida-btn ida-btn--primary ida-btn--sm" href="#/pages/${p.id}">Modifier</a>
           <a class="ida-btn ida-btn--ghost ida-btn--sm" href="${esc(p.view)}" target="_blank" rel="noopener">Aperçu ${icon('external-link', 12)}</a>`
        : '<span class="ida-badge ida-badge--warning">Page introuvable</span>',
    });
  };
  const card = ({ title, sub, iconName, actions }) => `
    <div class="ida-card ida-card--hover ida-site-card">
      <div class="ida-site-card__icon">${icon(iconName, 18)}</div>
      <div class="ida-card__title">${esc(title)}</div>
      <div class="ida-card__sub">${esc(sub)}</div>
      <div class="ida-site-card__actions">${actions}</div>
    </div>`;

  el.querySelectorAll('.ida-skeleton').forEach((s) => s.remove());
  el.insertAdjacentHTML('beforeend', `
    <div class="ida-cards-grid">
      ${card({
        title: 'Accueil', sub: 'Hero, expertises, étapes, citation, CTA…', iconName: 'layout-grid',
        actions: `<a class="ida-btn ida-btn--primary ida-btn--sm" href="#/site/accueil">Modifier</a>
                  <a class="ida-btn ida-btn--ghost ida-btn--sm" href="${esc(CFG.homeUrl)}" target="_blank" rel="noopener">Aperçu ${icon('external-link', 12)}</a>`,
      })}
      ${card({
        title: 'Métiers', sub: `${store.metiers.length} métiers`, iconName: 'wrench',
        actions: `<a class="ida-btn ida-btn--primary ida-btn--sm" href="#/metiers">Modifier</a>
                  ${bySlug['categories'] ? `<a class="ida-btn ida-btn--ghost ida-btn--sm" href="${esc(bySlug['categories'].view)}" target="_blank" rel="noopener">Aperçu ${icon('external-link', 12)}</a>` : ''}`,
      })}
      ${pageCard('professionnels', 'Professionnels', 'Annuaire des artisans', 'hard-hat')}
      ${card({
        title: 'Blog', sub: `${articles.total} articles`, iconName: 'newspaper',
        actions: `<a class="ida-btn ida-btn--primary ida-btn--sm" href="#/articles">Voir les articles</a>
                  <a class="ida-btn ida-btn--secondary ida-btn--sm" href="#/articles?new=1">+ Ajouter</a>`,
      })}
      ${card({
        title: 'Guides & Prix', sub: `${guides.total} guides`, iconName: 'book-open',
        actions: `<a class="ida-btn ida-btn--primary ida-btn--sm" href="#/guides">Voir les guides</a>
                  <a class="ida-btn ida-btn--secondary ida-btn--sm" href="#/guides?new=1">+ Ajouter</a>`,
      })}
      ${pageCard('tarifs-pro', 'Tarifs Pro', 'Offres destinées aux artisans', 'euro')}
      ${pageCard('contact', 'Contact', 'Page de contact', 'mail')}
      ${pageCard('devis', 'Demande de devis', 'Formulaire principal', 'file-text')}
      ${pageCard('mentions-legales', 'Mentions légales', 'Page légale', 'file-text')}
      ${pageCard('confidentialite', 'Confidentialité', 'Politique de confidentialité', 'shield-check')}
      ${pageCard('cgv', 'CGV', 'Conditions générales', 'file-text')}
      ${pageCard('plan-du-site', 'Plan du site', 'Arborescence publique', 'panels-top-left')}
      ${card({
        title: 'Toutes les pages', sub: `${pages.total} pages au total`, iconName: 'panels-top-left',
        actions: '<a class="ida-btn ida-btn--secondary ida-btn--sm" href="#/pages">Ouvrir la liste</a>',
      })}
      ${card({
        title: 'Menus', sub: 'Navigation principale du site', iconName: 'list',
        actions: `<a class="ida-btn ida-btn--secondary ida-btn--sm" href="${esc(CFG.adminUrl)}nav-menus.php?classic=1" target="_blank" rel="noopener">Gérer les menus ${icon('external-link', 12)}</a>`,
      })}
    </div>`);
}

/* ── Éditeur de page d'accueil ──────────────────────────────────────────── */

/** Formulaires par section : [clé, label, description, buildForm(values)]. */
const SECTIONS = [
  {
    key: 'hero', label: 'Hero', desc: 'Titre principal, image de fond, barre de recherche',
    form: (v) => `
      ${field('Titre', input('title', v.title))}
      ${field('Mot accentué (vert, italique)', input('title_accent', v.title_accent))}
      ${field('Sous-titre (optionnel)', input('subtitle', v.subtitle))}
      ${field('Image de fond (ordinateur)', imagePickerHtml('image', v.image))}
      ${field('Image de fond (mobile — optionnelle)', imagePickerHtml('image_mobile', v.image_mobile))}
      <div class="ida-form-grid">
        ${field('Voile sombre (%)', input('overlay', v.overlay, 'type="number" min="0" max="90"'))}
        ${field('Hauteur minimale (px)', input('height', v.height, 'type="number" min="400" max="1200"'))}
      </div>
      ${field('', switchInput('show_search', v.show_search, 'Afficher la barre de recherche'))}
      <div class="ida-form-grid">
        ${field('Texte du bouton', input('btn1_label', v.btn1_label))}
        ${field('Lien du bouton', input('btn1_url', v.btn1_url))}
      </div>`,
  },
  {
    key: 'reassurance', label: 'Barre de réassurance', desc: 'Bandeau vert sous le hero',
    form: (v) => v.items.map((item, i) =>
      field(`Élément ${i + 1}`, input(`item_${i}`, item))).join(''),
    read: (form) => ({ items: [form.item_0, form.item_1, form.item_2].filter((x) => x !== undefined) }),
  },
  {
    key: 'expertises', label: 'Nos expertises', desc: 'Grille bento des métiers (ordre géré dans Métiers)',
    form: (v) => `
      ${field('Sur-titre', input('kicker', v.kicker))}
      ${field('Titre', input('title', v.title))}
      ${field('Texte du lien', input('link', v.link))}
      <p class="hint" style="font-size:12px;color:var(--ida-text-faint);">
        Les cartes affichées reprennent les premiers métiers dans l'ordre défini sur
        l'écran <a href="#/metiers">Métiers</a> (glisser-déposer).
      </p>`,
  },
  {
    key: 'etapes', label: 'Comment ça marche', desc: '3 étapes',
    form: (v) => `
      ${field('Titre', input('title', v.title))}
      ${field('Introduction', textarea('intro', v.intro, 2))}
      ${v.steps.map((s, i) => `
        <div class="ida-card ida-card--pad" style="margin-bottom:12px;padding:14px;">
          <strong style="font-size:12px;">Étape ${i + 1}</strong>
          ${field('Titre', input(`step_${i}_title`, s.title))}
          ${field('Description', textarea(`step_${i}_desc`, s.desc, 2))}
        </div>`).join('')}`,
    read: (form, v) => ({
      title: form.title,
      intro: form.intro,
      steps: v.steps.map((s, i) => ({ title: form[`step_${i}_title`], desc: form[`step_${i}_desc`] })),
    }),
  },
  {
    key: 'professionnels', label: 'Professionnels', desc: '3 derniers artisans publiés',
    form: (v) => `
      <div class="ida-form-grid">
        ${field('Titre', input('title', v.title))}
        ${field('Mot accentué', input('accent', v.accent))}
      </div>
      ${field('Introduction', textarea('intro', v.intro, 2))}`,
  },
  {
    key: 'realisations', label: 'Réalisations', desc: '3 dernières réalisations publiées',
    form: (v) => `
      <div class="ida-form-grid">
        ${field('Titre', input('title', v.title))}
        ${field('Mot accentué', input('accent', v.accent))}
      </div>
      ${field('Introduction', textarea('intro', v.intro, 2))}`,
  },
  {
    key: 'citation', label: 'Citation', desc: 'Bloc citation',
    form: (v) => `
      ${field('Citation', textarea('texte', v.texte, 4))}
      ${field('Auteur', input('auteur', v.auteur))}`,
  },
  {
    key: 'cta', label: 'CTA final', desc: 'Bandeau vert « Prêt à lancer vos travaux ? »',
    form: (v) => `
      ${field('Titre', input('title', v.title))}
      ${field('Introduction', textarea('intro', v.intro, 2))}
      <div class="ida-form-grid">
        ${field('Bouton 1 — texte', input('btn1_label', v.btn1_label))}
        ${field('Bouton 1 — lien', input('btn1_url', v.btn1_url))}
        ${field('Bouton 2 — texte', input('btn2_label', v.btn2_label))}
        ${field('Bouton 2 — lien', input('btn2_url', v.btn2_url))}
      </div>`,
  },
];

export async function viewHomeEditor(el) {
  el.innerHTML = pageHead({
    breadcrumb: '<a href="#/site">Gestion du site</a> › Accueil',
    title: 'Page d\'accueil',
    sub: 'Chaque section s\'édite dans son panneau. Les modifications sont visibles immédiatement sur le site.',
    actions: `<a class="ida-btn ida-btn--secondary" href="${esc(CFG.homeUrl)}" target="_blank" rel="noopener">${icon('external-link', 15)} Aperçu</a>`,
  }) + skeletonRows(6);

  const data = await api.get('/home');
  let sections = data.sections;

  const render = () => {
    const listEl = el.querySelector('[data-sections]') || (() => {
      el.querySelectorAll('.ida-skeleton').forEach((s) => s.remove());
      const div = document.createElement('div');
      div.className = 'ida-sections';
      div.setAttribute('data-sections', '');
      el.appendChild(div);
      return div;
    })();

    listEl.innerHTML = SECTIONS.map((s) => {
      const v = sections[s.key];
      const preview = v.title || v.texte || (v.items ? v.items.join(' · ') : '') || '';
      return `
        <div class="ida-section-row">
          <span class="grip" aria-hidden="true">${icon('grip-vertical', 16)}</span>
          <div class="info">
            <strong>${esc(s.label)}</strong>
            <small>${esc(preview || s.desc)}</small>
          </div>
          <span class="ida-switch" title="Visible sur le site">
            <input type="checkbox" data-visible="${s.key}" ${v.visible ? 'checked' : ''} aria-label="Section ${esc(s.label)} visible">
            <span class="track"></span>
          </span>
          <button class="ida-btn ida-btn--secondary ida-btn--sm" data-edit="${s.key}">
            ${icon('pencil', 13)} Modifier
          </button>
        </div>`;
    }).join('');

    listEl.querySelectorAll('[data-visible]').forEach((cb) => {
      cb.addEventListener('change', async () => {
        sections[cb.dataset.visible].visible = cb.checked;
        await save(cb.dataset.visible);
      });
    });
    listEl.querySelectorAll('[data-edit]').forEach((btn) => {
      btn.addEventListener('click', () => openSectionDrawer(btn.dataset.edit));
    });
  };

  const save = async (key) => {
    await tryApi(api.post('/home', { sections: { [key]: sections[key] } }), 'Enregistrement');
    toast('Section enregistrée');
  };

  const openSectionDrawer = (key) => {
    const def = SECTIONS.find((s) => s.key === key);
    const values = sections[key];
    openDrawer({
      title: def.label,
      width: 520,
      body: def.form(values),
      footer: `
        <button class="ida-btn ida-btn--secondary" data-cancel>Annuler</button>
        <button class="ida-btn ida-btn--primary" data-save>${icon('save', 15)} Enregistrer</button>`,
      onMount: (drawer, close) => {
        mountImagePickers(drawer.querySelector('.ida-drawer__body'));
        drawer.querySelector('[data-cancel]').addEventListener('click', close);
        drawer.querySelector('[data-save]').addEventListener('click', async () => {
          const form = readForm(drawer.querySelector('.ida-drawer__body'));
          const parsed = def.read ? def.read(form, values) : form;
          sections[key] = { ...values, ...parsed };
          await save(key);
          close();
          render();
        });
      },
    });
  };

  render();
}
