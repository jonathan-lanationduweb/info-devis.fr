<?php
require_once BASE_PATH . '/controllers/BaseController.php';
require_once BASE_PATH . '/models/CategoryModel.php';
require_once BASE_PATH . '/services/SeoService.php';

class CategoryController extends BaseController
{

    private CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    public function index(): void
    {
        $categories = $this->categoryModel->getMainCategories();
        $this->view('categories/index', [
            'pageTitle'  => 'Toutes les catégories de travaux | InfoDevis',
            'metaDesc'   => 'Trouvez un artisan qualifié dans toutes les catégories : plomberie, électricité, peinture, toiture et plus encore.',
            'categories' => $categories,
        ]);
    }

    public function show(string $slug): void
    {
        $category = $this->categoryModel->findBySlug($slug);
        if (!$category) {
            http_response_code(404);
            require VIEWS_PATH . '/layout/404.php';
            return;
        }

        $subCategories  = $this->categoryModel->getSubCategories($category['id']);
        $services       = $this->categoryModel->getServices($category['id']);
        $images         = $this->categoryModel->getImages($category['id']);
        $artisanCount   = $this->categoryModel->getArtisanCount($category['id']);
        $topArtisans    = $this->getTopArtisansForCat($category['id']);
        $seoPage        = SeoService::getPage('category', $category['id']);
        $relatedCats    = $this->getRelated($category['id']);
        $faq            = $seoPage ? json_decode($seoPage['faq'] ?? '[]', true) : [];

        $data = [
            'pageTitle'     => $category['meta_title'] ?? 'Devis ' . $category['name'] . ' | InfoDevis',
            'metaDesc'      => $category['meta_description'] ?? '',
            'category'      => $category,
            'subCategories' => $subCategories,
            'services'      => $services,
            'images'        => $images,
            'artisanCount'  => $artisanCount,
            'topArtisans'   => $topArtisans,
            'relatedCats'   => $relatedCats,
            'faq'           => $faq,
            'seoPage'       => $seoPage,
        ];

        // Cherche une vue spécifique au slug, sinon fallback sur show générique
        $slugView = 'categories/slugs/' . $slug;
        if (file_exists(BASE_PATH . '/views/' . $slugView . '.php')) {
            $this->view($slugView, $data);
        } else {
            $this->view('categories/show', $data);
        }
    }

    private function getTopArtisansForCat(int $catId): array
    {
        return Database::fetchAll(
            'SELECT a.*, u.first_name, u.last_name, u.avatar
             FROM artisans a
             JOIN users u ON u.id = a.user_id
             JOIN artisan_categories ac ON ac.artisan_id = a.id
             WHERE ac.category_id = ? AND a.is_verified = 1
             ORDER BY a.matching_score DESC LIMIT 3',
            [$catId]
        );
    }

    private function getRelated(int $catId): array
    {
        return Database::fetchAll(
            'SELECT * FROM categories WHERE id != ? AND parent_id IS NULL AND is_active = 1
             ORDER BY RAND() LIMIT 4',
            [$catId]
        );
    }
}
