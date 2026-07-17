# Comparaison site original ↔ WordPress — ce qui manque (16/07/2026)

Comparaison des ~120 routes de l'app originale (`localhost/info-devis`) avec le thème
`info-devis` + plugins `info-devis-core` / `infodevis-admin`. Chaque point vérifié en code.

## ✅ Déjà porté (rappel)
Accueil, catégories + pages métier par slug, annuaire professionnels, fiche artisan (onglets),
devis (formulaire + confirmation), blog (liste + article), contact, tarifs-pro, guides,
niveaux de confiance, pages légales, inscription/connexion, prise de RDV (créneaux + photos),
dashboards client & artisan (profil, devis, avis, RDV, dispos, leads, projets, abonnement,
documents), back-office InfoDevis Admin, validation SIRET à l'inscription, emails, Stripe abo.

---

## ❌ Fonctionnalités métier ABSENTES (à recréer)

1. **Messagerie client ↔ artisan** — `client/messages.php`, `artisan/messages.php`, API
   `/api/messages`. Échange de messages sur un lead/devis. **Aucun équivalent WP** (le lead
   se répond en une fois via `idc_lead_respond`, pas de conversation). *Impact : fort pour
   une marketplace.*
2. **Signature électronique du devis** — `client/signature.php` (176 l.), routes
   `/dashboard/client/signature/{id}` + `sign`. Le client signe le devis en ligne.
   **Absent en WP.**
3. **Paiement en ligne d'un devis par le client** — `/dashboard/client/paiement/{id}`,
   `/paiement/success`, `/paiement/cancel`, `ApiPaymentsController`. **Absent** (WP ne gère
   que l'abonnement Stripe des artisans, pas le paiement d'un devis par un client).
4. **Rédaction d'articles de blog par les artisans** — `artisan/blog.php` + `blog_form.php`,
   routes `/dashboard/artisan/blog/*`. Un artisan peut écrire/éditer ses articles.
   **Absent** (en WP le blog n'est alimenté que par l'admin).
5. **Page statistiques artisan** — `artisan/stats.php` (151 l.), `/dashboard/artisan/stats`
   (vues fiche, leads reçus, taux de réponse…). **Absente.**
6. **Personnalisation de l'apparence de la fiche** — `artisan/apparence.php` (178 l.),
   `/dashboard/artisan/apparence` (couleurs, bannière…). **Absente.**
7. **Cloche de notifications** — `partials/notifications_bell.php` + API `/api/notifications`
   (le badge « 8 » visible dans l'en-tête de l'original). **Absente en WP.**

## ⚠️ Présentation / SEO incomplètes

8. **Page détail d'une réalisation** — `pages/projet_detail.php` (186 l.). L'URL WP
   `/realisation/{slug}/` répond (200) mais **sans template dédié** → rendu générique au lieu
   de la belle page projet (galerie photos, stats, artisan). À créer : `single-realisation.php`.
9. **Pages « prestation / sous-métier »** — `pages/prestation_detail.php` (336 l.), routes
   `/service/{slug}` et `/metiers/{cat}/{slug}`. Pages SEO détaillées par prestation.
   **Absentes en WP.**

## 🔸 Partiellement porté

10. **Vérification artisan** — `artisan/verification.php` (286 l.) : validation SIRET ✓ +
    upload de documents ✓ (`tpl-artisan-documents`), mais le workflow complet (upload
    KBIS/assurance/pièce d'identité + statuts + validation admin dédiée) est plus léger.
11. **Détail d'un RDV côté artisan** — liste présente (`tpl-artisan-rdv`), mais le détail
    riche de l'original (notes internes, saisie du montant, proposer un autre créneau,
    joindre un document au RDV) n'est pas porté.
12. **Mot de passe oublié / réinitialisation** — fonctionne via l'écran natif WordPress,
    mais pas au design du site (l'original a `auth/forgot.php` + `auth/reset.php` thématisés).

## 🕓 Différé volontairement (déjà noté avant)

13. **Connexion avec Google** (OAuth) — `/auth/google`. Non porté.
14. **Chatbot** — `/api/chatbot`. Non porté.
15. **Favoris (persistance)** — bouton présent, enregistrement non branché.
16. **Vérification d'email à l'inscription** — l'inscription connecte directement sans
    e-mail de confirmation (`/verifier-email` dans l'original).
17. **Notify-launch** (`/api/notify-launch`) — page « prévenez-moi au lancement ». Non
    pertinent pour le site en production.

---

## Priorisation suggérée
- **Cœur marketplace** : messagerie (1), signature devis (2), paiement client (3).
- **Valeur artisan** : blog artisan (4), stats (5), apparence (6), détail RDV riche (11).
- **Fidélité/SEO** : page réalisation (8), pages prestation (9), notifications (7).
- **Confort/sécurité** : mot de passe oublié thématisé (12), vérif email (16), favoris (15).
- **Optionnels** : Google (13), chatbot (14).
