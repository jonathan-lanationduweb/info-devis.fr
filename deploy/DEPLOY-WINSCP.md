# Mise en ligne d'InfoDevis sur info-devis.fr (WinSCP + phpMyAdmin)

> ⚠️ **Important — ce que je peux et ne peux pas faire.**
> Je n'ai **aucun accès** à ton serveur online.net ni à WinSCP, et je ne dois **jamais**
> manipuler tes identifiants FTP/base de données. J'ai préparé **tout le côté local**
> (export SQL avec URLs déjà remplacées, `wp-config` de prod prêt, listes de fichiers,
> cette procédure). **C'est toi qui exécutes les étapes ci-dessous** ; je t'aide ensuite
> à **tester le site en ligne** une fois le domaine ouvert.
>
> 🔒 **Ne considère jamais la mise en ligne « réussie » tant que https://info-devis.fr
> n'a pas été ouvert et testé réellement dans un navigateur.**

---

## 0. Ce qui est déjà prêt (dans ce dépôt)

| Fichier | Rôle |
|---|---|
| `backups/info-devis-prod-2026-07-21.sql` | **Base de production** : URLs `http://info-devis.local` → `https://info-devis.fr` déjà remplacées (sérialisation respectée, WP-CLI `search-replace --precise`). 0 référence locale restante. **À importer** dans la base de prod. |
| `deploy/wp-config-prod.php` | **Config de prod** : URLs, sels de sécurité neufs, HTTPS forcé, durcissement. Il ne reste que **4 valeurs de base de données** à renseigner. |
| `deploy/DEPLOY-WINSCP.md` | Ce document. |

> ⚠️ Le fichier SQL contient des mots de passe (hachés) et des données personnelles.
> Ne le publie/committe pas. Supprime-le du serveur après import.

---

## 1. Connexion WinSCP (détaillé)

1. Ouvre WinSCP → fenêtre « Connexion ».
2. **Protocole** : essaie **SFTP** d'abord ; si échec, reviens et choisis **FTP**.
3. **Nom d'hôte** : `ftp.online.net` · **Utilisateur** : `webmaster@info-devis.fr` ·
   **Mot de passe** : le tien (ne me le communique jamais).
4. **Connexion**. 1ʳᵉ fois : accepte la clé du serveur (« Oui »).
5. Écran en deux volets : **gauche = ton PC**, **droite = le serveur**. À droite tu dois voir
   `www/`, `www-old/`, `database_backup/`, `logs/`.

> 💡 `www` est le dossier que le domaine affiche publiquement. Toute la manœuvre tourne autour.

---

## 2. SAUVEGARDE de l'ancien site (obligatoire, avant tout)

### 2a. Fichiers
Dans WinSCP, **renomme** (clic droit → Renommer) le dossier actuel :

```
www  →  www-backup-info-devis-2026-07-21
```

- ❌ **Ne pas** écraser ni réutiliser `www-old` (déjà pris).
- Si `www-backup-info-devis-2026-07-21` existe déjà, ajoute un suffixe : `-2`, `-b`, etc.
- Puis **crée un nouveau dossier vide** `www` (clic droit → Nouveau → Répertoire).

### 2b. Base de données de l'ancien site
Depuis phpMyAdmin de l'hébergeur (ancienne base du site PHP) :
- Onglet **Exporter** → méthode **Personnalisée** → format **SQL** → **Exécuter**.
- Enregistre le fichier, puis **dépose-le dans `/database_backup/`** via WinSCP,
  nommé par ex. `ancien-site-info-devis-2026-07-21.sql`.

✅ À ce stade : anciens fichiers **et** ancienne base sauvegardés. Rollback possible.

---

## 3. TRANSFERT du nouveau site vers /www

Envoie le contenu du dossier local **`C:\wamp64\www\info-devis.local\`** dans le **`/www`**
distant (le contenu directement à la racine de `www`, **pas** dans un sous-dossier).

### ✅ À ENVOYER
- `wp-admin/`, `wp-includes/`, tous les `wp-*.php`, `index.php`, `xmlrpc.php`
- `wp-content/themes/info-devis/` (**notre thème**) + `twentytwentyfour/` (thème de secours)
- `wp-content/plugins/` **en entier** : `info-devis-core/`, `infodevis-admin/`,
  `wordpress-seo/`, `wp-mail-smtp/`, `akismet/`, `hello.php`, `index.php`
- `wp-content/uploads/` (**~5,3 Mo — les médias, indispensable**)
- `wp-content/languages/`
- Fichiers PWA à la racine : `manifest.webmanifest`, `service-worker.js`, `offline.html`,
  `assets/` (icônes)

### ❌ À NE PAS ENVOYER (dev / secrets / inutile en prod)
- `wp-config.php` (local) → **remplacé** par `wp-config-prod.php`, voir étape 4
- `.git/`, `.gitignore`
- `backups/` (dont le `.sql` de prod : il s'importe via phpMyAdmin, ne va pas dans `/www`)
- `deploy/`, `docs/`, `DEPLOY.md`, `README.md`, `readme.html`
- Dans `wp-content/themes/info-devis/` : `node_modules/`, `package.json`,
  `package-lock.json`, `tailwind.config.js` *(le CSS compilé `assets/css/tailwind.build.css`
  est déjà inclus — les sources de build sont inutiles en prod)*
- `wp-content/mu-plugins/` (vide — rien à envoyer)
- `wp-content/cache/`, `wp-content/upgrade/` s'ils existent

> Astuce WinSCP : Options → Préférences → Transfert → Masques de fichiers, exclure
> `node_modules/; .git/; backups/; deploy/; *.log`.

---

## 4. Config : wp-config.php de production

1. Ouvre **`deploy/wp-config-prod.php`** (local), renseigne les **4 valeurs de base
   de données** `À_REMPLIR_...` que t'a fournies online.net (console → Bases MySQL).
   Sur online.net, l'hôte de base est souvent **un serveur dédié** (ex.
   `xxxxx.mysql.db`), **pas** `localhost` — vérifie dans ta console.
2. Envoie ce fichier dans `/www` **en le renommant `wp-config.php`**.
3. Les sels de sécurité et le forçage HTTPS sont déjà en place ; ne touche pas au reste.

---

## 5. IMPORT de la base de production

1. phpMyAdmin de l'hébergeur → sélectionne la **base de prod** (celle configurée à l'étape 4).
2. Si elle contient de vieilles tables, **supprime-les** d'abord (onglet Opérations, ou
   sélectionne tout → Supprimer) pour repartir propre.
3. Onglet **Importer** → **Choisir un fichier** → `info-devis-prod-2026-07-21.sql` (depuis
   ton PC) → **Exécuter**.
   - Si le fichier dépasse la limite d'upload de phpMyAdmin, dépose-le d'abord via WinSCP
     et utilise un import « depuis le serveur », ou compresse-le en `.sql.gz`.
4. Vérifie que les tables `wp_options`, `wp_posts`, `wp_users`, `wp_idc_*`, `wp_ida_*`
   sont bien présentes.

> Les URLs sont **déjà** en `https://info-devis.fr` dans ce dump — **aucun** `search-replace`
> supplémentaire n'est nécessaire. (Réalisé en local avec
> `wp search-replace 'http://info-devis.local' 'https://info-devis.fr' --all-tables --precise`.)

---

## 6. HTTPS / domaine

1. Assure-toi que le **certificat SSL** (Let's Encrypt) est activé pour info-devis.fr dans
   la console online.net, et que le domaine pointe bien sur `/www`.
2. `wp-config-prod.php` force déjà HTTPS (`FORCE_SSL_ADMIN`, détection
   `HTTP_X_FORWARDED_PROTO`).
3. **Redirection http→https et www→non-www** : à mettre dans le `.htaccess` de `/www`.
   Un `.htaccess` WordPress standard suffit ; ajoute en tête si besoin :
   ```apache
   <IfModule mod_rewrite.c>
   RewriteEngine On
   RewriteCond %{HTTPS} off [OR]
   RewriteCond %{HTTP_HOST} ^www\. [NC]
   RewriteCond %{HTTP_HOST} ^(?:www\.)?(.+)$ [NC]
   RewriteRule ^ https://%1%{REQUEST_URI} [L,R=301]
   </IfModule>
   # … puis le bloc # BEGIN WordPress habituel …
   ```

---

## 7. TESTS en ligne (obligatoire avant d'annoncer la mise en ligne)

Ouvre https://info-devis.fr et vérifie :

- [ ] Page d'accueil s'affiche, **cadenas HTTPS** vert, pas d'alerte de contenu mixte
- [ ] Styles/polices/icônes OK (design identique au local)
- [ ] Liens du menu, page **Professionnels**, une fiche artisan
- [ ] **Connexion** artisan + tableau de bord (RDV, disponibilités, documents)
- [ ] **Connexion** client + Mes RDV / Mes projets
- [ ] **Formulaire de devis** (soumission + email de confirmation reçu)
- [ ] Back-office `/infodevis-admin/`
- [ ] `wp-admin` accessible et en HTTPS
- [ ] Bandeau **RGPD** s'affiche, boutons Accepter/Refuser fonctionnent
- [ ] **PWA** : `manifest.webmanifest` et `service-worker.js` se chargent (onglet Réseau, 200)
- [ ] `https://info-devis.fr/sitemap_index.xml` (Yoast) répond
- [ ] Yoast → **le site est bien indexable** (Réglages → Lecture : « Visibilité moteurs »
      décochée). *Le `blog_public=1` est déjà dans le dump.*

👉 Dis-moi quand le domaine est ouvert : je peux t'aider à **auditer le site en ligne**
(console, réseau, responsive, SEO) via le navigateur.

### Après validation
- Régénère les index **Yoast** (SEO → Outils → « Optimiser les données SEO ») — les caches
  ont été vidés dans le dump.
- Configure **WP Mail SMTP** (clé Brevo) dans son écran de réglages, envoie un mail de test.
- Renseigne les **clés Stripe LIVE** dans `wp-config.php` si les paiements sont activés.
- **Supprime** `info-devis-prod-2026-07-21.sql` du serveur s'il y a été déposé.

---

## 8. Plan de ROLLBACK (si problème)

1. Renomme le `/www` défaillant → `www-echec-2026-07-21`.
2. Renomme `www-backup-info-devis-2026-07-21` → `www` (l'ancien site PHP revient).
3. Ré-importe `ancien-site-info-devis-2026-07-21.sql` (étape 2b) dans l'ancienne base.
4. Le site d'origine est de nouveau en ligne. On corrige à froid, puis on retente.

---

## 9. Modèle de rapport final (à compléter après exécution)

```
- Dossier local utilisé ......... C:\wamp64\www\info-devis.local
- Dossier distant utilisé ....... /www
- Sauvegarde fichiers créée ..... www-backup-info-devis-2026-07-21  (OUI/NON)
- Base ancienne sauvegardée ..... /database_backup/ancien-site-...sql  (OUI/NON)
- Nouvelle base importée ........ info-devis-prod-2026-07-21.sql  (OUI/NON)
- URLs remplacées ............... OUI (fait en local, 0 référence .local restante)
- wp-config de prod en place .... OUI/NON
- HTTPS actif + redirections .... OUI/NON
- Tests en ligne (§7) ........... … / … passés
- Statut final .................. EN LIGNE ET TESTÉ / EN ÉCHEC (voir rollback)
```
