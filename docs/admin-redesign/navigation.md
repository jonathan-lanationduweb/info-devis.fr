# InfoDevis Admin — Navigation

## Structure du menu latéral

Routes hash (`#/…`) de la SPA. Badges dynamiques entre parenthèses.

```
🏠 Tableau de bord                    #/dashboard

SITE
   Gestion du site                    #/site            (page en cartes)
   Accueil                            #/site/accueil    (éditeur par sections)
   Métiers                            #/metiers         (cartes réordonnables)
   Pages                              #/pages
   Menus                              lien wp-admin nav-menus (échappatoire assumée)

CONTENUS
   Articles                           #/articles
   Guides                             #/guides
   Catégories                         #/categories      (= métiers, filtre blog)
   FAQ                                #/faq

ARTISANS
   Artisans                           #/artisans
   Vérifications (n en attente)       #/artisans/verifications
   Réalisations                       #/realisations
   Avis (n à modérer)                 #/avis

CLIENTS
   Clients                            #/clients
   Demandes                           #/demandes
   Rendez-vous                        #/rdv

DEVIS
   Toutes les demandes                #/demandes        (vue Kanban + vue liste)
   En attente                         #/demandes?statut=pending
   Acceptées                          #/demandes?statut=accepted
   Terminées                          #/demandes?statut=completed

PAIEMENTS
   Abonnements                        #/paiements
   Stripe                             #/paiements/stripe
   Factures                           #/paiements/factures

SEO
   Vue d'ensemble                     #/seo
   Redirections                       #/seo/redirections
   Sitemap                            #/seo/sitemap

PARAMÈTRES
   Réglages                           #/parametres
   Emails                             #/parametres/emails
   SMTP & API                         #/parametres/api
   Sauvegardes                        #/parametres/sauvegardes

────────────  (bas de sidebar, repliable, administrateurs uniquement)

▸ ADMINISTRATION WORDPRESS
   Médias                             wp-admin/upload.php
   Apparence                          wp-admin/themes.php
   Extensions                         wp-admin/plugins.php
   Utilisateurs                       wp-admin/users.php
   Outils                             wp-admin/tools.php
   Réglages                           wp-admin/options-general.php
   Santé du site                      wp-admin/site-health.php
   Mises à jour (badge n)             wp-admin/update-core.php
   Yoast SEO                          wp-admin/admin.php?page=wpseo_dashboard
   WP Mail SMTP                       wp-admin/admin.php?page=wp-mail-smtp
```

Les menus natifs de WordPress ne sont **jamais supprimés** : ils restent indispensables
pour la maintenance (extensions, mises à jour, réglages avancés). Le groupe repliable
ci-dessus y donne accès directement depuis l'app ; l'état ouvert/replié est mémorisé
(`localStorage`). WordPress applique ses propres capacités sur chaque écran natif.

## Barre supérieure

- **Recherche globale** (⌘K / Ctrl+K) : overlay plein écran, recherche instantanée
  (debounce 200 ms) sur pages, articles, guides, artisans, clients, demandes, RDV,
  utilisateurs. Résultats groupés par type, navigation clavier (↑ ↓ Entrée, Échap).
- **Bouton « + Nouveau »** : menu (article, guide, artisan, demande, FAQ).
- **Voir le site** (nouvel onglet).
- **Avatar utilisateur** : profil, admin WordPress classique (admins), déconnexion.

## Écrans remplaçant wp-admin

| Écran WordPress natif | Remplacé par |
|---|---|
| Tableau de bord wp-admin | `#/dashboard` |
| Toutes les pages (`edit.php?post_type=page`) | `#/pages` + `#/site` |
| Tous les articles (`edit.php`) | `#/articles` |
| Guides | `#/guides` |
| Artisans | `#/artisans` |
| Avis clients | `#/avis` |
| Demandes de devis | `#/demandes` |
| Réalisations | `#/realisations` |
| Rendez-vous | `#/rdv` |
| Catégories travaux (`edit-tags.php`) | `#/metiers` |
| Info Devis → Emails | `#/parametres/emails` |
| Info Devis → État du système | `#/parametres` (santé) + `#/dashboard` |

## Redirections wp-admin

Gérées par `wp-admin-integration.php` :

- **Connexion** : après login, tout utilisateur `idc_manage` → `/infodevis-admin/`.
- **`/wp-admin/` (index.php)** : utilisateur `idc_manage` → `/infodevis-admin/#/dashboard`.
- **Listes natives remplacées** (`edit.php` des types ci-dessus) → écran InfoDevis
  correspondant.
- **Échappatoire** : `?classic=1` pose un cookie de session `ida_classic` qui désactive
  les redirections (réservé aux administrateurs).
- **Autorisés sans redirection** : `admin-ajax.php`, `admin-post.php`, `async-upload.php`,
  médias, `post.php`/`post-new.php` (éditeur avancé Gutenberg accessible depuis les liens
  « Éditeur avancé » de l'app), profil.
- **Admin bar** : masquée sur le front pour les gestionnaires ; conservée pour les
  administrateurs en mode classique.

## Règles d'affichage

- Menu latéral repliable (icônes seules) ; état persisté en `localStorage`.
- Mobile/tablette : sidebar en tiroir (overlay), ouverture par bouton hamburger.
- Section active mise en évidence (fond vert pâle + barre gauche verte).
- Chaque écran a un fil d'ariane court + titre + actions à droite (pattern Shopify).
- Toutes les listes conservent leurs filtres dans l'URL (deep-linking).
