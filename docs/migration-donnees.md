# Méthode de migration des données

## Principes

- **Lecture seule** sur la base historique `infodevis` (mysqli dédié, aucune écriture).
- **Idempotence** : détection des doublons par slug (termes), email (utilisateurs) et meta `_idc_legacy_*` (contenus). Relancer un script est sans effet sur l'existant.
- **Traçabilité** : chaque objet migré garde l'ID d'origine en meta.
- **Interruption** : un script interrompu se relance simplement — il reprend là où il s'était arrêté (les éléments déjà migrés sont sautés).
- **Encodage** : connexions forcées en utf8mb4 des deux côtés.

## Correspondances de types

| Source | Destination | Transformation notable |
|---|---|---|
| `categories` | termes `metier` | parent/enfant respecté ; icône, image, prix, SEO → term meta |
| `users` | `wp_users` | rôle mappé (admin→administrator, artisan, client) ; hash bcrypt réécrit directement dans `user_pass` **après** `wp_insert_user` (sinon WP re-hasherait) ; login dérivé de l'email avec suffixe anti-collision |
| `artisans` | CPT `artisan` | description+bio+mission concaténées en contenu ; ~15 meta `_idc_*` ; catégories → termes |
| `devis` | CPT `demande_devis` | titre = référence + intitulé ; statuts conservés tels quels |
| `guides` | CPT `guide` | champs JSON (`quand_json`, `apport_json`, `questions_json`) convertis en sections HTML `<h2>+<ul>` avec échappement |
| `blog_posts` | articles | statut mappé ; auteur relié via la table de correspondance utilisateurs |

## Mots de passe — décision

WordPress ≥ 6.8 accepte nativement les hashs bcrypt `$2y$` (vérification via `password_verify`). Les hashs historiques (bcrypt cost 12) ont donc été importés **tels quels** : les utilisateurs conservent leur mot de passe, sans migration progressive ni réinitialisation forcée. Vérifié par une connexion réelle. Si un hash non-bcrypt apparaissait dans de futures données, le script l'ignore et laisse le mot de passe aléatoire généré → parcours « mot de passe oublié ».

## Rejouer la migration (données réelles avant mise en prod)

1. Sauvegarder les deux bases (voir `maintenance.md`).
2. Exécuter les scripts avec le PHP CLI de WAMP : `C:\wamp64\bin\php\php8.3.28\php.exe <script>.php`
   (scripts archivés dans la sauvegarde ; ils chargent `wp-load.php` et se paramètrent sur `info-devis.local`).
3. Contrôler les compteurs affichés puis `Info Devis → État du système`.
4. Compléter à la main ce qui n'est pas automatisé : images à la une (médiathèque), documents artisans.
