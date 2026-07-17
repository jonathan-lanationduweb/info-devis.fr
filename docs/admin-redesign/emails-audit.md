# Audit — section Emails (avant refonte)

**Date :** 16 juillet 2026 · **Sauvegarde :** `C:\wamp64\backups\info-devis\<horodatage>-avant-emails\` (SQL + zip des deux plugins)

## Plugins email installés

| Plugin | Rôle | Décision |
|---|---|---|
| **WP Mail SMTP** (actif) | Transport SMTP via constantes `WPMS_*` dans wp-config (Mailtrap sandbox en local, Brevo prévu en prod) | **Réutilisé tel quel** — aucune seconde configuration, aucun secret dupliqué ; la carte SMTP lit les constantes sans afficher le mot de passe |
| Aucun plugin de journalisation (type Check & Log) | — | Table personnalisée à créer : `{prefix}idv_email_logs` |

## Journaux existants

- Option `idc_email_log` (info-devis-core) : 50 dernières entrées `{date, template, to, status}` — sans erreur détaillée, sans tentatives, sans filtre. **Conservée** (compatibilité écran Réglages) mais remplacée comme source par la nouvelle table.

## Emails envoyés par InfoDevis (déclencheurs réels)

| Clé modèle | Déclencheur |
|---|---|
| devis_admin / devis_client / devis_artisan | Nouvelle demande de devis (formulaire) |
| devis_client_accepte | Artisan accepte un lead |
| inscription_artisan_admin / inscription_artisan_bienvenue | Inscription artisan |
| avis_recu_admin / avis_publie_artisan | Dépôt / publication d'avis |
| rdv_nouveau_artisan / rdv_recap_client / rdv_confirme_client / rdv_annule | Cycle rendez-vous |
| abonnement_active / paiement_echec | Webhooks Stripe |
| contact_admin | Formulaire de contact (journalisé, corps non modélisé) |
| Emails natifs WordPress | réinitialisation mot de passe, notifications nouveau compte |

## Modèles existants

13 modèles dans `idc_email_templates_catalog()` (info-devis-core/includes/emails.php), variables `{var}`, personnalisation dans l'option non-autoloadée `idc_email_templates`, édités via wp-admin (Info Devis → Emails) et via InfoDevis Admin (`GET/POST idc/v1/admin/emails`).

## Manques identifiés (objet de la refonte)

1. Pas de test d'envoi outillé (durée, réponse SMTP) ; pas d'analyse SPF/DKIM/DMARC ; pas de page d'erreurs ni de renvoi ; pas de filtres/pagination sur les journaux ; pas de statistiques ; pas de rétention.
2. Modèles absents avec déclencheur possible : bienvenue client (user_register), mot de passe oublié (retrieve_password_message), validation/refus artisan (transition de statut de la fiche), abonnement annulé (webhook existant, sans email).
3. Flux SANS déclencheur dans le site actuel (modèles **non créés** pour ne rien simuler) : vérification d'adresse email, modification de rendez-vous, changement d'email — documentés comme « à brancher quand le flux existera ».

## Architecture cible

Voir `emails-architecture.md`. Résumé : table `wp_idv_email_logs` alimentée par les hooks natifs `wp_mail` (filter) + `wp_mail_succeeded` + `wp_mail_failed` (fiable, couvre AUSSI les emails WordPress natifs), contexte de type posé par `idc_send_mail`, rétention configurable purgée par WP-Cron, API REST `idc/v1/admin/emails/*` (capacité `idc_manage`), vue SPA `views/emails.js` (cartes) intégrée au design InfoDevis Admin existant.
