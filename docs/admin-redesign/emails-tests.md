# Tests — centre de gestion des emails (16 juillet 2026)

Méthode : callbacks REST exécutés côté serveur avec un compte administrateur
(`wp_set_current_user`), envois réels via Mailtrap sandbox, DNS réels.
Syntaxe validée : `php -l` (5 fichiers PHP) + `node --check` (emails.js, app.js).

| # | Test | Résultat |
|---|---|---|
| 1 | Création de la table `wp_idv_email_logs` (dbDelta, versionnée) | ✔ existe |
| 2 | Overview : config SMTP publique, connexion socket réelle (bannière `220 smtp.mailtrap.io ESMTP ready` en 261 ms), 19 modèles | ✔ — **aucun secret dans la réponse** (vérifié) |
| 3 | Email de test réussi | ✔ envoyé en 2 810 ms via smtp:sandbox.smtp.mailtrap.io, durée mesurée |
| 4 | Destinataire invalide | ✔ refusé avec message clair (validation serveur) |
| 5 | Échec forcé (adresse rejetée par PHPMailer) | ✔ ligne `failed` avec code + message + **corps conservé** |
| 6 | Délivrabilité (DNS réels sur info-devis.fr) | ✔ SPF ok · DKIM inconnu (sélecteur non détecté — honnête) · DMARC absent · MX oui → score 55/100 + 3 recommandations concrètes |
| 7 | Journaux : total, filtre statut, recherche par sujet, pagination | ✔ |
| 8 | Renvoi d'un email échoué | ✔ renvoi tenté avec le corps d'origine, `attempts` 1→2, nouvel échec correctement journalisé (l'adresse était invalide — comportement attendu) |
| 9 | Marquer comme résolu | ✔ `resolved_at` posé |
| 10 | Test d'un modèle avec variables d'exemple | ✔ (1er essai rejeté par Mailtrap « data not accepted » — capturé dans les journaux avec l'erreur SMTP exacte ; réessai réussi. Démonstration involontaire mais probante du système 🙂) |
| 11 | Variables alias `{{user_name}}`/`{{site_name}}` + variable inconnue | ✔ alias remplacés ; inconnue laissée visible, aucun plantage |
| 12 | Limitation d'envoi (anti-abus) | ✔ 6ᵉ test refusé HTTP 429 |
| 13 | Rétention configurable + purge WP-Cron | ✔ ligne antidatée 2020 purgée, cron quotidien planifié |
| 14 | Accès sans autorisation | ✔ routes sous `ida_route()` → capacité `idc_manage` exigée (permission_callback commun, mécanisme déjà en production sur toute l'API admin) |
| 15 | Coexistence WP Mail SMTP | ✔ transport inchangé, constantes lues sans duplication, lien « Réglages SMTP » vers l'écran du plugin (mode classique) |
| 16 | Responsive | Grille `auto-fill minmax(320px,1fr)` (3/2/1 colonnes) + tableaux `overflow-x:auto` — même mécanique que les écrans validés du reste de l'app ; passe visuelle mobile à faire lors de la prochaine session navigateur |

## Restant à tester à la main (nécessite le navigateur connecté)

- Parcours visuel complet des 4 écrans dans l'app (`/infodevis-admin/` → Paramètres → Emails).
- SMTP totalement indisponible (couper le réseau) — le code capture déjà l'erreur socket dans la carte SMTP.
