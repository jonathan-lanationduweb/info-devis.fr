# Audit complet — sécurité, performance & qualité (16/07/2026)

Audit du site WordPress InfoDevis (thème `info-devis`, plugins `info-devis-core` et
`infodevis-admin`, configuration serveur) réalisé avant mise en production.

## Verdict global

**Le code applicatif est solide et bien sécurisé.** L'échappement des sorties (anti-XSS),
les nonces (anti-CSRF), les contrôles de propriété (anti-IDOR), les requêtes SQL préparées,
la signature du webhook Stripe et la validation des uploads sont appliqués correctement et
systématiquement. Aucune injection SQL ni XSS exploitable trouvée dans le code custom.

Les risques réels sont **de configuration/exposition**, pas de logique applicative — et la
plupart ne comptent qu'en production. Rien de tout cela n'est dangereux tant que le site
reste en local.

---

## 🔴 CRITIQUE — à corriger impérativement AVANT toute mise en ligne

### C1. Dossier `backups/` téléchargeable publiquement
`http://<site>/backups/` liste et sert des **dumps SQL complets** (`db-wordpress.sql`,
990 Ko, avec les hash de mots de passe, emails, téléphones, adresses de chantier, IDs Stripe)
et `wp-config.php.bak` (**contenant tous les secrets**). Listing de répertoire activé, aucune
protection. En production = fuite totale de la base et des secrets.
→ **Sortir `backups/` (et `docs/`) hors de la racine web** + `.htaccess deny` de secours.
Source aggravante : `infodevis-admin/includes/rest-lists.php:466-543` écrit les sauvegardes
auto DANS la racine web (`ABSPATH/backups/`, nom horodaté devinable). À rediriger hors webroot.

### C2. Secrets en clair dans le code source
`wp-config.php` contient clés Stripe (l.115-119) et identifiants SMTP (l.107-108) en clair.
→ En production : clés LIVE **jamais** committées ; charger via variables d'environnement ou
fichier hors webroot non versionné. **Rotation obligatoire** de toutes les clés avant le LIVE
(les clés de test ont potentiellement fuité via les backups ci-dessus). Régénérer aussi les
clés de salage (salts) — invalide les sessions, sain à la mise en ligne.

---

## 🟠 IMPORTANT — durcissement avant production

### H1. Liens de réinitialisation de mot de passe conservés et exposés
`infodevis-admin` : le hook `wp_mail_failed` stocke le **corps complet** des emails échoués
(colonne `failed_body`), y compris le lien de reset WordPress (`wp-login.php?action=rp&key=…`).
Ce corps est renvoyé par l'API (`rest-emails.php:241`) et rejouable. Comme la capacité
`idc_manage` est aussi donnée au rôle **gestionnaire**, un gestionnaire pourrait consulter un
lien de reset d'admin → prise de contrôle de compte.
→ Ne pas stocker le corps des emails système/reset (ou masquer les URLs de reset dans les logs).
Fichiers : `email-logs.php:124-138`, `rest-emails.php:220-286`, `emails.php:240-253`.

### H2. Rôle « gestionnaire » trop puissant
`info-devis-core.php:195-212` : le gestionnaire cumule `edit_others_posts`,
`delete_others_posts`, `delete_published_posts` **et** `idc_manage` (accès total au back-office,
création de dumps SQL, lecture de toutes les données clients, logs emails). Combiné à H1, un
simple gestionnaire peut exfiltrer la base.
→ Cloisonner : capacités distinctes pour backups / logs emails / paramètres sensibles ;
retirer les `delete_*` non nécessaires.

### H3. `.htaccess` sans aucun durcissement
Racine = bloc WordPress par défaut seulement. Manquent : blocage de `wp-config.php`, `.bak`,
`.sql`, `.log` ; désactivation du listing (`Options -Indexes`) ; en-têtes de sécurité
(`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, HSTS, CSP).
**`wp-content/uploads/` n'a aucun `.htaccess`** interdisant l'exécution PHP — or le rôle artisan
a `upload_files`. → Ajouter un `.htaccess` « pas de PHP dans uploads » + validation MIME (déjà
faite pour les photos RDV : jpg/png/webp, 10 Mo, 5 max ✓).

### H4. Absence d'anti-abus sur inscription et connexion
`inscription.php` : seul un honeypot protège l'inscription (pas de rate-limit, captcha, ni
double opt-in), et l'inscription **connecte automatiquement**. Permet création massive de
comptes + email-bombing de l'admin (un email envoyé par inscription artisan). La connexion
(wp-login) n'a pas de limiteur anti-brute-force.
→ Rate-limit / captcha sur inscription + connexion (ou plugin type Limit Login Attempts).
NB : le formulaire de devis/contact, lui, est déjà rate-limité ✓.

### H5. `DISALLOW_FILE_EDIT` absent + HTTPS non forcé
L'éditeur de code thème/plugin de l'admin est actif (`DISALLOW_FILE_EDIT` non défini) → un
compte admin compromis peut injecter du PHP. HTTPS non forcé (`FORCE_SSL_ADMIN`).
→ Ajouter `DISALLOW_FILE_EDIT`, `FORCE_SSL_ADMIN`, utilisateur MySQL dédié (pas `root`,
mot de passe fort) en production.

---

## 🟡 RECOMMANDÉ — qualité, performance, RGPD

### Performance
- **P1. Tailwind chargé via CDN** (`functions.php:52`) : `cdn.tailwindcss.com` compile le CSS
  dans le navigateur — explicitement **non destiné à la production** (lenteur, clignotement au
  chargement, dépendance externe). → Compiler un CSS statique et le charger localement.
- **P2. ~60 images hotlinkées vers images.unsplash.com** (`inc/categorie-config.php`,
  `front-page.php`, gabarits catégories). Dépendance externe (disponibilité, CGU, perf/LCP).
  → Rapatrier en local (le thème privilégie déjà les fichiers locaux s'ils existent).
- **P3. Requêtes N+1 dans l'annuaire** (`card-pro.php:21-28`) : un `get_posts(-1)` par carte
  pour compter les réalisations. → Précalculer un compteur en post_meta, ou mettre en cache.
- **P4. `numberposts/posts_per_page = -1`** à plusieurs endroits (dashboards, recalcul des
  notes, `<select>` de 200 artisans). OK à faible volume, à borner/cacher si le site grossit.
- **P5. Fontes/Font Awesome via CDN** sans `preconnect`. → Auto-héberger ou preconnect.

### RGPD / données personnelles
- **R1.** La table `idv_email_logs` et l'option `idc_email_log` conservent destinataires,
  sujets et extraits en clair (purge à 90 j). Minimisation à revoir.

### Nettoyage / surface d'attaque
- **N1.** Supprimer **Hello Dolly** (`hello.php`), le thème **Astra** (inutilisé, le thème actif
  est `info-devis`) et les thèmes par défaut superflus (garder un seul récent en secours).
- **N2.** Supprimer/bloquer `readme.html` et `license.txt` (exposent la version WP).
- **N3.** `xmlrpc.php` actif (répond 405) — bloquer si non utilisé (vecteur brute-force/DDoS).
- **N4.** En production : `DISABLE_WP_CRON` + vraie tâche cron système (fiabilise la purge des
  logs emails, seul cron custom du projet — implémentation correcte par ailleurs).

### Points mineurs (défense en profondeur)
- Quelques templates font `echo $_GET['x'] === 'ok' ? 'texte fixe' : 'texte fixe'` : non
  exploitable (la valeur GET n'est jamais imprimée) mais à convertir en mapping `esc_html()`
  par cohérence.
- `page-devis.php:298` construit un message d'erreur en `innerHTML` à partir de `data.errors`
  du serveur — vérifier que le handler ne réfléchit pas d'entrée brute (DOM-XSS théorique).
- Rate-limit RDV : présent sur la voie AJAX (3/7j) mais absent de l'ancien handler
  `admin_post_idc_rdv_create` (`rdv.php:339`) — harmoniser ou retirer l'ancien handler.

---

## ✅ Ce qui est déjà bien fait (vérifié)

- **Inscription** : impossible de créer un compte admin/gestionnaire (rôle forcé à
  client/artisan, `inscription.php:90`) — vérifié.
- **Anti-IDOR** : propriété systématiquement vérifiée (RDV, avis, devis liés, leads, fiches).
- **REST admin** : `permission_callback` correct sur toutes les routes (`ida_can_manage`) ;
  seul `__return_true` = webhook Stripe (justifié par la signature).
- **SQL** : tout est préparé (`$wpdb->prepare`), y compris les logs emails. Aucune injection.
- **XSS** : sorties échappées partout (`esc_html/attr/url/js`).
- **Stripe** : signature HMAC + anti-rejeu 300 s + idempotence + plan/prix validés serveur.
- **Uploads** : whitelist MIME + taille + nombre.
- **Emails** : `To` validé, pas d'injection d'en-têtes.
- **Redirections** : `wp_safe_redirect` partout (pas d'open redirect).
- **Nonces** : présents sur tous les formulaires POST et vérifiés côté handlers.

---

## Ordre d'action conseillé

**Maintenant (sans risque, en local) :** sortir `backups/` et `docs/` du webroot ; supprimer
Hello Dolly + Astra + thèmes superflus ; ajouter `DISALLOW_FILE_EDIT` ; corriger l'écriture des
backups auto hors webroot ; ne plus stocker le corps des emails de reset.

**À la mise en production :** rotation + externalisation des secrets ; `.htaccess` durci +
uploads no-PHP ; HTTPS forcé + user MySQL dédié ; rate-limit inscription/connexion ; build
Tailwind local + images rapatriées ; cloisonner le rôle gestionnaire.
