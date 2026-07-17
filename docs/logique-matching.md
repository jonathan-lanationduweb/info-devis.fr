# Logique de matching artisans / demandes

## Historique (ancienne appli)

`services/Matching` croisait : catégories (`artisan_categories`), zone géographique (`artisan_zones` + rayon `radius_km` en km avec coordonnées lat/long des demandes), disponibilité, et un `matching_score` (taux de réponse, leads acceptés/refusés, temps de réponse). Les tables `zones` et `artisan_zones` étaient **vides** (fonctionnalité non alimentée en pratique).

## V1 WordPress (implémentée — `includes/matching.php`)

`idc_match_artisans( $demande_id )` retourne les fiches artisans telles que :

1. **Métier** : au moins une catégorie `metier` en commun avec la demande ;
2. **Zone** : même **département** (2 premiers chiffres du code postal). Un artisan **sans code postal** est inclus (profil incomplet ne doit pas être exclu silencieusement) ; une demande sans code postal matche tous les artisans du métier.

`idc_get_demandes_for_artisan( $user_id )` est la réciproque (demandes `pending`/`sent` correspondant aux métiers + département de la fiche liée au compte).

### Points d'usage

- Metabox « Artisans correspondants » sur chaque demande (admin) — le calcul est **explicable** d'un coup d'œil.
- Envoi d'email aux artisans matchés à chaque nouvelle demande (modèle `devis_artisan`, désactivable).
- Tableau « Projets correspondants » dans l'espace artisan.

### Pourquoi ce choix

- Les données géographiques historiques (zones, lat/long) étaient vides : rien à perdre.
- Département = simple, robuste, sans dépendance externe, testable.
- La comparaison historique reste possible : l'ancienne logique est archivée dans la sauvegarde de l'appli.

## V2 (à faire)

- Rayon kilométrique réel : géocodage du CP (API adresse.data.gouv.fr, gratuite) + distance haversine ≤ `_idc_radius_km`.
- Pondération : plan d'abonnement (Gold prioritaire), note moyenne, taux de réponse, plafond de demandes/jour.
- Réglage administrable de la priorité métier (page Info Devis → Réglages).
- Journal du matching par demande (qui a été notifié, quand).
