# Stripe — abonnements professionnels

## Configuration

Clés définies dans `wp-config.php` uniquement (`IDC_STRIPE_PUBLIC`, `IDC_STRIPE_SECRET`, `IDC_STRIPE_PRICE_SILVER`, `IDC_STRIPE_PRICE_GOLD`, `IDC_STRIPE_WEBHOOK_SECRET`). **Actuellement en mode TEST.** La clé secrète n'apparaît jamais côté client ni dans les écrans d'admin.

## Flux d'abonnement

1. Page **Tarifs professionnels** (`[idc_tarifs]`) : Gratuit / Silver 29 € / Gold 59 €.
2. Artisan connecté → bouton « Choisir » → `admin_post_idc_stripe_checkout` (nonce + contrôle du rôle).
3. Création d'une **Checkout Session** (mode `subscription`) via l'API REST Stripe (`wp_remote_post`, pas de SDK) avec `metadata[user_id]` et `metadata[plan]` → redirection vers checkout.stripe.com.
4. Retour : `/espace-membre/?abo=ok` (succès) ou `/tarifs-professionnels/?abo=annule`.
5. **Webhook** `POST /wp-json/idc/v1/stripe-webhook` :
   - vérification de la signature `Stripe-Signature` (HMAC-SHA256, tolérance 5 min anti-rejeu) ;
   - **idempotence** par ID d'événement (option `idc_stripe_events`, 200 derniers) ;
   - `checkout.session.completed` → plan sur le compte + la fiche artisan + email `abonnement_active` ;
   - `invoice.payment_failed` → email `paiement_echec` ;
   - `customer.subscription.deleted` → retour au plan gratuit.

## Tests effectués (15/07/2026, clés test)

- Création de session réelle : OK (`cs_test_…`, URL checkout.stripe.com).
- Webhook signé simulé : HTTP 200, plan appliqué au compte + fiche.
- Rejeu du même événement : « already processed » (aucun double traitement).
- Signature invalide : HTTP 400.
- Diagnostic : Info Devis → État du système affiche l'état Stripe (mode TEST/LIVE) sans révéler les clés.

## Avant production

1. Créer les produits/prix en mode live et remplacer les 2 price IDs.
2. Remplacer les clés par les clés **live** (rotation : les clés test ont transité par l'ancien `.env`).
3. Déclarer l'endpoint webhook de production dans le dashboard Stripe (événements : `checkout.session.completed`, `invoice.payment_failed`, `customer.subscription.deleted`) et reporter le nouveau `whsec_…`.
4. V2 : portail client Stripe (changement d'offre/annulation en self-service), historique des paiements dans l'espace artisan, factures.
