# InfoDevis Admin — Design system & composants

## 1. Fondations

### Palette

| Token | Valeur | Usage |
|---|---|---|
| `--ida-primary` | `#207752` (vert InfoDevis, repris du thème) | Actions principales, état actif |
| `--ida-primary-dark` | `#165a3c` | Hover |
| `--ida-primary-soft` | `#e7f4ee` | Fonds actifs, badges succès |
| `--ida-navy` | `#0f1f33` (bleu nuit) | Sidebar, titres, texte fort |
| `--ida-bg` | `#f6f7f9` | Fond de l'app |
| `--ida-surface` | `#ffffff` | Cartes, panneaux |
| `--ida-border` | `#e5e8ec` | Bordures 1px |
| `--ida-text` | `#1a2433` / `--ida-text-soft` `#5c6675` / `--ida-text-faint` `#8b95a5` | Texte |
| Sémantiques | `--ida-success #1a7f4e` · `--ida-warning #b45309` · `--ida-danger #c2410c`/`#dc2626` · `--ida-info #2563eb` | Badges, alertes |

Chaque couleur sémantique a sa variante `-soft` pour les fonds de badge.
Le mode sombre est prévu : tous les composants consomment exclusivement les variables
CSS ; un bloc `[data-theme="dark"]` les redéfinira (non activé en v1).

### Typographie

- **Manrope** (déjà utilisée sur le front) : interface, 13–14 px de base.
- Titres : 20/24 px semi-bold, chiffres clés : 28 px bold, `font-variant-numeric: tabular-nums`.

### Espacement, rayons, ombres

- Grille 4 px. Padding cartes : 20–24 px. Vides généreux (inspiration Notion).
- Rayons : `--ida-r-sm 8px`, `--ida-r 12px` (cartes), `--ida-r-lg 16px`, pilules `999px`.
- Ombres : `--ida-shadow-sm` (0 1px 2px rgba(15,31,51,.06)) au repos,
  `--ida-shadow` (0 4px 16px rgba(15,31,51,.08)) au survol/overlay.
- Animations : 150–200 ms `ease`, translations 2–4 px max, `fadeInUp` discret à
  l'affichage des vues. `prefers-reduced-motion` respecté.

### Icônes

Lucide, sous-ensemble inliné en SVG (`icons.js`) — aucun CDN. 24 px stroke 2 dans la
navigation, 16–18 px dans les composants.

## 2. Composants (assets/js/ui.js)

| Composant | Description |
|---|---|
| `Icon(name, size)` | SVG Lucide inline |
| `Badge(label, tone)` | Pilule statut (`success/warning/danger/info/neutral`) — statuts demandes, plans, badges artisan |
| `StatCard` | Carte KPI du dashboard : icône, valeur, libellé, delta |
| `DataTable` | Table générique : colonnes déclaratives, tri, pagination serveur, recherche instantanée (debounce), filtres (selects), sélection multiple + actions groupées, menu d'actions par ligne (⋯), états vide/chargement (skeleton) |
| `CardGrid` | Grille de cartes (Gestion du site, Métiers) |
| `Drawer` | Panneau latéral droit (édition rapide, panneaux de sections de l'accueil) — focus trap, Échap pour fermer |
| `Modal` | Confirmations (suppression…), focus trap |
| `Toast` | Notifications éphémères en bas à droite (succès/erreur) |
| `Tabs` | Onglets de sous-navigation d'écran |
| `EmptyState` | Illustration + texte + CTA |
| `Menu` | Menu contextuel (actions ligne, bouton « + Nouveau ») |
| `ImagePicker` | Champ image : aperçu, upload direct (`wp/v2/media`), retrait |
| `RichText` | Éditeur léger contenteditable : gras, italique, H2/H3, listes, lien, image ; lien « Éditeur avancé » vers Gutenberg |
| `Field/Form` | Génération de formulaires depuis un schéma (text, textarea, select, toggle, number, image, color) |
| `Kanban` | Colonnes + cartes déplaçables (drag & drop natif), compteur par colonne, changement de statut à la dépose |
| `Calendar` | Vues mois / semaine / liste pour les rendez-vous |
| `Chart` | Graphiques SVG maison (barres + ligne/aire) pour le dashboard — pas de dépendance |
| `CommandPalette` | Recherche globale ⌘K : overlay, groupes par type, navigation clavier |

## 3. Patterns d'écran

### Liste (articles, guides, artisans, clients…)
En-tête (titre + compteur + bouton « + ») → barre outils (recherche, filtres, tri,
bascule de vue le cas échéant) → `DataTable` ou grille de cartes → pagination.
Ligne cliquable = édition. Actions ⋯ : Modifier, Aperçu, Dupliquer, Corbeille.

### Édition (article, guide, artisan…)
Page double colonne : contenu principal à gauche (titre, RichText), panneau droit
(statut + bouton Publier, image à la une, catégorie, SEO Yoast : titre/description +
compteur de caractères, champs métier `_idc_*`). Enregistrement AJAX, toast, aucun rechargement.

### Panneaux de sections (page d'accueil)
Liste verticale des sections (Hero, Expertises, Comment ça marche, Professionnels,
Citation, CTA, Footer) avec aperçu réduit + interrupteur de visibilité ; clic → `Drawer`
avec le formulaire de la section ; bouton « Aperçu » ouvre le front dans un nouvel onglet.

## 4. Accessibilité

- Navigation clavier complète (tab order logique, focus visible `outline` 2 px vert).
- Overlays : `role="dialog"`, `aria-modal`, focus trap, retour du focus à l'ouverture/fermeture.
- Tables : `<th scope>`, boutons avec `aria-label`, badges avec texte réel (pas seulement couleur).
- Kanban : boutons « Déplacer vers… » en alternative au drag & drop.
- Contrastes AA sur tous les couples texte/fond du design system.
