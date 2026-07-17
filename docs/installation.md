# Installation — InfoDevis WordPress

## Prérequis

- Apache + mod_rewrite, PHP ≥ 8.1 (extensions pdo_mysql, curl, openssl, mbstring), MySQL ≥ 8.0
- En local : WAMP, vhost `info-devis.local` → `C:\wamp64\www\info-devis.local` + entrée hosts `127.0.0.1 info-devis.local`

## Installation propre (nouvel environnement)

1. Copier l'arborescence du site (ou restaurer `site-wordpress.zip` d'une sauvegarde).
2. Créer la base et importer `wordpress.sql` (dump de sauvegarde) :
   `mysql -u root wordpress < wordpress.sql`
3. Adapter `wp-config.php` : identifiants base, constantes `WPMS_*` (SMTP) et `IDC_STRIPE_*` (voir `configuration.md`) — **ne jamais commiter ce fichier**.
4. Si le domaine change : mettre à jour `siteurl` et `home` dans `wp_options`, puis régénérer les permaliens (Réglages → Permaliens → Enregistrer).
5. Vérifier `Info Devis → État du système` : tout doit être ✔.

## Composants applicatifs

| Composant | Emplacement | Activation |
|---|---|---|
| Thème Info Devis (enfant Astra) | `wp-content/themes/info-devis` | Apparence → Thèmes |
| Astra (parent) | `wp-content/themes/astra` | requis par le thème enfant |
| Info Devis Core | `wp-content/plugins/info-devis-core` | Extensions |
| Yoast SEO, WP Mail SMTP | `wp-content/plugins/…` | Extensions |

L'extension ne crée pas de table personnalisée : tout repose sur les tables natives (`wp_posts`, `wp_postmeta`, `wp_users`, `wp_terms…`) → les sauvegardes/restaurations standard suffisent.

## Migration des données historiques

Voir `migration-donnees.md`. Les scripts se relancent sans risque (idempotents).
