# Emails — fonctionnement et administration

## Architecture

- Couche d'envoi : `wp_mail()` → **WP Mail SMTP** (constantes `WPMS_*` dans `wp-config.php`).
- Local : Mailtrap sandbox (les emails sont capturés, rien ne part réellement).
- Production : Brevo — créer la clé SMTP dans Brevo > SMTP & API puis remplacer host/port/user/pass dans `wp-config.php`.

## Modèles administrables (Info Devis → Emails)

9 modèles, chacun avec sujet, corps, variables `{…}` et interrupteur d'activation :

| Clé | Déclencheur |
|---|---|
| `devis_admin` | Nouvelle demande de devis → notification admin |
| `devis_client` | Nouvelle demande → confirmation au client |
| `devis_artisan` | Nouvelle demande → chaque artisan matché |
| `inscription_artisan_admin` | Inscription artisan → admin (validation attendue) |
| `inscription_artisan_bienvenue` | Inscription artisan → bienvenue |
| `avis_recu_admin` | Dépôt d'avis → admin (modération) |
| `avis_publie_artisan` | Avis approuvé → artisan concerné |
| `abonnement_active` | Webhook Stripe checkout complété → artisan |
| `paiement_echec` | Webhook Stripe paiement échoué → artisan |

Les personnalisations sont stockées dans l'option non-autoloadée `idc_email_templates`. La fonction d'envoi est `idc_send_mail( $clé, $destinataire, $variables )`.

## Journalisation

Les 50 derniers envois (date, modèle, destinataire, statut envoyé/échec) sont visibles en bas de la page Emails — sans le contenu du message (pas de données personnelles superflues). Emails natifs WordPress (réinitialisation de mot de passe, etc.) : gérés par WordPress, transportés par le même SMTP.

## À faire (V2)

- File d'attente + renvoi automatique après échec (WP-Cron) — l'ancienne appli utilisait `queue_worker.php`.
- Version HTML des modèles (actuellement texte, plus fiable en délivrabilité de départ).
- Relances automatiques des demandes sans réponse (paramétrable).
