# Tests responsive — 15 juillet 2026

## Approche

Mobile-first dans `assets/css/responsive.css` (chargé en dernier). Le desktop ≥ 1440px reste identique aux captures du site original. Points de rupture : **mobile ≤ 767px**, **tablette 768-1023px**, **laptop 1024-1439px**, **grand écran ≥ 1440px**. Tailwind (préfixes `md:`/`lg:` du HTML original) gère déjà une partie des empilements ; `responsive.css` corrige ce que l'original ne couvrait pas.

## Adaptations implémentées

| Exigence | Implémentation |
|---|---|
| Menu hamburger | Mobile **et tablette** (la navbar desktop — 6 liens + 4 actions — ne tient pas sous 1024px : constaté par mesure, débordement réel à 768/820px → hamburger étendu à la tablette) |
| Grilles 3-4 col → 2 → 1 | Tablette : override `md:grid-cols-3`, `lg:grid-cols-3/4` → 2 colonnes ; bento accueil : grande carte pleine largeur + cartes 2/ligne ; mobile : 1 colonne (Tailwind d'origine) |
| Héro empilé | Barre de recherche en colonne, séparateurs verticaux → horizontaux, bouton pleine largeur ; hauteur héro 870→640px |
| Titres `clamp()` | h1 `clamp(1.9rem, 8.5vw, 3rem)`, h2 `clamp(1.5rem, 6.5vw, 2.25rem)` sur mobile ; `text-8xl` tempéré sur tablette |
| Zéro débordement | `overflow-x: clip` + `img/svg max-width:100%` + hauteurs bento fixes → `aspect-ratio: 4/3` sur mobile — **vérifié par mesure DOM** (voir matrice) |
| Images | `object-fit: cover` (déjà dans le HTML original), ratios flexibles sur mobile |
| Tactile | Cibles ≥ 44px (`pointer: coarse`), champs ≥ 46px, `font-size: 16px` (anti-zoom iOS), boutons pleine largeur mobile |
| Marges latérales | 20px sur mobile (au lieu de 32px), contenus jamais collés aux bords |
| Tableaux | `.idc-dash-table`, `.guide-table` → zones défilables horizontales sur mobile/tablette |
| Filtres pros | `.idv-filter-pills` → ligne défilable horizontale sur mobile |
| Formulaire devis | Champs empilés, tailles tactiles, validation navigateur conservée |
| Footer | 4 colonnes → 1 colonne (Tailwind), espacements resserrés sur mobile |
| Accessibilité | `prefers-reduced-motion: reduce` respecté (animations désactivées) |

## Matrice de vérification (mesure DOM réelle, pas visuelle)

Méthode : harnais same-origin temporaire (iframe à largeur exacte + `scrollWidth` + détection d'éléments dépassant le viewport hors zones défilantes volontaires), exécuté en Chrome headless. **Note :** les captures d'écran Chrome headless à petites largeurs présentent un artefact de zoom (viewport visuel) — la mesure DOM fait foi.

Pages testées : accueil, catégories, professionnels, catégorie plomberie, devis.
Largeurs : 320, 375, 390, 430, 768, 820, 1024, 1280, 1440, 1920 px.

**Résultat final : 50/50 OK — aucun scroll horizontal, aucun élément débordant.**

## Anomalies trouvées et corrigées

1. **Navbar 768-820px** : les clusters desktop débordaient du viewport (3 éléments hors écran à 768px, 2 à 820px, sur toutes les pages — défaut hérité de l'original). → Hamburger étendu à la tablette (`responsive.css`, media 768-1023). Re-testé : OK.
2. **Faux positifs pastilles filtres** (professionnels ≤ 430px) : les pastilles dépassent à droite **dans leur conteneur défilable** — comportement voulu (« ligne scrollable »), exclu de la détection.
3. **Hauteurs bento fixes** (400-500px inline) : disproportionnées sur mobile → `aspect-ratio: 4/3`.

## Reste à tester lors des étapes suivantes

- Espaces client/artisan (étape 10 du plan) : tableaux → cartes/défilement, sidebar mobile.
- Fenêtres modales (aucune portée pour l'instant).
- Vérification sur appareils réels (les mesures headless valident la géométrie, pas le toucher).

---

## Vérification responsive — 17 juillet 2026 (après ajout des nouveaux modules)

Mesure du **débordement horizontal réel** (scrollWidth vs viewport) via iframe même-origine
aux largeurs 320 / 390 / 430 / 768 / 1440 px. Méthode fiable (la capture headless < 400px
présente un artefact de zoom trompeur — on se fie à la mesure DOM).

**Pages publiques** (accueil, professionnels, catégories [standard + maçonnerie +
aménagements-extérieurs], blog, article, fiche artisan, réalisation, devis, contact,
guides, tarifs-pro, connexion, inscription, niveaux de confiance) : **aucun débordement**.

**Espace client** (tableau de bord, mes projets, messagerie, signature, avis, profil,
dispos, mes RDV) : **aucun débordement** à 320/390.

**Espace artisan** (tableau de bord, opportunités, interventions, messagerie, réalisations,
disponibilités, statistiques, profil, avis, documents, abonnement) : **aucun débordement**
à 320/390/768.

### Correctif appliqué
- **Tarifs Pro** — la grille « avantages » (`#idv-tarifs-grid`) débordait à 533 px sur mobile :
  le Tailwind CDN calculait une largeur intrinsèque anormale (symptôme du CDN, cf. audit P1).
  Corrigé par un `<style>` ciblé (ID) : sur ≤ 767 px, `display:block` + `width:100%` sur la
  grille et ses cartes → empilement propre. Desktop inchangé (grille 4 colonnes ≥ 768 px).
  Vérifié : 0 débordement à 320/390/430/768/1440.

Conclusion : **responsive opérationnel sur l'ensemble des pages**, anciennes et nouvelles.
