# Architecture — centre de gestion des emails

## Vue d'ensemble

| Couche | Emplacement | Rôle |
|---|---|---|
| Journalisation | `infodevis-admin/includes/email-logs.php` | Table `wp_idv_email_logs`, hooks de capture, requêtes, rétention |
| API | `infodevis-admin/includes/rest-emails.php` | Routes `idc/v1/admin/emails/*` (capacité `idc_manage`) |
| Interface | `infodevis-admin/assets/js/views/emails.js` | SPA : cartes + journaux + erreurs + modèles (routes `#/parametres/emails[...]`) |
| Modèles & envoi | `info-devis-core/includes/emails.php` | Catalogue (19 modèles), `idc_send_mail()`, alias `{{variable}}`, déclencheurs |
| Transport | WP Mail SMTP (constantes `WPMS_*`) | **Réutilisé, jamais dupliqué** — aucun secret stocké ni affiché ailleurs |

## Journalisation

Capture par hooks natifs — fiable et exhaustive (emails métier ET natifs WordPress) :
1. filter `wp_mail` (prio 999) → insertion ligne `pending` (type via global `idc_current_mail_template`, défaut `wordpress` ; objet lié via `idc_current_mail_related`) ;
2. action `wp_mail_succeeded` → `sent` + `sent_at` ;
3. action `wp_mail_failed` → `failed` + code/message PHPMailer + **corps conservé uniquement en cas d'échec** (`failed_body`, pour le renvoi) — purgé avec la rétention.

Champs : id, message_type, recipient_email, subject, excerpt (200 c. max), status, attempts, error_code, error_message, failed_body, failed_headers, provider, created_at, sent_at, resolved_at, related_object_type, related_object_id. Index sur status/type/destinataire/date.

**Rétention** : option `idv_email_retention_days` (7–730 j, défaut 90), purge quotidienne WP-Cron `idv_email_logs_purge`, modifiable depuis la carte Journaux.

## Routes API

| Route | Méthode | Rôle |
|---|---|---|
| `/emails/overview` | GET | Stats, config SMTP publique, état de connexion (socket réel + bannière serveur), dernier test, délivrabilité en cache, rétention |
| `/emails/test` | POST | Envoi de test (to/sujet/message/HTML), durée ms, erreur PHPMailer exacte — **limité à 5/10 min** |
| `/emails/deliverability` | POST | Contrôles **DNS réels** (`dns_get_record`) : SPF (TXT), DMARC (`_dmarc.`), DKIM (sélecteurs courants, sinon « inconnu » honnête), MX ; score /100 + recommandations concrètes |
| `/emails/logs` | GET | Liste filtrée (statut, type, destinataire, sujet, dates) + pagination — requêtes préparées `$wpdb->prepare` |
| `/emails/logs/{id}/resend` | POST | Renvoi d'un échec (corps conservé requis), incrémente `attempts` |
| `/emails/logs/{id}/resolve` | POST | Marque une erreur résolue |
| `/emails/template-test` | POST | Envoi d'un modèle avec variables d'exemple factices — même limitation |
| `/emails/settings` | POST | Rétention |
| `/emails` (existant) | GET/POST | Catalogue + sauvegarde des modèles (réutilisé tel quel) |

## Modèles (19) et variables

Catalogue existant (13) + nouveaux à déclencheurs réels : `compte_client_bienvenue` (user_register rôle client), `mdp_oublie` (filtres `retrieve_password_message/title` — remplace l'email natif), `artisan_valide` (transition pending→publish de la fiche), `artisan_refuse` (meta `_idc_verification_status` → refused), `abonnement_annule` (webhook Stripe subscription.deleted).

**Non créés volontairement** (aucun flux déclencheur dans le site — pas de faux modèles) : vérification d'adresse email, modification de rendez-vous. À ajouter quand le flux existera.

Variables : syntaxe interne `{var}` + **alias documentés `{{double_accolade}}`** ({{site_name}}, {{user_name}}, {{user_email}}, {{artisan_name}}, {{client_name}}, {{quote_reference}}, {{appointment_date}}, {{appointment_time}}, {{payment_amount}}, {{subscription_name}}, {{login_url}}, {{reset_url}}) convertis dans `idc_send_mail`. Variable inconnue = laissée visible telle quelle (pas de plantage).

## Interface (design InfoDevis Admin)

6 cartes (`.ida-card`) en grille `auto-fill minmax(320px,1fr)` → 3/2/1 colonnes selon la largeur ; badges d'état (Opérationnel / Attention / Erreur / Non configuré / En attente) ; tiroirs (`openDrawer`) pour le test d'email et l'édition de modèle (aperçu en direct avec variables d'exemple + envoi de test) ; tableaux scrollables (`overflow-x:auto`) sur mobile ; entrée sidebar « Emails » existante conservée.

## Sécurité

Capacité `idc_manage` sur toutes les routes (permission_callback commun), nonce REST `X-WP-Nonce`, validation/sanitisation serveur systématique, sorties échappées (`esc()` côté SPA, sanitize côté PHP), requêtes préparées, rate-limit d'envoi, **aucun secret** (mot de passe SMTP/clés jamais lus ni retournés), variables d'exemple factices pour les tests de modèles (jamais de vraies données client).
