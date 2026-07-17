# RGPD

## Données personnelles traitées

| Donnée | Où | Finalité | Base légale |
|---|---|---|---|
| Nom, email, téléphone, ville/CP des demandeurs | CPT `demande_devis` (meta `_idc_contact_*`) | Mise en relation avec les artisans | Consentement (case obligatoire, horodatée dans `_idc_consent_at`) |
| Comptes clients/artisans | `wp_users` + user meta | Espace membre, suivi | Exécution du service |
| Consentement inscription artisan | user meta `_idc_consent_at` | Preuve | Consentement |
| Avis (nom affiché, commentaire) | CPT `avis` | Information des consommateurs | Intérêt légitime + modération |
| Journal d'emails | option `idc_email_log` (50 entrées max, sans contenu) | Diagnostic | Intérêt légitime |

## Dispositifs en place

- **Consentement explicite** requis (non pré-coché) sur le formulaire de devis et l'inscription artisan, avec horodatage stocké.
- **Page Politique de confidentialité** publiée et déclarée à WordPress (Réglages → Confidentialité).
- **Export / effacement** : outils natifs WordPress (Outils → Exporter/Effacer les données personnelles) couvrent les comptes ; les demandes de devis liées se retrouvent par email de contact.
- **Minimisation** : seuls les champs nécessaires sont collectés ; le journal d'emails ne stocke pas le contenu.
- **Pages privées non indexées** (noindex espace membre, inscription, recherche).
- Cookies : uniquement techniques (session de connexion WordPress) — pas de bannière nécessaire en l'état ; à revoir si un outil de mesure d'audience est ajouté.

## Reste à faire

1. Compléter la politique de confidentialité (durées définitives, DPO éventuel, hébergeur).
2. Brancher les données métier (demandes, avis) sur les exporteurs/effaceurs natifs (`wp_privacy_personal_data_exporters`) pour un export 100 % automatique.
3. Procédure interne de traitement des demandes RGPD (délai 30 jours).
4. Registre des traitements.
5. Politique de purge : anonymiser les demandes de devis > 3 ans (tâche WP-Cron à créer).
