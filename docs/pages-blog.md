# Blog — home.php (liste) + single.php (article) — 16/07/2026

**Modèles :** `views/blog/index.php` (164 l.) et `views/blog/show.php` (261 l.).

## Liste `/blog/` — home.php (déjà fidèle, ajusté)

En-tête éditorial (« Conseils & guides travaux », H1 « Blog InfoDevis »), grille 1/2/3 cards
(cover 16/10 ou icône FA par catégorie sur dégradé, badge catégorie [« Conseils » par défaut],
date `d/m/Y`, titre serif, extrait 2 lignes, pied avec temps de lecture + « Lire → »), CTA vert
« Prêt à lancer vos travaux ? ». **Ajustement fidélité** : la liste affiche toujours
« 5 min de lecture » — comme l'original (`$article['read_time'] ?? '5 min'`, champ jamais
fourni pour les articles en base ; seul l'article calcule le temps réel).

## Article — single.php (réécrit 1:1 depuis show.php)

- Kicker « Le Curateur de l'Habitat — Guide Expert », H1 serif italique 5xl/7xl, colonne droite badge catégorie (terme métier sinon « Conseils » + icône FA) + « Publié le d/m/Y » + trait.
- Hero image à la une 400/500 px arrondie + dégradé + **encart extrait** (140 car., carte blur en bas à gauche).
- Grille 12 : **sidebar sticky** (Sommaire → #contenu/#devis, citation « Un devis gratuit… », carte CTA verte « Besoin d'un artisan ? ») + article col-9.
- Intro = extrait (bordure gauche verte), corps `.article-body` avec le bloc `<style>` original copié verbatim (H2/H3 Newsreader, puces rondes vertes, blockquote, img, code…).
- Pied : « N min de lecture · N mots » (calcul ≈200 mots/min identique) + « Par {prénom nom de l'auteur} » (fallback display_name).
- « L'avis de l'expert » (citation fixe) + CTA « Besoin d'un artisan pour vos travaux de {catégorie en minuscules} ? » → /devis/.
- **Non porté (bug d'origine)** : « Articles similaires » — le contrôleur passe `related`, la vue attend `relatedArticles` → la section ne s'est jamais affichée sur l'ancien site.
- Les autres types de contenus (guides…) conservent la bannière générique dans single.php.

## Données (scratchpad/sync-blog.php, rejouable)

- Dates WP alignées sur `created_at` legacy (ce que l'original affiche) — corrigé : toiture → 15/11/2025, Germain → 22/05/2026.
- Cover de l'article Germain Roux importée en médiathèque (`.jfif` renommé `.jpg`, WP refuse l'extension jfif) → image à la une (#105).
- « Bonjour tout le monde ! » (article d'exemple WordPress) mis à la corbeille.

## Tests

`/blog/` HTTP 200 : 7 cards, cover Germain, dates conformes, « 5 min de lecture » ×7.
Article Germain HTTP 200 sans erreur PHP : kicker, hero + encart extrait, sidebar, sections,
« 2 min de lecture · ~340 mots · Par tom arto », avis expert, CTA. Captures :
`docs/captures/wordpress/blog-liste.png` + `blog-article-germain.png` (validées contre le modèle).
