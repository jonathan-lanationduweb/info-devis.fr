# InfoDevis.fr — Guide d'installation

## 🚀 Prérequis

- WAMP / XAMPP (PHP 8.1+, MySQL 8.0+, Apache)
- Extensions PHP : PDO, PDO_MySQL, mbstring, curl, openssl, json

---

## 📁 Installation

### 1. Copier les fichiers
```
C:/wamp64/www/info-devis/
```

### 2. Base de données
1. Ouvrir **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Créer la base : `infodevis`
3. Importer : `config/database.sql`

### 3. Configuration
Éditer `config/app.php` :
```php
define('APP_URL', 'http://localhost/info-devis');
// Renseigner Stripe, SMTP, Google Maps...
```

### 4. Droits d'écriture
```bash
chmod 775 uploads/ logs/
```

### 5. Apache — activer mod_rewrite
Dans `httpd.conf` :
```
AllowOverride All
```

### 6. Accès
- **Site** : `http://localhost/info-devis`
- **Admin** : `http://localhost/info-devis/admin`
- **Login admin** : `admin@info-devis.fr` / `Admin1234!`

---

## ⚙️ Configuration Stripe (paiements)

1. Créer un compte sur [stripe.com](https://stripe.com)
2. Copier les clés dans `config/app.php` :
```php
define('STRIPE_PUBLIC_KEY', 'pk_live_...');
define('STRIPE_SECRET_KEY', 'sk_live_...');
```
3. Configurer le webhook Stripe → URL : `https://votredomaine.fr/api/payments/webhook`

---

## 📧 Configuration Email (SMTP)

Dans `config/app.php` :
```php
define('MAIL_HOST', 'smtp.votre-provider.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'votre@email.fr');
define('MAIL_PASS', 'motdepasse');
```

---

## 🔄 Crons (automatisations)

```bash
# Queue worker — toutes les minutes
* * * * * php /var/www/info-devis/queue_worker.php

# SEO + métriques — tous les jours à 2h
0 2 * * * php /var/www/info-devis/cron_seo.php
```

---

## 🗂️ Architecture

```
/info-devis
├── index.php          ← Routeur principal
├── .htaccess          ← Réécriture URL + sécurité
├── config/
│   ├── app.php        ← Configuration générale
│   ├── database.php   ← Connexion PDO
│   ├── database.sql   ← Schéma BDD complet
│   └── security.php   ← CSRF, JWT, hash...
├── controllers/       ← Logique métier
├── models/            ← Accès base de données
├── services/          ← Mail, Matching, SIRET, SEO...
├── views/             ← Templates PHP
├── assets/            ← CSS, JS, images
├── chatbot/           ← Widget chatbot intelligent
├── uploads/           ← Fichiers utilisateurs
├── logs/              ← Logs erreurs & sécurité
├── queue_worker.php   ← Traitement emails asynchrones
└── cron_seo.php       ← Génération SEO automatique
```

---

## 🔒 Sécurité intégrée

- ✅ CSRF sur tous les formulaires
- ✅ Mots de passe bcrypt (cost 12)
- ✅ Préparation SQL (PDO paramétré)
- ✅ Échappement XSS (htmlspecialchars)
- ✅ Sessions sécurisées (httpOnly, regenerate)
- ✅ Headers sécurité (X-Frame-Options, CSP...)
- ✅ Rate limiting (API, contact, chatbot)
- ✅ Anti-doublon demandes (24h)
- ✅ Score client anti-spam
- ✅ Logs activité complets
- ✅ Consentement RGPD

---

## 📊 Fonctionnalités

| Fonctionnalité | Statut |
|---|---|
| Multi-catégories devis | ✅ |
| Matching artisans (zone + score) | ✅ |
| Chatbot intelligent | ✅ |
| Vérification SIRET (API officielle) | ✅ |
| Dashboard artisan complet | ✅ |
| Dashboard client | ✅ |
| Admin panel | ✅ |
| Système d'avis vérifiés | ✅ |
| Signature électronique | ✅ |
| Paiement Stripe | ✅ |
| SEO automatique + sitemap | ✅ |
| Queue jobs emails | ✅ |
| Relances automatiques | ✅ |
| RGPD / Consentements | ✅ |
| Anti-spam (scoring client) | ✅ |
| Disponibilités artisan | ✅ |
| Zones géographiques | ✅ |
| Métriques business | ✅ |

---

## 📞 Support

- Email : contact@info-devis.fr
- Tél : 06 61 48 62 67
- Adresse : 45 Rue des Boulets, 75011 Paris

