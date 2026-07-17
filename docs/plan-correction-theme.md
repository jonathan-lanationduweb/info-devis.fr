# Plan de correction — conversion fidèle du site original

**Principe :** le site original (`C:\wamp64\www\info-devis` + ZIP) est la référence absolue. Aucun redesign, aucune interprétation, aucun emoji. Le résultat doit donner l'impression que le site original a simplement été branché sur WordPress.

## Pourquoi la V1 ne correspondait pas

La V1 a été construite comme un **nouveau site** « dans l'esprit de » : j'ai repris uniquement 2 couleurs (`#207752`, `#f97316`) trouvées dans le CSS, puis j'ai assemblé une maquette générique sur Astra avec des sections inventées et des emojis en guise d'icônes — **sans jamais ouvrir les vues PHP ni les captures du site réel**. C'est une erreur de méthode : la conversion fidèle exige de partir du HTML/CSS/JS réellement rendu, page par page. Ce plan corrige cela.

## Stratégie technique

1. **Thème `info-devis` autonome** (plus enfant d'Astra) : `Template:` supprimé, structure complète (header.php, footer.php, front-page.php, page.php, single-*.php, archive-*.php, 404.php, template-parts/, assets/).
2. **Portage des assets originaux** dans le thème : `theme.css`, `portfolio.css`, `rdv.css` copiés tels quels dans `assets/css/` ; `main.js`/`portfolio.js` adaptés (URLs & nonces WordPress) dans `assets/js/` ; images de `assets/img/` copiées dans `assets/images/` + médiathèque.
3. **Tailwind** : le site original dépend du CDN Tailwind avec config inline. Deux options — (a) conserver le CDN à l'identique (fidélité immédiate, dépendance externe), (b) compiler un build statique des classes utilisées. **Décision : (a) dans un premier temps** (identique à l'original donc rendu identique), la compilation (b) sera faite avant production.
4. **Header/footer** : portage 1:1 de `includes/navbar.php` et `includes/footer.php` (logo « Info-Devis » serif, menu Métiers/Professionnels/Guides/Tarifs Pro/Blog/Contact, boutons Se connecter/S'inscrire/Espace Pro/Demander un devis, états connecté/déconnecté, cloche notifications, hamburger mobile). Menu branché sur `wp_nav_menu` avec un walker reproduisant les classes originales.
5. **Pages** : chaque vue PHP est convertie en template WordPress en conservant le HTML/classes à l'identique ; les données proviennent des CPT/meta migrés (plus de SQL direct). Les 18 vues SEO `categories/slugs/*.php` deviennent le contenu long des termes `metier`.
6. **Formulaire de devis** : reprise du parcours original (`home/devis.php` + `devis-contact.php` + `devis-confirmation.php` + `partials/devis-trust.php`) — la logique reste dans l'extension (handler existant conservé), seul le rendu change.
7. **Dashboards client/artisan** : templates dédiés (`page-espace-*.php`) reproduisant sidebar + cartes + tableaux + modales des vues `client/*` et `artisan/*`, alimentés par l'extension. Permissions vérifiées écran par écran.
8. **Icônes** : Font Awesome 7 + Material Symbols (comme l'original). Tous les emojis de la V1 sont supprimés.
9. **Textes** : extraits des vues originales, jamais réécrits.
10. **Astra** : conservé inactif comme retour arrière, désinstallé en fin de chantier.

## Ordre d'exécution (imposé §21)

| # | Étape | Validation |
|---|---|---|
| 1 | ✅ Sauvegarde (`backups/avant-correction-fidelite/` + doc restauration) | faite |
| 2 | ✅ Analyse original + ZIP | faite (audit-visuel-original.md) |
| 3 | ✅ Inventaire routes/vues/CSS/JS | fait |
| 4 | ✅ Captures de référence (16 pages publiques) | `docs/captures/original/` |
| 5 | Thème autonome activé, zéro trace Astra | capture + grep « astra » |
| 6 | Header + footer 1:1 | comparaison captures |
| 7 | Accueil 1:1 (héro recherche, bandeau, expertises, comment ça marche, sections restantes) | comparaison captures |
| 8 | Catégories (18 pages SEO), services, artisans, professionnels | comparaison captures |
| 9 | Formulaire devis complet + confirmation | test E2E + captures |
| 10 | Espaces client & artisan (tous les écrans) | tests par rôle + captures |
| 11 | Blog, guides, réalisations, avis, tarifs-pro, niveaux de confiance, auth stylée, 404 | comparaison captures |
| 12 | Emails, SEO, comptes, RDV, paiements | tests E2E |
| 13-14 | Comparaison systématique + corrections | `docs/captures/comparaisons/` |
| 15 | Rapport final | — |

## Captures dashboards (pages derrière connexion)

Les captures de référence des espaces privés seront prises à l'étape 10 avec une session authentifiée (compte de test sur l'application originale), même procédé Chrome headless avec cookie de session.

## Réversibilité

- WP actuel sauvegardé : `backups/avant-correction-fidelite/` (SQL + thème/extension/config + protection `.htaccess`).
- Restauration : réimporter le SQL, extraire le zip, réactiver l'ancien thème (`Apparence → Thèmes`).
- L'application originale et son ZIP ne sont jamais modifiés.
