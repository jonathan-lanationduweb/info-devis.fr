# InfoDevis Admin — Roadmap

## Phase 0 — Préparation ✅

- [x] Analyse complète du projet (thème, plugin `info-devis-core`, CPT, taxonomies,
      options, endpoints, base de données)
- [x] Sauvegarde base (`backups/2026-07-15-avant-admin-redesign/db-wordpress.sql`)
- [x] Sauvegarde fichiers (`fichiers-projet.tar.gz`) + instructions `RESTAURATION.md`
- [x] Documentation de conception (`docs/admin-redesign/`)

## Phase 1 — Socle (v1)

- [x] Plugin `infodevis-admin` : bootstrap, point d'entrée `/infodevis-admin/`,
      contrôle d'accès `idc_manage`
- [x] Design system CSS (tokens, composants, layouts, responsive, dark-mode-ready)
- [x] Shell SPA : sidebar, topbar, routeur hash, toasts, icônes Lucide inline
- [x] API REST `idc/v1/admin/*` : bootstrap, listes génériques, CRUD entités,
      dashboard, recherche globale, réglages
- [x] Intégration wp-admin : redirection login, remplacement des listes natives,
      admin bar, échappatoire `?classic=1`

## Phase 2 — Écrans (v1)

- [x] Tableau de bord : salutation, KPI, graphique demandes, à traiter, activité
      récente, état système, accès rapides
- [x] Gestion du site (cartes) + éditeur page d'accueil par sections (drawers)
- [x] Métiers : cartes, drag & drop de l'ordre, SEO, visibilité
- [x] Articles & Guides : listes modernes (recherche instantanée, filtres,
      dupliquer/corbeille), écran d'édition (RichText léger + panneau SEO Yoast +
      lien Gutenberg)
- [x] Pages : liste + édition
- [x] FAQ (nouveau CPT léger `faq` géré par la nouvelle admin)
- [x] Artisans : table filtrable, fiche d'édition complète (SIRET, plan, badge,
      vérification), actions Vérifier / Suspendre ; file « Vérifications »
- [x] Réalisations & Avis (modération : approuver/refuser)
- [x] Clients : liste des comptes + détail (demandes liées)
- [x] Demandes : Kanban drag & drop + vue liste + fiche détail (matching artisans)
- [x] Rendez-vous : calendrier mois/semaine + liste + création/édition
- [x] Paiements : abonnements par artisan, état Stripe, événements récents
- [x] SEO : synthèse (scores, metas manquantes), liens Yoast
- [x] Paramètres : société/coordonnées/réseaux (options), intégrations (états),
      santé système, emails (9 modèles + journal), sauvegardes
- [x] Recherche globale Ctrl+K
- [x] Page d'accueil du thème rendue administrable (options `idv_home_*`,
      défauts = contenu actuel — aucun changement visuel tant qu'on ne modifie rien)

## Phase 3 — Améliorations (v1.x)

- [ ] Mode sombre (tokens prêts, ajouter le sélecteur + palette dark)
- [ ] Notifications temps réel (polling léger sur `#/dashboard` → pastilles sidebar)
- [ ] Redirections SEO gérées en base (table dédiée ou option) + import
      `docs/plan-redirections.csv`
- [ ] Statistiques enrichies (visites via API externe, taux de conversion devis)
- [ ] Éditeur de menus intégré (remplace le lien vers wp-admin)
- [ ] Factures PDF (génération à partir des paiements Stripe)
- [ ] Gestion fine des réalisations par artisan (galerie, tri)
- [ ] Journal d'audit (qui a modifié quoi)

## Phase 4 — Plus tard (v2)

- [ ] Messagerie interne client ↔ artisan
- [ ] Application des couleurs choisies dans Paramètres au front (variables CSS)
- [ ] Rôles granulaires (éditeur contenu vs gestionnaire commercial)
- [ ] Export CSV des listes (artisans, demandes, clients)
- [ ] Webhooks sortants / API publique partenaires

## Garde-fous

- Ne rien supprimer : wp-admin, Gutenberg et `info-devis-core` restent intacts.
- Chaque évolution du thème garde les valeurs actuelles comme défauts.
- Toute écriture passe par l'API `idc/v1/admin/*` (capacité `idc_manage` + nonce).
