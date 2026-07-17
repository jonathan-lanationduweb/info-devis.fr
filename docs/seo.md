# SEO

## Répartition des responsabilités

- **Yoast SEO** (extension active) : titres, méta-descriptions, canonicals, Open Graph/Twitter, sitemap XML (`/sitemap_index.xml`), fil d'Ariane disponible. Ne pas dupliquer ces fonctions dans le code maison.
- **Extension Info Devis** (`includes/seo.php`) : ce que Yoast ne connaît pas —
  - JSON-LD `ProfessionalService` sur chaque fiche artisan (nom, adresse ville/CP, téléphone, métiers) ;
  - `AggregateRating` ajouté **uniquement** si des avis approuvés existent réellement (pas de données structurées trompeuses) ;
  - `noindex` sur espace membre, inscription artisan et résultats de recherche interne.

## État technique

- Permaliens propres : `/%postname%/`, `/artisan/{slug}/`, `/metier/{slug}/`, `/guide/{slug}/`, `/realisation/{slug}/`
- Slugs historiques conservés (catégories, guides, articles) → continuité SEO
- Page 404 fonctionnelle (HTTP 404 vérifié)
- Sitemap vérifié : HTTP 200
- Site en « ne pas indexer » **volontairement** tant qu'on est en local — à décocher en production (Réglages → Lecture)

## Redirections

Voir `plan-redirections.csv`. À la mise en production, implémenter les 301 (module Redirections de Yoast Premium, extension Redirection, ou `.htaccess`). Point clé : les fiches artisans passent d'un ID (`/artisan/12`) à un slug — générer la table de correspondance depuis les meta `_idc_legacy_id`.

## À faire en production

1. Décocher la case anti-indexation.
2. Configurer Yoast (assistant : identité Organization, logo, réseaux sociaux) → schémas `Organization`/`WebSite` gérés par Yoast.
3. Déployer les redirections + tester (curl -I sur chaque ancienne URL).
4. Search Console : soumettre le sitemap.
5. Compléter les données de term meta (`_idc_meta_title`, `_idc_meta_description` migrées) dans les réglages Yoast des termes, ou brancher un filtre pour les servir automatiquement.
6. Attributs alt lors de l'ajout des images en médiathèque.
