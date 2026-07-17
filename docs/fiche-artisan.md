# Fiche publique artisan — single-artisan.php

**Modèle :** `views/pages/artisan_profil.php` + partials `tabs_artisan.php` / `card_projet.php` / `artisan_badge.php` (site original) — reproduit 1:1 le 16/07/2026. Sauvegarde préalable : `backups/20260716-1044-avant-fiche-artisan/`.

## Structure

1. **Bannière** pleine largeur (`.ad-cover` — ratio 5/3 mobile, 8/3 desktop, min/max-height, object-cover) — meta `_idc_cover_url`, fallback metier.png, `onerror` géré.
2. **Identité** : avatar rond superposé (image à la une, fallback avatar-default), nom serif, « Gérant : … » (si ≠ raison sociale), spécialité italique (1ᵉʳ type de projet), **badge de niveau** (Référencé/Vérifié/Vérifié Pro selon plan), **pill Silver/Gold** (plans payants uniquement), lien « ? » vers /nos-niveaux-de-confiance, réseaux (LinkedIn/Instagram) en boutons ronds.
3. **Bloc infos** (grille 4/8) : Expérience, Réalisations, Localisation avec icônes Material sur fond #f8f8f8 ; « Types de projets » en tags ; bio (contenu du post) ; **CTA selon l'état** : anonyme → « Se connecter pour demander » (avec redirect_to), client connecté → « Prendre rendez-vous » (/prendre-rdv/?pro=) + « Demander un devis » (métier pré-sélectionné), **propriétaire → « Modifier ma fiche »** (jamais de demande vers soi-même) ; bouton favori.
4. **Onglets** `.tabs-artisan` : Réalisations (compteur) / À propos / Avis (compteur) — navigation par `?tab=`, **fonctionne sans JavaScript**.

## Onglets

- **Réalisations** : grille 1/2/3 colonnes, cartes `.projet-card` (image, titre, stats surface `_idc_surface_m2` / budget `_idc_budget` / durée `_idc_duree` affichées seulement si renseignées, résumé, « Voir la réalisation ») ; état vide propre.
- **À propos** : sections conditionnelles (rien de vide affiché) — Présentation (post_content), Notre mission (`_idc_mission`), Domaines d'expertise (`_idc_expertises_detail` + termes métiers), **« Pourquoi nous choisir ? »** (fond vert pâle, bord gauche vert, liste à coches si multi-lignes — `_idc_pourquoi_nous_choisir`), Contact (respecte `_idc_show_contact` ; email public = `_idc_email_public` uniquement — jamais l'email de compte ; téléphone, ville·CP, LinkedIn en cartes fines) ; fallback « pas encore complété ».
- **Avis** : **synthèse** (note moyenne serif, étoiles, nombre, **répartition 5→1 avec barres**, depuis `_idc_rating_avg/count/distribution`), dépôt réservé aux **clients connectés non-propriétaires** (étoiles cliquables + handler `idc_submit_avis` existant : nonce, anti-doublon, modération), invitation à se connecter sinon ; liste : prénom + initiale (confidentialité), badge « Vérifié », note, date, **réponse de l'artisan** ; états vides.

## Tests passés

3 onglets HTTP 200 sans erreur PHP · bannière/badges/synthèse présents · CTA anonyme ✓ · CTA client (RDV + devis) ✓ · formulaire d'avis client ✓ · propriétaire sans bouton de demande ✓ · avis affiché « Claire C. » ✓.

## Restes connus

Modification/suppression d'un avis par son auteur directement sur la fiche (dans l'original) — disponible via l'espace client ; favoris = bouton présent, persistance à brancher (comme l'original : API favoris non portée) ; champs admin détaillés (mission/expertises/pourquoi/email public/réseaux) éditables via meta — à exposer dans l'éditeur InfoDevis Admin > Artisans (champ par champ) lors d'une prochaine session.
