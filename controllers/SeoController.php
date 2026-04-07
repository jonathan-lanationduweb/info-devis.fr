<?php
require_once BASE_PATH . '/controllers/BaseController.php';
require_once BASE_PATH . '/services/SeoService.php';

class SeoController extends BaseController {

    public function sitemap(): void {
        header('Content-Type: application/xml; charset=utf-8');
        echo SeoService::generateSitemap();
        exit;
    }

    public function robots(): void {
        header('Content-Type: text/plain');
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin\n";
        echo "Disallow: /dashboard\n";
        echo "Disallow: /api\n";
        echo "Disallow: /uploads\n";
        echo "Sitemap: " . APP_URL . "/sitemap.xml\n";
        exit;
    }
}
