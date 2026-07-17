# Pages catégories de travaux — taxonomy-metier.php (refonte 16/07/2026)

**Constat** : la base originale n'ayant jamais reçu les colonnes « Brief V2 » (`hero_title`…),
l'ancien site affiche **toujours** les vues par slug `views/categories/slugs/{slug}.php` —
jamais la vue universelle `show.php`. La première version WordPress reproduisait la vue
universelle : c'est ce que Jonathan a signalé (« cela doit ressembler comme cela »).

## Architecture (thème)

- `taxonomy-metier.php` — répartiteur reproduisant la logique de `CategoryController::show()` :
  1. `amenagements-exterieurs` → `template-parts/categorie/amenagements-exterieurs.php` (mise en page propre : hero italique, « L'Excellence du Plein Air » avec cover locale, expertise 360°, méthode 3 colonnes, CTA double bouton) ;
  2. `maconnerie` → `template-parts/categorie/maconnerie.php` (hero en grille avec encart « Qualité Garantie », galerie 2 rangées, prestations détaillées, CTA « vision ») ;
  3. les 16 autres métiers → `template-parts/categorie/slug-standard.php` + données par slug dans `inc/categorie-config.php` (kicker, H1 [HTML si accent italique — carrelage], description par défaut, image hero + 3 images galerie Unsplash identiques à l'original, liste des prestations) ;
  4. métier créé ultérieurement sans vue d'origine → `template-parts/categorie/universelle.php` (port de `show.php`, ancien gabarit conservé en secours).

## Sections de la vue standard (1:1 avec l'original)

Hero plein écran min-h-[870px] (image + dégradé, kicker, H1 6xl/8xl, description = description
du terme sinon défaut original, boutons « Demander un devis » → `/devis/?metier={slug}` et
« Voir les réalisations » → `/categories/`) · galerie bento « Inspirations & Réalisations »
(grande image + légende « {titre} — Finitions premium » + 2 vignettes) · « Des prestations
sur-mesure » centré (cards fallback « Artisans qualifies pour ce type de prestation. » — la table
`services` legacy est vide) · « Pourquoi nous faire confiance ? » + « Le Processus InfoDevis »
(textes de la variante slug) · « Artisans recommandés » (3 fiches publiées du métier, tri note
décroissante, initiale du prénom du gérant, ville, étoiles ★☆, note) · « Autres corps de
métiers » (4 termes au hasard) · CTA vert « Prêt pour vos travaux de {nom} ? ».

FAQ : `seo_pages` est vide côté legacy → la section ne s'affichait jamais ; non portée
(à ajouter si une source de données FAQ est créée un jour).

## Données / assets

- `inc/categorie-config.php` : map des 16 slugs + helpers `idv_categorie_top_artisans()` / `idv_categorie_related()`.
- Cover locale copiée : `assets/images/categories/amenagements-exterieurs/cover.jpg` (depuis `assets/img/categories/` de l'original).
- Images Unsplash : mêmes URLs hotlinkées que l'original (fidélité stricte).

## Tests (16/07/2026)

HTTP 200 sans erreur PHP : energies-renouvelables, plomberie, maconnerie,
amenagements-exterieurs, carrelage, toiture. Captures pleine page validées contre le modèle :
`docs/captures/wordpress/categorie-{energies-renouvelables,maconnerie,amenagements-exterieurs}.png`
(hero, bento, prestations, processus, artisans recommandés [Germain Roux / targareyn],
autres corps de métiers, CTA — sections vides masquées comme dans l'original).

Sauvegarde de l'ancien gabarit : `backups/20260716-categorie-avant/taxonomy-metier.php`.
