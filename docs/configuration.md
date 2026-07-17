# Configuration — InfoDevis WordPress

## Constantes wp-config.php (secrets — jamais dans le code ni Git)

| Constante | Rôle |
|---|---|
| `WPMS_ON`, `WPMS_MAILER`, `WPMS_SMTP_HOST`, `WPMS_SMTP_PORT`, `WPMS_SSL`, `WPMS_SMTP_AUTH`, `WPMS_SMTP_USER`, `WPMS_SMTP_PASS`, `WPMS_MAIL_FROM`, `WPMS_MAIL_FROM_NAME` | SMTP (extension WP Mail SMTP). Local : Mailtrap sandbox. Production : Brevo (`smtp-relay.brevo.com:587`, TLS) |
| `IDC_STRIPE_PUBLIC` / `IDC_STRIPE_SECRET` | Clés API Stripe (actuellement **mode test**) |
| `IDC_STRIPE_PRICE_SILVER` / `IDC_STRIPE_PRICE_GOLD` | IDs de prix des abonnements |
| `IDC_STRIPE_WEBHOOK_SECRET` | Secret de signature du webhook |
| `FS_METHOD` = `direct` | Installation d'extensions sans FTP (local) |

## Réglages WordPress appliqués

- Permaliens : `/%postname%/`
- Page d'accueil statique « Accueil » ; page des articles « Actualités »
- Page de politique de confidentialité déclarée (outils RGPD natifs actifs)
- Rôles : `artisan`, `client`, `gestionnaire` (+ capacité `idc_manage` pour gestionnaire et administrateur)

## Écrans d'administration Info Devis

- **Info Devis → État du système** : diagnostic complet sans exposition de secrets.
- **Info Devis → Emails** : personnalisation de chaque modèle (sujet, corps, variables, activation) + journal des 50 derniers envois.

## Avant la mise en production (checklist)

1. **Rotation de tous les secrets** (Stripe live, SMTP Brevo, Google OAuth si repris) — les clés de test ont transité par l'ancien `.env`.
2. Reconfigurer l'endpoint webhook côté Stripe : `https://<domaine>/wp-json/idc/v1/stripe-webhook` + nouveau `whsec_…`.
3. Compléter les pages légales (mentions : forme juridique/SIREN/hébergeur ; CGU : relecture juridique).
4. Réglages → Lecture : décocher « demander aux moteurs de ne pas indexer ».
5. Supprimer les comptes et contenus de test (voir `tests.md`, section Données de test).
6. Désactiver l'ancienne appli (`C:\wamp64\www\info-devis`) sur le serveur de production ; appliquer `plan-redirections.csv`.
7. Passer `WP_DEBUG` à false (déjà le cas) et vérifier qu'aucune erreur PHP ne s'affiche.
