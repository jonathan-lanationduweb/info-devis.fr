<?php
require_once BASE_PATH . '/controllers/BaseController.php';

class BlogController extends BaseController
{
    public function index(): void
    {
        // Essayer de récupérer depuis la BDD, sinon articles statiques
        try {
            $articles = Database::fetchAll(
                'SELECT bp.*, u.first_name, u.last_name, c.name as category_name
                 FROM blog_posts bp
                 LEFT JOIN users u ON u.id = bp.author_id
                 LEFT JOIN categories c ON c.id = bp.category_id
                 WHERE bp.status = "published"
                 ORDER BY bp.published_at DESC
                 LIMIT 20'
            );
        } catch (\Exception $e) {
            $articles = [];
        }

        // Si pas d'articles en BDD, utiliser les articles statiques
        if (empty($articles)) {
            $articles = $this->getDefaultArticles();
        }

        $this->view('blog/index', [
            'pageTitle' => 'Blog & Conseils travaux | InfoDevis',
            'metaDesc'  => 'Conseils, guides et astuces pour vos travaux.',
            'articles'  => $articles,
        ]);
    }

    public function show(string $slug): void
    {
        // Essayer BDD d'abord
        try {
            $article = Database::fetch(
                'SELECT bp.*, u.first_name, u.last_name, c.name as category_name
                 FROM blog_posts bp
                 LEFT JOIN users u ON u.id = bp.author_id
                 LEFT JOIN categories c ON c.id = bp.category_id
                 WHERE bp.slug = ? AND bp.status = "published"',
                [$slug]
            );
        } catch (\Exception $e) {
            $article = null;
        }

        // Sinon chercher dans les articles statiques
        if (!$article) {
            foreach ($this->getDefaultArticles() as $a) {
                if ($a['slug'] === $slug) {
                    $article = $a;
                    break;
                }
            }
        }

        if (!$article) {
            http_response_code(404);
            require_once BASE_PATH . '/views/layout/404.php';
            return;
        }

        // Articles liés (même catégorie)
        $related = [];
        try {
            $related = Database::fetchAll(
                'SELECT * FROM blog_posts WHERE status = "published" AND slug != ? ORDER BY published_at DESC LIMIT 3',
                [$slug]
            );
        } catch (\Exception $e) {
            // Ignore
        }
        if (empty($related)) {
            $related = array_filter($this->getDefaultArticles(), fn($a) => $a['slug'] !== $slug);
            $related = array_slice(array_values($related), 0, 3);
        }

        $this->view('blog/show', [
            'pageTitle' => ($article['title'] ?? '') . ' | InfoDevis',
            'metaDesc'  => $article['excerpt'] ?? '',
            'article'   => $article,
            'related'   => $related,
        ]);
    }

    // Articles par défaut si la BDD est vide
    private function getDefaultArticles(): array
    {
        return [
            [
                'id' => 1,
                'slug' => 'combien-coute-refaire-toiture',
                'title' => 'Combien coûte une réfection de toiture ?',
                'excerpt' => 'Découvrez les facteurs qui influencent le coût d\'une réfection de toiture et comment obtenir le meilleur devis.',
                'content' => '<p>La réfection de toiture est un chantier important qui dépend de nombreux facteurs : la surface du toit, le type de tuiles, la pente, l\'accessibilité et l\'état de la charpente.</p><p>Pour obtenir un devis précis, il est indispensable de faire appel à un couvreur qualifié qui pourra évaluer l\'état exact de votre toiture.</p>',
                'category' => 'Toiture',
                'category_name' => 'Toiture',
                'icon' => '🏠',
                'read_time' => '5 min',
                'created_at' => '2025-11-15',
                'published_at' => '2025-11-15',
                'first_name' => 'Équipe',
                'last_name' => 'InfoDevis',
            ],
            [
                'id' => 2,
                'slug' => 'prix-installation-pompe-chaleur',
                'title' => 'Pompe à chaleur : ce qu\'il faut savoir',
                'excerpt' => 'Tout comprendre sur l\'installation d\'une pompe à chaleur, les aides disponibles et les points à vérifier.',
                'content' => '<p>La pompe à chaleur est une solution de chauffage efficace mais qui nécessite une étude préalable. La puissance nécessaire dépend de la surface et de l\'isolation de votre logement.</p><p>Des aides comme MaPrimeRénov\' peuvent financer une partie de l\'installation. Faites-vous accompagner par un professionnel RGE.</p>',
                'category' => 'Chauffage',
                'category_name' => 'Chauffage',
                'icon' => '🔥',
                'read_time' => '7 min',
                'created_at' => '2025-11-10',
                'published_at' => '2025-11-10',
                'first_name' => 'Équipe',
                'last_name' => 'InfoDevis',
            ],
            [
                'id' => 3,
                'slug' => 'fissure-mur-que-faire',
                'title' => 'Fissure dans un mur : que faire ?',
                'excerpt' => 'Comment diagnostiquer une fissure et savoir si elle est dangereuse.',
                'content' => '<p>Toutes les fissures ne sont pas dangereuses. Une fissure capillaire (moins de 2mm) est souvent esthétique. Une fissure structurelle nécessite l\'intervention d\'un professionnel.</p><p>En cas de doute, faites appel à un maçon ou un expert en bâtiment pour un diagnostic.</p>',
                'category' => 'Maçonnerie',
                'category_name' => 'Maçonnerie',
                'icon' => '⬛',
                'read_time' => '6 min',
                'created_at' => '2025-11-05',
                'published_at' => '2025-11-05',
                'first_name' => 'Équipe',
                'last_name' => 'InfoDevis',
            ],
            [
                'id' => 4,
                'slug' => 'depannage-plomberie-urgence',
                'title' => 'Fuite d\'eau : les bons gestes',
                'excerpt' => 'En cas de fuite, les bons réflexes pour limiter les dégâts.',
                'content' => '<p>En cas de fuite d\'eau, la première chose à faire est de couper l\'arrivée d\'eau générale. Ensuite, appelez un plombier qualifié en urgence.</p><p>Photographiez les dégâts pour votre assurance et notez l\'heure de découverte.</p>',
                'category' => 'Plomberie',
                'category_name' => 'Plomberie',
                'icon' => '🔧',
                'read_time' => '4 min',
                'created_at' => '2025-10-28',
                'published_at' => '2025-10-28',
                'first_name' => 'Équipe',
                'last_name' => 'InfoDevis',
            ],
            [
                'id' => 5,
                'slug' => 'isolation-combles-prix',
                'title' => 'Isolation des combles : guide complet',
                'excerpt' => 'Matériaux, techniques et aides pour isoler vos combles.',
                'content' => '<p>L\'isolation des combles est l\'un des travaux les plus rentables en rénovation énergétique. Ouate de cellulose, laine de verre ou laine de roche : chaque matériau a ses avantages.</p><p>Les aides CEE et MaPrimeRénov\' peuvent couvrir une grande partie du coût.</p>',
                'category' => 'Isolation',
                'category_name' => 'Isolation',
                'icon' => '🧱',
                'read_time' => '8 min',
                'created_at' => '2025-10-20',
                'published_at' => '2025-10-20',
                'first_name' => 'Équipe',
                'last_name' => 'InfoDevis',
            ],
            [
                'id' => 6,
                'slug' => 'renovation-salle-de-bain-budget',
                'title' => 'Rénovation salle de bain : guide complet',
                'excerpt' => 'Les postes à prévoir et les conseils pour bien gérer votre budget.',
                'content' => '<p>Rénover une salle de bain implique plusieurs corps de métier : plombier, carreleur, électricien. Planifier les interventions dans le bon ordre est essentiel.</p><p>Pensez à prévoir une réserve de budget pour les imprévus (tuyauteries vétustes, humidité cachée).</p>',
                'category' => 'Rénovation',
                'category_name' => 'Rénovation',
                'icon' => '🏗️',
                'read_time' => '6 min',
                'created_at' => '2025-10-12',
                'published_at' => '2025-10-12',
                'first_name' => 'Équipe',
                'last_name' => 'InfoDevis',
            ],
        ];
    }
}
