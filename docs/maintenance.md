# Maintenance et restauration

## Sauvegardes

Emplacement : `C:\wamp64\backups\info-devis\<horodatage>\`

Contenu de la sauvegarde du 15/07/2026 (avant refonte majeure) :

| Fichier | Contenu |
|---|---|
| `wordpress.sql` | Base WordPress complète (mysqldump, utf8mb4) |
| `infodevis.sql` | Base historique complète |
| `site-wordpress.zip` | Arborescence `C:\wamp64\www\info-devis.local` |
| `ancienne-application.zip` | Arborescence `C:\wamp64\www\info-devis` |

Refaire une sauvegarde (PowerShell) :

```powershell
$d = "C:\wamp64\backups\info-devis\$(Get-Date -Format 'yyyyMMdd-HHmm')"
New-Item -ItemType Directory -Force $d
& C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe -u root --port=3306 --single-transaction --result-file="$d\wordpress.sql" wordpress
Compress-Archive -Path "C:\wamp64\www\info-devis.local" -DestinationPath "$d\site-wordpress.zip"
```

## Restauration

1. **Base** : `C:\wamp64\bin\mysql\mysql8.4.7\bin\mysql.exe -u root --port=3306 wordpress < wordpress.sql`
2. **Fichiers** : extraire `site-wordpress.zip` vers `C:\wamp64\www\` (remplacer).
3. Vider les caches navigateur, vérifier `Info Devis → État du système`.

## Tâches récurrentes recommandées

- Sauvegarde hebdomadaire (et systématique avant toute mise à jour).
- Mises à jour WordPress / Astra / Yoast / WP Mail SMTP depuis l'admin (le thème enfant et l'extension maison ne sont pas affectés par les mises à jour du parent).
- Purge du journal d'emails et des événements Stripe : automatique (plafonnés à 50 / 200 entrées).
- Surveillance : la page État du système signale pages manquantes, SMTP/Stripe non configurés, permaliens cassés.

## Développement

- Métier : `wp-content/plugins/info-devis-core/` (un module par domaine dans `includes/`).
- Affichage : `wp-content/themes/info-devis/`.
- Préfixes réservés : fonctions/hooks `idc_`, meta `_idc_`.
- Toujours tester avec `php -l` puis sur `http://info-devis.local` avant toute mise en production.
