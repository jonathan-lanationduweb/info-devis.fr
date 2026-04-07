<?php
/**
 * Service SEO — génération automatique de pages, sitemap, meta
 */

class SeoService {

    public static function getPage(string $entityType, int $entityId): ?array {
        return Database::fetch(
            'SELECT * FROM seo_pages WHERE entity_type = ? AND entity_id = ?',
            [$entityType, $entityId]
        );
    }

    public static function generateCategoryPage(array $category): void {
        $slug  = '/categorie/' . $category['slug'];
        $price = $category['prix_min'] && $category['prix_max']
            ? 'à partir de ' . number_format($category['prix_min'], 0, ',', ' ') . ' €'
            : '';

        $h1   = 'Devis ' . $category['name'] . ' gratuit — Artisans qualifiés';
        $meta = 'Obtenez jusqu\'à 5 devis ' . strtolower($category['name']) . ' gratuits de professionnels certifiés. Réponse sous 24h. ' . $price . ' Sans engagement.';

        $faq = self::generateFaq($category);
        $content = self::generateContent($category);

        $existing = self::getPage('category', $category['id']);
        $data = [
            'entity_type'      => 'category',
            'entity_id'        => $category['id'],
            'slug'             => $slug,
            'meta_title'       => 'Devis ' . $category['name'] . ' | Comparez des artisans | InfoDevis',
            'meta_description' => $meta,
            'h1'               => $h1,
            'content'          => $content,
            'faq'              => json_encode($faq),
        ];

        if ($existing) {
            Database::update('seo_pages', $data, ['id' => $existing['id']]);
        } else {
            Database::insert('seo_pages', $data);
        }
    }

    private static function generateFaq(array $category): array {
        $name  = $category['name'];
        $pMin  = $category['prix_min'] ? number_format($category['prix_min'], 0, ',', ' ') . ' €' : 'variable';
        $pMax  = $category['prix_max'] ? number_format($category['prix_max'], 0, ',', ' ') . ' €' : 'variable';

        return [
            ['q' => "Combien coûte une prestation de {$name} ?",
             'a' => "Le prix d'une prestation de {$name} varie généralement entre {$pMin} et {$pMax} selon la complexité, la région et le professionnel choisi. Obtenez des devis gratuits pour comparer."],
            ['q' => "Comment trouver un bon artisan en {$name} ?",
             'a' => "Sur InfoDevis, tous les artisans sont vérifiés et certifiés. Vous pouvez consulter leurs avis, leur expérience et comparer jusqu'à 5 devis gratuits en moins de 24h."],
            ['q' => "Le service de devis est-il vraiment gratuit ?",
             'a' => "Oui, 100% gratuit et sans engagement. Vous recevez des devis d'artisans qualifiés dans votre zone et choisissez librement."],
            ['q' => "Combien de temps pour recevoir des devis {$name} ?",
             'a' => "Vous recevez vos premiers devis en moins de 24 heures après votre demande. Les artisans de votre zone sont contactés immédiatement."],
            ['q' => "Puis-je demander un devis {$name} le week-end ?",
             'a' => "Oui, notre service est disponible 7j/7 et 24h/24. Votre demande est traitée automatiquement et transmise aux artisans disponibles."],
        ];
    }

    private static function generateContent(array $category): string {
        $name = $category['name'];
        $desc = $category['description'] ?? '';
        return "<h2>Pourquoi choisir InfoDevis pour vos travaux de {$name} ?</h2>
<p>{$desc}</p>
<p>InfoDevis vous met en relation avec des artisans qualifiés, vérifiés et certifiés dans votre région. Comparez jusqu'à 5 devis gratuits et choisissez le professionnel qui correspond à votre budget et vos attentes.</p>
<h2>Comment fonctionne notre service de devis {$name} ?</h2>
<ol>
<li><strong>Décrivez votre projet</strong> en quelques secondes</li>
<li><strong>Recevez jusqu'à 5 devis</strong> d'artisans qualifiés sous 24h</li>
<li><strong>Comparez et choisissez</strong> le meilleur professionnel</li>
<li><strong>Suivez votre chantier</strong> depuis votre espace client</li>
</ol>
<h2>Artisans certifiés en {$name}</h2>
<p>Tous nos artisans sont soumis à une vérification stricte : SIRET, assurances, certifications professionnelles et avis clients vérifiés. Vous êtes protégé à chaque étape.</p>";
    }

    public static function generateSitemap(): string {
        $baseUrl = APP_URL;
        $xml     = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml    .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        // Pages statiques
        $static = ['/', '/categories', '/blog', '/contact', '/tarifs-pro', '/guides-prix'];
        foreach ($static as $path) {
            $xml .= self::urlEntry($baseUrl . $path, 'weekly', '1.0');
        }

        // Catégories
        $cats = Database::fetchAll('SELECT slug, updated_at FROM categories WHERE is_active = 1');
        foreach ($cats as $cat) {
            $xml .= self::urlEntry($baseUrl . '/categorie/' . $cat['slug'], 'weekly', '0.9', $cat['updated_at'] ?? null);
        }

        // Services
        $services = Database::fetchAll('SELECT slug, created_at FROM services WHERE is_active = 1');
        foreach ($services as $svc) {
            $xml .= self::urlEntry($baseUrl . '/service/' . $svc['slug'], 'monthly', '0.7', $svc['created_at'] ?? null);
        }

        // Blog
        $posts = Database::fetchAll('SELECT slug, updated_at FROM blog_posts WHERE status = "published"');
        foreach ($posts as $post) {
            $xml .= self::urlEntry($baseUrl . '/blog/' . $post['slug'], 'monthly', '0.6', $post['updated_at'] ?? null);
        }

        $xml .= '</urlset>';
        return $xml;
    }

    private static function urlEntry(string $loc, string $freq, string $priority, ?string $lastmod = null): string {
        $mod = $lastmod ? '<lastmod>' . date('Y-m-d', strtotime($lastmod)) . '</lastmod>' : '';
        return "  <url><loc>{$loc}</loc>{$mod}<changefreq>{$freq}</changefreq><priority>{$priority}</priority></url>" . PHP_EOL;
    }
}
