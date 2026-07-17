# Prendre rendez-vous — tpl-prendre-rdv.php (refonte fidèle 16/07/2026)

**Modèles :** `views/pages/rdv_prendre.php` (369 l.) + `views/partials/creneaux_picker.php`
+ `RdvController` (anti-spam, API créneaux). L'ancienne version WordPress était un
formulaire simplifié (liste de radios) — remplacée par le port 1:1.

## Gabarit (thème)

- `templates/tpl-prendre-rdv.php` — breadcrumb (Tableau de bord › Mes devis › Prendre RDV),
  H1 « Prendre rendez-vous » + « Visite technique gratuite · ~1h30 sur place », avertissement
  anti-spam (≥3 demandes/7j au même artisan), sidebar « ARTISAN CHOISI » (initiales
  prénom+nom du gérant, raison sociale, note ★ si > 0, spécialité = 1ᵉʳ type de projets) +
  carte « PROJET LIÉ » si `?devis_id=` (demande du client), section 1 créneaux, section 2
  détails (adresse obligatoire, CP/ville, précisions, **photos max 5** avec aperçu JS,
  case d'engagement), bandeau récap vert (libellé « Vendredi 12 juin · 14:30 » au clic,
  bouton désactivé sans créneau), messages ok/erreur, note rassurante. JS d'origine adapté
  (admin-ajax au lieu de /api et /rdv/creer). Paramètre `?pro=` (`?artisan=` = query var WP réservé).
- `template-parts/creneaux-picker.php` — port du partial : nav « ‹ Semaine précédente /
  Semaine suivante › » (précédente désactivée sur la semaine courante), libellé
  « Semaine du 13 juill. au 19 juill. », grille 4 jours (lun→jeu), jour courant surligné,
  « Aucun créneau » si vide. CSS : `assets/css/rdv.css` (copie identique de l'original).

## Plugin info-devis-core (includes/rdv.php — ajouts)

- `idc_rdv_slots_semaine($fiche_id, $lundi)` — créneaux d'une semaine donnée (mêmes règles :
  planning hebdo, indispos, occupés, délai de prévenance, plafond/jour).
- `idc_rdv_demandes_recentes($client, $fiche, 7)` — compteur anti-spam.
- AJAX `idc_creneaux` (GET fiche+week, public comme l'original) → `{success, slots}` pour la
  navigation de semaine sans rechargement.
- AJAX `idc_rdv_creer` (connecté + nonce) : validation (fiche publiée, format créneau,
  consentement, adresse obligatoire), **anti-spam 3/7j (429)**, recalcul serveur du créneau
  sur SA semaine (409 si pris), création CPT rdv + metas `_idc_adresse/_idc_code_postal/
  _idc_ville/_idc_devis_id`, description → post_content, **photos** via media_handle_upload
  (max 5, 10 Mo, JPG/PNG/WEBP, 1ʳᵉ = image à la une du RDV), emails artisan + client,
  réponse `{success, redirect}`. L'ancien handler admin_post `idc_rdv_create` est conservé.

## Divers

- Barre d'admin WP masquée sur le front pour les rôles client/artisan (functions.php) —
  l'original n'en a pas.

## Tests (16/07/2026)

Page connectée (Claire) : tous les blocs présents, 0 erreur PHP, capture
`docs/captures/wordpress/prendre-rdv.png` conforme au modèle (semaine 13→19 juill.,
« Aucun créneau » sur les jours passés). API `idc_creneaux` : créneaux réels semaine
suivante. E2E : réservation 21/07 09:30 → succès + redirect /mes-rdv/?rdv=ok ; même
créneau → refus 409 ; créneau retiré de l'API ; metas adresse/CP/ville stockées ;
RDV de test supprimé. Sauvegardes : `backups/20260716-categorie-avant/{rdv.php.bak,
tpl-prendre-rdv.php.bak}`.

⚠ À surveiller : les options `home`/`siteurl` sont passées à `http://info-devis.fr`
puis revenues à `http://info-devis.local` pendant la session du 16/07 — si le passage
en production est en préparation, faire le changement de domaine au déploiement
(search-replace complet), pas en local.
