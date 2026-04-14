<?php
$isLogged   = !empty($_SESSION['user_id']);
$role       = $_SESSION['user_role'] ?? '';
$dashUrl    = match ($role) {
    'admin'   => APP_URL . '/admin',
    'artisan' => APP_URL . '/dashboard/artisan',
    default   => APP_URL . '/dashboard/client',
};
$currentUri = '/' . trim(str_replace('/info-devis', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');

// ── Fonction déclarée une seule fois ─────────────────────────
if (!function_exists('navLink')) {
    function navLink(string $href, string $label, string $currentUri): string
    {
        $path     = '/' . trim(str_replace(APP_URL, '', $href), '/');
        $isActive = ($path !== '/' && str_starts_with($currentUri, $path));
        $classes  = $isActive ? 'nav-link nav-link--active' : 'nav-link';
        return '<a href="' . $href . '" class="' . $classes . '">' . $label . '</a>';
    }
}
?>
<style>
    .nav-link {
        position: relative;
        font-size: .875rem;
        font-weight: 500;
        color: rgba(47, 51, 51, .7);
        transition: color .22s;
        padding-bottom: 3px;
        text-decoration: none;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: #0e6c48;
        border-radius: 2px;
        transition: width .28s cubic-bezier(.4, 0, .2, 1);
    }

    .nav-link:hover {
        color: #0e6c48;
    }

    .nav-link:hover::after {
        width: 100%;
    }

    .nav-link--active {
        color: #0e6c48 !important;
        font-weight: 600;
    }

    .nav-link--active::after {
        width: 100% !important;
    }
</style>

<nav class="fixed top-0 w-full z-50 bg-white/90 backdrop-blur-md border-b border-gray-100" id="site-header">
    <div class="flex justify-between items-center px-8 py-4 w-full max-w-screen-2xl mx-auto">

        <div class="flex items-center gap-10">
            <a href="<?= APP_URL ?>" class="font-headline text-2xl font-bold italic text-primary">
                Info<span class="text-on-background">-Devis</span>
            </a>
            <div class="hidden md:flex items-center gap-7">
                <?= navLink(APP_URL . '/categories',  'Métiers',           $currentUri) ?>
                <?= navLink(APP_URL . '/guides-prix', 'Guides &amp; Prix', $currentUri) ?>
                <?= navLink(APP_URL . '/tarifs-pro',  'Tarifs Pro',        $currentUri) ?>
                <?= navLink(APP_URL . '/blog',        'Blog',              $currentUri) ?>
                <?= navLink(APP_URL . '/contact',     'Contact',           $currentUri) ?>
            </div>
        </div>

        <div class="hidden md:flex items-center gap-4">
            <?php if ($isLogged): ?>
                <a href="<?= $dashUrl ?>" class="nav-link">Mon espace</a>
                <a href="<?= APP_URL ?>/deconnexion" class="nav-link">Déconnexion</a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/connexion" class="nav-link">Se connecter</a>
                <a href="<?= APP_URL ?>/inscription" class="nav-link">S'inscrire</a>
                <a href="<?= APP_URL ?>/inscription?type=artisan" class="nav-link">Espace Pro</a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/devis"
                class="bg-primary text-white px-5 py-2.5 rounded font-semibold text-sm hover:opacity-90 transition-all">
                Demander un devis
            </a>
        </div>

        <button class="md:hidden" id="hamburger" aria-label="Menu">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="3" y1="6" x2="21" y2="6" />
                <line x1="3" y1="12" x2="21" y2="12" />
                <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
        </button>
    </div>

    <!-- Mobile -->
    <div class="md:hidden hidden flex-col gap-1 px-6 pb-5 bg-white border-t border-gray-100" id="mobile-nav">
        <?php
        $mobileLinks = [
            [APP_URL . '/categories',  'Métiers'],
            [APP_URL . '/guides-prix', 'Guides &amp; Prix'],
            [APP_URL . '/tarifs-pro',  'Tarifs Pro'],
            [APP_URL . '/blog',        'Blog'],
            [APP_URL . '/contact',     'Contact'],
        ];
        foreach ($mobileLinks as [$href, $label]):
            $path   = '/' . trim(str_replace('/info-devis', '', parse_url($href, PHP_URL_PATH)), '/');
            $active = ($path !== '/' && str_starts_with($currentUri, $path));
        ?>
            <a href="<?= $href ?>"
                class="py-3 border-b border-gray-100 font-medium text-sm <?= $active ? 'text-primary font-semibold' : 'text-on-background' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
        <?php if ($isLogged): ?>
            <a href="<?= $dashUrl ?>" class="text-on-background py-3 border-b border-gray-100 font-medium text-sm">Mon espace</a>
            <a href="<?= APP_URL ?>/deconnexion" class="text-on-background py-3 border-b border-gray-100 font-medium text-sm">Déconnexion</a>
        <?php else: ?>
            <a href="<?= APP_URL ?>/connexion" class="text-on-background py-3 border-b border-gray-100 font-medium text-sm">Se connecter</a>
            <a href="<?= APP_URL ?>/inscription" class="text-on-background py-3 border-b border-gray-100 font-medium text-sm">S'inscrire</a>
            <a href="<?= APP_URL ?>/inscription?type=artisan" class="text-on-background py-3 border-b border-gray-100 font-medium text-sm">Espace Pro</a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/devis" class="mt-3 bg-primary text-white text-center py-3 rounded font-semibold text-sm block">
            Demander un devis
        </a>
    </div>
</nav>

<script>
    document.getElementById('hamburger').addEventListener('click', function() {
        var m = document.getElementById('mobile-nav');
        m.classList.toggle('hidden');
        m.classList.toggle('flex');
    });
</script>