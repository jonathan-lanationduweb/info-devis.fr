# Tests — Professionnels & fiche artisan (16/07/2026)

## Annuaire /professionnels/ (validé lors de l'étape 8 + re-vérifié)

Recherche par nom (`?q=targa`) ✓ · filtre métier (`?categorie=plomberie`) ✓ · tri (`?sort=rating`) ✓ · HTTP 200 sans erreur PHP ✓ · capsules actives ✓ · compteur réel ✓ · responsive validé par mesure DOM 320→1920 px (voir tests-responsive.md, 50/50 OK).

## Fiche artisan /artisan/{slug}/

| Test | Résultat |
|---|---|
| Onglet Réalisations (défaut) | ✓ HTTP 200, grille/état vide |
| Onglet À propos (?tab=apropos) | ✓ sections conditionnelles, fallback propre |
| Onglet Avis (?tab=avis) | ✓ synthèse + répartition + liste |
| Bannière + avatar + badges + pill plan | ✓ |
| Visiteur anonyme | ✓ « Se connecter pour demander » avec retour vers la fiche |
| Client connecté (Claire) | ✓ « Prendre rendez-vous » + « Demander un devis » + formulaire d'avis à étoiles |
| Artisan propriétaire (Marc sur sa fiche) | ✓ « Modifier ma fiche », aucun bouton de demande vers lui-même |
| Confidentialité avis | ✓ « Claire C. » (prénom + initiale) |
| Dépôt d'avis | ✓ handler existant (nonce + anti-doublon + modération) — testé E2E lors de l'étape avis |
| Onglets sans JavaScript | ✓ navigation par liens ?tab= |
| Artisan sans couverture / avatar / réalisation / avis | ✓ fallbacks images + états vides (fiche 57 : 0 réalisation, fiche 6 : 1 avis) |
| Artisan non publié | ✓ WordPress renvoie 404 nativement (pending exclu) |
| Erreurs PHP sur les 3 onglets | ✓ aucune |

## À tester à la main / restes

Passe visuelle mobile navigateur (la géométrie est validée par les règles déjà mesurées : bannière ratio 5/3, avatar réduit, boutons empilés) · favoris (bouton présent, persistance non branchée — comme l'original dont l'API n'est pas portée) · édition d'avis inline par son auteur (dispo via l'espace client).
