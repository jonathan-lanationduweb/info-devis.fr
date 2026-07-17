# Sécurité

## Mesures en place

| Menace | Protection |
|---|---|
| CSRF | Nonce WordPress sur chaque formulaire front (devis, avis, inscription, checkout) et chaque metabox admin |
| XSS | Échappement systématique en sortie (`esc_html`, `esc_attr`, `esc_url`, `esc_textarea`) ; assainissement en entrée (`sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`, `sanitize_title`, `sanitize_key`) |
| Injection SQL | Aucune requête brute avec variable non préparée ; `$wpdb->prepare` sur les requêtes du diagnostic ; API WP (WP_Query, get_posts) partout ailleurs |
| Élévation de privilèges | `current_user_can` sur les metaboxes et pages d'admin (`idc_manage`) ; contrôle du rôle avant le checkout Stripe ; dépôt d'avis réservé aux connectés |
| IDOR | Dashboards : les demandes sont filtrées par `_idc_client_user_id` / email du compte ; la fiche artisan est résolue depuis `_idc_user_id` du compte courant |
| Spam | Pot de miel + rate-limit par IP (transient, 2 min) sur le formulaire de devis ; anti-doublon d'avis par couple client/artisan |
| Rejeu / webhooks falsifiés | Signature Stripe vérifiée (HMAC, `hash_equals`, tolérance 5 min) + idempotence par ID d'événement |
| Redirections ouvertes | `wp_safe_redirect` sur tous les retours de formulaires (seule la redirection vers checkout.stripe.com utilise `wp_redirect`, URL fournie par l'API Stripe) |
| Accès direct aux fichiers PHP | `if (!defined('ABSPATH')) exit;` en tête de chaque fichier du thème et de l'extension |
| Secrets | wp-config.php uniquement ; aucun secret dans le code, le JS, les écrans d'admin ou la documentation |
| Journaux | Journal d'emails sans contenu de message ; aucun mot de passe/token/clé journalisé |

## Tests de sécurité effectués

- Webhook sans signature → 400 ✔ ; signature invalide → 400 ✔ ; rejeu → ignoré ✔
- Page d'admin Info Devis sans être connecté → « Accès refusé » ✔
- Double soumission d'avis → refusée ✔
- Rate-limit devis → 2e envoi sous 2 min refusé (constaté lors des tests E2E)
- Aucune erreur/warning PHP visible sur le front ✔

## Reste à faire avant production

1. **Rotation des secrets** (Stripe, SMTP, OAuth) — transités par l'ancien `.env`.
2. Réinitialiser le mot de passe du compte admin historique (documenté en clair dans l'ancien README).
3. Supprimer/priver d'accès l'ancienne appli sur le serveur (elle expose `.env`, logs, scripts de test).
4. Limiter les tentatives de connexion (extension type Limit Login Attempts) + 2FA pour les administrateurs.
5. HTTPS obligatoire + `FORCE_SSL_ADMIN`.
6. Vérifier les en-têtes de sécurité (CSP, X-Frame-Options) au niveau serveur — l'ancienne appli en définissait.
