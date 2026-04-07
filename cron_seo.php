#!/usr/bin/env php
<?php
/**
 * Cron SEO — Génération automatique pages + sitemap
 * Cron : 0 2 * * * php /path/info-devis/cron_seo.php
 */
define('ROOT', __DIR__);
require_once ROOT . '/config/app.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/config/security.php';
require_once ROOT . '/services/SeoService.php';

echo '[' . date('Y-m-d H:i:s') . '] Génération SEO...' . PHP_EOL;

// Générer pages pour toutes les catégories
$categories = Database::fetchAll('SELECT * FROM categories WHERE is_active = 1');
foreach ($categories as $cat) {
    SeoService::generateCategoryPage($cat);
    echo '  ✅ ' . $cat['name'] . PHP_EOL;
}

// Générer sitemap
$sitemap = SeoService::generateSitemap();
file_put_contents(ROOT . '/sitemap.xml', $sitemap);
echo '  ✅ sitemap.xml généré (' . strlen($sitemap) . ' octets)' . PHP_EOL;

// Générer robots.txt
$robots = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /dashboard\nDisallow: /api\nSitemap: " . APP_URL . "/sitemap.xml\n";
file_put_contents(ROOT . '/robots.txt', $robots);
echo '  ✅ robots.txt généré' . PHP_EOL;

// Mettre à jour métriques business du jour
$today  = date('Y-m-d');
$revenue = Database::fetch("SELECT COALESCE(SUM(amount),0) as s FROM paiements WHERE status='paid' AND DATE(created_at)=?", [$today])['s'] ?? 0;
$leads   = Database::fetch("SELECT COUNT(*) as c FROM devis WHERE DATE(created_at)=?", [$today])['c'] ?? 0;
$accepted= Database::fetch("SELECT COUNT(*) as c FROM leads WHERE status='accepted' AND DATE(created_at)=?", [$today])['c'] ?? 0;
$artisans= Database::fetch("SELECT COUNT(*) as c FROM artisans WHERE is_verified=1")['c'] ?? 0;
$conv    = $leads > 0 ? round($accepted / $leads * 100, 2) : 0;

$existing = Database::fetch('SELECT id FROM business_metrics WHERE date=?', [$today]);
if ($existing) {
    Database::update('business_metrics', ['revenue'=>$revenue,'leads_count'=>$leads,'leads_accepted'=>$accepted,'conversion_rate'=>$conv,'active_artisans'=>$artisans], ['date'=>$today]);
} else {
    Database::insert('business_metrics', ['date'=>$today,'revenue'=>$revenue,'leads_count'=>$leads,'leads_accepted'=>$accepted,'conversion_rate'=>$conv,'active_artisans'=>$artisans]);
}

echo '[' . date('H:i:s') . '] Terminé.' . PHP_EOL;
