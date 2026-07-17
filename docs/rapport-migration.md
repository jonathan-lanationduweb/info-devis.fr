# Rapport de migration des données — infodevis → WordPress

**Exécutée le :** 15 juillet 2026 (locale, MySQL 8.4.7 port 3306)
**Scripts :** idempotents et rejouables (voir `migration-donnees.md` pour la méthode).

## Résultats

| Donnée | Source | Volume migré | Destination | Notes |
|---|---|---|---|---|
| Catégories métiers | `categories` | 18/18 | Termes taxonomie `metier` + term meta (icône, image, prix min/max, SEO) | Slugs conservés à l'identique |
| Utilisateurs | `users` | 5 créés + 1 déjà existant | `wp_users` + rôles `administrator`/`artisan`/`client` | **Hashs bcrypt `$2y$` importés tels quels — mots de passe historiques fonctionnels** (supportés nativement par WordPress ≥ 6.8, vérifié par connexion réelle) |
| Artisans | `artisans` + `artisan_categories` | 3/3 | CPT `artisan` publiés + ~15 meta + termes métiers + lien compte | Rayon, plan, badges, statuts docs conservés |
| Demandes de devis | `devis` + `devis_categories` | 2/2 | CPT `demande_devis` | Références, statuts, budgets conservés |
| Avis | `avis` | 0 (table vide) | CPT `avis` | Workflow prêt, testé avec des données de test |
| Guides | `guides` | 18/18 | CPT `guide` (contenu HTML reconstruit depuis les champs JSON intro/quand/apport/questions) | D'abord migrés en articles puis convertis en CPT |
| Articles de blog | `blog_posts` | 7/7 | Articles natifs, catégorie « Blog » | Statuts mappés (published→publish, rejected→draft), auteurs reliés |
| Consentements | `consents` | non migrés | — | Données de test ; le nouveau site horodate ses propres consentements (`_idc_consent_at`) |
| RDV / créneaux | `rdv`, `artisan_creneaux` | non migrés | CPT `rdv` prêt | Données de test ; re-saisie plus sûre que migration |
| Paiements / abonnements | `abonnements`, `paiements` | non migrés | user meta Stripe | 2 abonnements de test ; les nouveaux abonnements passent par Stripe Checkout |
| Fichiers uploads | `uploads/` | non migrés | Médiathèque WP | 3 documents de test ; chemins historiques conservés en meta `_idc_legacy_cover` pour ré-import |

## Traçabilité et rejouabilité

- Chaque contenu migré porte une meta `_idc_legacy_id` (ou `_idc_legacy_guide_id` / `_idc_legacy_blog_id`) pointant vers l'ID de la table d'origine.
- Les scripts vérifient l'existence (slug, email, legacy id) avant toute insertion : relancer une migration ne crée **aucun doublon**.
- La base `infodevis` n'a subi **aucune modification** (lecture seule).

## Vérifications effectuées

- Comptage post-migration en base (`wp_posts` par type/statut) ✔
- Connexion réelle avec un compte migré ✔
- Affichage front des 18 catégories, 3 fiches artisans, guides et articles ✔
- Encodage utf8mb4 vérifié (accents corrects sur tout le front) ✔

## Points d'attention

1. Le compte administrateur historique (`admin@…`) a été migré avec son mot de passe d'origine, documenté en clair dans l'ancien README → **à réinitialiser immédiatement**.
2. Les données étant essentiellement du jeu de test, une **re-migration finale** devra être rejouée si l'ancienne appli accumule de vraies données d'ici la mise en production (scripts prêts, mêmes commandes).
