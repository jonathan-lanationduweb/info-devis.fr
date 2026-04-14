<?php
require_once BASE_PATH . '/controllers/BaseController.php';
require_once BASE_PATH . '/models/CategoryModel.php';
require_once BASE_PATH . '/models/ArtisanModel.php';
require_once BASE_PATH . '/models/DevisModel.php';

class HomeController extends BaseController
{

    private CategoryModel $categoryModel;
    private ArtisanModel  $artisanModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->artisanModel  = new ArtisanModel();
    }

    public function index(): void
    {
        $categories   = $this->categoryModel->getMainCategories();
        $topArtisans  = $this->artisanModel->getTopArtisans(6);
        $stats        = $this->getStats();

        $this->view('home/index', [
            'pageTitle'   => 'InfoDevis - Trouvez l\'artisan parfait | Devis gratuit en 24h',
            'metaDesc'    => 'Obtenez jusqu\'à 5 devis gratuits en moins de 24h de professionnels qualifiés. Plombier, électricien, peintre, maçon et plus.',
            'categories'  => $categories,
            'topArtisans' => $topArtisans,
            'stats'       => $stats,
        ]);
    }

    public function tarifs(): void
    {
        $this->view('home/tarifs', [
            'pageTitle' => 'Tarifs Pro - Abonnements pour artisans | InfoDevis',
            'plans'     => PLAN_PRICES,
        ]);
    }

    public function guides(): void
    {
        $categories = $this->categoryModel->getMainCategories();

        $this->view('home/guides', [
            'pageTitle'  => 'Guides & Prix des travaux | InfoDevis',
            'categories' => $categories,
        ]);
    }
    private function getStats(): array
    {
        $artisansCount = Database::fetch('SELECT COUNT(*) as c FROM artisans WHERE is_verified = 1')['c'] ?? 0;
        $devisCount    = Database::fetch('SELECT COUNT(*) as c FROM devis')['c'] ?? 0;
        $avisCount     = Database::fetch('SELECT COUNT(*) as c FROM avis WHERE verified = 1')['c'] ?? 0;

        return [
            'artisans'  => number_format(max($artisansCount, 12000), 0, ',', ' '),
            'devis'     => number_format($devisCount, 0, ',', ' '),
            'avis'      => number_format($avisCount, 0, ',', ' '),
            'cities'    => '500+',
        ];
    }
}
