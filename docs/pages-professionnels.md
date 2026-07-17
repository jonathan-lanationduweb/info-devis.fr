# Page Professionnels — page-professionnels.php

**Modèle :** `views/pages/professionnels.php` (site original), porté 1:1 lors de l'étape 8 et re-vérifié le 16/07/2026 contre le cahier des charges.

- **URL** : `/professionnels/` (+ 301 depuis `/artisans/`).
- **En-tête éditorial** : « Découvrez nos *architectes* et *professionnels* disponibles » — mots en **capsules noires** via `.pill-title` (portfolio.css original), serif Newsreader, paragraphe d'intro.
- **Barre de recherche** `.search-pill` : champ « Quel professionnel recherchez-vous ? » (recherche WP sur nom/raison sociale/description) + champ « Où ? » (LIKE sur `_idc_ville`) + bouton vert, icônes Material, séparateur vertical — fonctionne réellement (GET q/ville).
- **Filtres métiers** : capsules `.btn-outline-pill` « Tous » + les 18 métiers (taxonomie `metier`, dynamiques), état actif (bordure/fond verts), liens accessibles clavier, **ligne défilable sur mobile** (`.idv-filter-pills` dans responsive.css).
- **Résultats** : compteur réel (`found_posts`), tri Recommandés / Mieux notés (meta `_idc_rating_avg`) / Plus d'avis / Plus récents — aucune valeur fictive.
- **Section « Artisans recommandés »** (Top 5 Gold, médaille FA) uniquement en tri par défaut sans recherche, comme l'original.
- **Carte artisan** `template-parts/card-pro.php` : cover (`_idc_cover_url`), badge compteur de réalisations (calcul réel), bouton favori, avatar circulaire en débord, nom serif, « Artisan qualifié », tags métiers avec icônes FA (max 3 + compteur), badge de niveau, ville, ancienneté, bio 2 lignes, bouton « Voir le profil » pleine largeur — 100 % dynamique.
- **Grille** : 1/2/3 colonnes (Tailwind `md:grid-cols-2 lg:grid-cols-3`) — une carte seule garde sa largeur de colonne ; **pagination** `paginate_links` (paramètre `pg`).
- **États** : aucun résultat (icône + « Réinitialiser les filtres »), 1 ou N professionnels — seules les fiches **publiées** (validées) apparaissent.
- **SEO** : titres/canonical via Yoast ; la page est indexable, les combinaisons filtrées passent par des paramètres GET (non générées comme pages).
- **Performance** : 24 fiches/page, une requête WP_Query principale, comptage réalisations par carte (accepté à cette volumétrie — à mutualiser si le volume grossit), `loading="lazy"` sur les couvertures.
