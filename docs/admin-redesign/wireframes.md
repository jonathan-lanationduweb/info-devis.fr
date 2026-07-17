# InfoDevis Admin — Wireframes

Wireframes texte des écrans principaux. Largeur de référence : 1280 px.
Sidebar 248 px (repliée : 64 px) · topbar 56 px · contenu max 1120 px centré.

## Shell général

```
┌──────────┬──────────────────────────────────────────────────────┐
│          │  ☰   🔍 Rechercher (Ctrl K)        [+ Nouveau ▾] 🌐 ◉ │
│ InfoDevis├──────────────────────────────────────────────────────┤
│          │                                                      │
│ 🏠 Tableau│              (vue active, fond #f6f7f9)             │
│   de bord │                                                      │
│ ── SITE ──│                                                      │
│ Gestion   │                                                      │
│ Accueil   │                                                      │
│ Métiers   │                                                      │
│ …         │                                                      │
│           │                                                      │
│ (sidebar  │                                                      │
│ bleu nuit)│                                                      │
└──────────┴──────────────────────────────────────────────────────┘
```

## #/dashboard — Tableau de bord

```
Bonjour Jonathan 👋                                    mardi 15 juillet 2026

┌ Demandes ─┐ ┌ Artisans ─┐ ┌ Articles ─┐ ┌ Guides ──┐ ┌ Avis ────┐ ┌ RDV ─────┐
│ 4         │ │ 4         │ │ 8         │ │ 18       │ │ 1        │ │ 0        │
│ 2 en att. │ │ 1 à valider│ │ publiés  │ │ publiés  │ │ 0 à modér│ │ à venir  │
└───────────┘ └───────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────┘

┌ Demandes de devis (6 mois) ────────────┐ ┌ À traiter ──────────────────┐
│ ▂▄▆█▅▃  (graphique barres SVG)         │ │ ⚠ 2 demandes en attente     │
│                                        │ │ ⚠ 1 artisan à vérifier      │
└────────────────────────────────────────┘ │ ✓ 0 avis à modérer          │
┌ Activité récente ──────────────────────┐ └─────────────────────────────┘
│ ● Demande DV260715-XXXX reçue · il y a…│ ┌ État du système ────────────┐
│ ● Artisan « Dupont » inscrit · …       │ │ ✓ SMTP  ✓ Stripe  ✓ Pages   │
│ ● Avis 5★ publié · …                   │ └─────────────────────────────┘
└────────────────────────────────────────┘

Accès rapides
[+ Artisan] [+ Article] [+ Guide] [+ FAQ] [+ Demande] [📊 Statistiques] [+ Offre]
```

## #/site — Gestion du site

```
Gestion du site                                            [Voir le site ↗]

┌ Accueil ──────────┐ ┌ Métiers ──────────┐ ┌ Professionnels ───┐
│ Page d'accueil    │ │ 18 métiers        │ │ Annuaire artisans │
│ [Modifier][Aperçu]│ │ [Modifier][Aperçu]│ │ [Modifier][Aperçu]│
└───────────────────┘ └───────────────────┘ └───────────────────┘
┌ Blog ─────────────┐ ┌ Guides ───────────┐ ┌ Tarifs Pro ───────┐
│ 8 articles        │ │ 18 guides         │ │ Offres artisans   │
│ [Voir] [+ Ajouter]│ │ [Voir] [+ Ajouter]│ │ [Modifier][Aperçu]│
└───────────────────┘ └───────────────────┘ └───────────────────┘
┌ Contact ──────────┐ ┌ Mentions légales ─┐ ┌ Confidentialité ──┐ ┌ Plan du site ┐
```

## #/site/accueil — Éditeur de la page d'accueil

```
Accueil                                   [Aperçu ↗]  [Enregistrer]

┌ ⠿ Hero ─────────────────────────────── 👁 visible ── [Modifier >] ┐
│ « Trouvez le bon artisan… » · image, recherche                    │
├ ⠿ Expertises ────────────────────────── 👁 visible ── [Modifier >] ┤
├ ⠿ Comment ça marche ─────────────────── 👁 visible ── [Modifier >] ┤
├ ⠿ Professionnels ────────────────────── 👁 visible ── [Modifier >] ┤
├ ⠿ Citation ──────────────────────────── 👁 visible ── [Modifier >] ┤
├ ⠿ CTA ───────────────────────────────── 👁 visible ── [Modifier >] ┤
└ Footer (coordonnées, réseaux) ────────────────────── [Modifier >] ┘

Clic « Modifier » → Drawer droit avec le formulaire de la section :
┌─ Hero ───────────────── ✕ ┐
│ Titre        [___________] │
│ Sous-titre   [___________] │
│ Image desktop [🖼 choisir] │
│ Image mobile  [🖼 choisir] │
│ Overlay       [▓ 40 %]     │
│ Bouton 1  txt [__] url [__]│
│ Hauteur      [◉ auto ○ 100vh]
│          [Annuler][Enregistrer]
└────────────────────────────┘
```

## #/metiers — Métiers

```
Métiers (18)                                  [+ Ajouter un métier]
🔍 Rechercher…

⠿ ┌🖼┐ Plomberie      12 artisans · 3 guides   👁  [SEO ✓] [Modifier] [Aperçu ↗]
⠿ ┌🖼┐ Électricité    …                        👁  [SEO ✗] [Modifier] [Aperçu ↗]
(glisser-déposer ⠿ pour réordonner — ordre affiché sur le front)
```

## #/articles — Blog (idem #/guides)

```
Articles (8)                [Filtre: Catégorie ▾] [Statut ▾]   [+ Nouvel article]
🔍 Recherche instantanée…

┌──┬────────────────────────────┬────────────┬─────────┬──────┬─────────┬───┐
│🖼│ Titre                      │ Catégorie  │ Lecture │ SEO  │ Statut  │ ⋯ │
│🖼│ Rénover sa salle de bain   │ Plomberie  │ 4 min   │ ✓    │ Publié  │ ⋯ │
│🖼│ …                          │            │         │      │Brouillon│ ⋯ │
└──┴────────────────────────────┴────────────┴─────────┴──────┴─────────┴───┘
⋯ = Modifier · Aperçu · Dupliquer · Corbeille          ← 1 2 →
```

## #/artisans — Professionnels

```
Artisans (4)   [Statut ▾][Abonnement ▾][Badge ▾]  Tri: Récents ▾   [+ Ajouter]
🔍 Nom, ville, SIRET…

┌───┬──────────────┬──────────┬──────┬─────────┬──────────┬─────────┬───┐
│ ◉ │ Nom          │ Ville    │ Anc. │ Badge   │ Abonnement│ Statut │ ⋯ │
│ ◉ │ Dupont Élec  │ Paris    │ 8 ans│ Vérifié │ Gold      │ Validé │ ⋯ │
│ ◉ │ Martin Plomb.│ Lyon     │ 3 ans│ Référencé│ Gratuit  │ ⏳ Att. │ ⋯ │
└───┴──────────────┴──────────┴──────┴─────────┴──────────┴─────────┴───┘
⋯ = Modifier · Voir la fiche ↗ · Vérifier · Suspendre
```

## #/demandes — Kanban

```
Demandes de devis            [Vue: ▣ Kanban | ☰ Liste]        [+ Demande]

┌ Nouvelle (2) ──┐ ┌ En cours (1) ─┐ ┌ Acceptée (0) ─┐ ┌ Terminée (1) ─┐
│ ┌────────────┐ │ │ ┌───────────┐ │ │               │ │ ┌───────────┐ │
│ │DV260712-AB │ │ │ │DV260701-CD│ │ │  (vide)       │ │ │DV260620-EF│ │
│ │Plomberie   │ │ │ │Toiture    │ │ │               │ │ │Peinture   │ │
│ │Paris · 🔴 urg│ │ │Lyon       │ │ │               │ │ │Marseille  │ │
│ └────────────┘ │ │ └───────────┘ │ │               │ │ └───────────┘ │
└────────────────┘ └───────────────┘ └───────────────┘ └───────────────┘
(cartes déplaçables ; la dépose met à jour _idc_status + toast)
```

## #/rdv — Rendez-vous

```
Rendez-vous            [Mois ▾ | Semaine | Liste]   ‹ Juillet 2026 ›  [+ RDV]

Lu   Ma   Me   Je   Ve   Sa   Di
      1    2    3    4    5    6
 7    8   [9]  10   11   12   13
           └ 14h Dupont × Client A (confirmé, pastille verte)
```

## #/seo — Vue d'ensemble SEO

```
SEO                                                      [Ouvrir Yoast ↗]

┌ Score global ┐ ┌ Indexables ┐ ┌ Sans meta desc ┐ ┌ Redirections ┐
│ 82 %         │ │ 61 pages   │ │ 7 contenus     │ │ 12 actives   │
└──────────────┘ └────────────┘ └────────────────┘ └──────────────┘

Contenus à corriger (title/description manquants)
┌ Type    ┬ Titre                  ┬ Problème            ┬ Action    ┐
│ Guide   │ Prix pose carrelage    │ Meta desc manquante │ [Corriger]│
```

## #/parametres — Paramètres

```
Paramètres
[Société] [Coordonnées] [Réseaux sociaux] [Intégrations] [Santé]

Société          : Nom, logo, couleurs (primaire/nuit)
Coordonnées      : Email, téléphone, adresse (options idv_contact_*)
Réseaux sociaux  : Facebook, Instagram, LinkedIn, X
Intégrations     : Stripe (état ✓/✗, mode TEST/LIVE), SMTP (état), Google, clés = wp-config
Santé            : checks de l'ancien « État du système » réutilisés
```

## Recherche globale (Ctrl+K)

```
┌──────────────────────────────────────────────┐
│ 🔍 dupont…                                   │
├──────────────────────────────────────────────┤
│ ARTISANS                                     │
│   ◉ Dupont Électricité — Paris               │
│ CLIENTS                                      │
│   ◉ Jean Dupont — jean@…                     │
│ DEMANDES                                     │
│   📄 DV260712-AB01 — Plomberie, Paris        │
│ (↑↓ naviguer · Entrée ouvrir · Échap fermer) │
└──────────────────────────────────────────────┘
```

## Responsive

- **≥1024 px** : sidebar fixe.
- **768–1023 px** : sidebar repliée en icônes ; grilles de cartes 2 colonnes.
- **<768 px** : sidebar en tiroir (hamburger), stat-cards en 2 colonnes, tables →
  cartes empilées (colonnes secondaires masquées), Kanban en colonnes défilables
  horizontalement, drawer plein écran.
