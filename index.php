<?php

/**
 * Point d'entrée principal — InfoDevis.fr
 * Routeur MVC
 */

define('ROOT', __DIR__);

// ── Composer autoload (SDK Mailtrap, etc.) ────────────────────
if (file_exists(ROOT . '/vendor/autoload.php')) {
    require_once ROOT . '/vendor/autoload.php';
}

// ── Autoload config ───────────────────────────────────────────
require_once ROOT . '/config/app.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/config/security.php';

// ── Headers sécurité ─────────────────────────────────────────
Security::setSecurityHeaders();

// ── Session ───────────────────────────────────────────────────
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// ── Autoload controllers / models / services / helpers ────────
spl_autoload_register(function (string $class): void {
    $dirs = [
        ROOT . '/controllers/',
        ROOT . '/models/',
        ROOT . '/services/',
        ROOT . '/helpers/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ── Routing ───────────────────────────────────────────────────
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = '/' . trim(str_replace('/info-devis', '', $uri), '/');
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    // Pages publiques
    'GET /'                       => ['HomeController',     'index'],
    'GET /categories'             => ['CategoryController', 'index'],
    'GET /categorie/{slug}'       => ['CategoryController', 'show'],
    'GET /service/{slug}'         => ['ServiceController',  'show'],
    'GET /artisans'               => ['ArtisanController',  'index'],
    'GET /artisan/{id}'           => ['ArtisanController',  'show'],
    'GET /devis'                  => ['DevisController',    'form'],
    'POST /devis'                 => ['DevisController',    'create'],
    'GET /devis/confirmation'     => ['DevisController',    'confirmation'],
    'GET /blog'                   => ['BlogController',     'index'],
    'GET /blog/{slug}'            => ['BlogController',     'show'],
    'GET /contact'                => ['ContactController',  'index'],
    'POST /contact'               => ['ContactController',  'send'],
    'GET /tarifs-pro'             => ['HomeController',     'tarifs'],
    'GET /guides-prix'            => ['HomeController',     'guides'],

    // Auth
    'GET /inscription'            => ['AuthController',     'registerForm'],
    'POST /inscription'           => ['AuthController',     'register'],
    'GET /connexion'              => ['AuthController',     'loginForm'],
    'POST /connexion'             => ['AuthController',     'login'],
    'GET /deconnexion'            => ['AuthController',     'logout'],
    'GET /mot-de-passe-oublie'    => ['AuthController',     'forgotForm'],
    'POST /mot-de-passe-oublie'   => ['AuthController',     'forgot'],
    'GET /reset-password'         => ['AuthController',     'resetForm'],
    'POST /reset-password'        => ['AuthController',     'reset'],
    'GET /verifier-email'         => ['AuthController',     'verifyEmail'],

    // Dashboard artisan
    'GET /dashboard/artisan'      => ['DashboardArtisanController', 'index'],
    'GET /dashboard/artisan/leads' => ['DashboardArtisanController', 'leads'],
    'POST /dashboard/artisan/lead/respond' => ['DashboardArtisanController', 'respondLead'],
    'GET /dashboard/artisan/messages'  => ['DashboardArtisanController', 'messages'],
    'GET /dashboard/artisan/profile'   => ['DashboardArtisanController', 'profile'],
    'POST /dashboard/artisan/profile'  => ['DashboardArtisanController', 'updateProfile'],
    'GET /dashboard/artisan/stats'     => ['DashboardArtisanController', 'stats'],
    'GET /dashboard/artisan/calendar'  => ['DashboardArtisanController', 'calendar'],
    'POST /dashboard/artisan/calendar' => ['DashboardArtisanController', 'saveAvailability'],
    'GET /dashboard/artisan/abonnement' => ['DashboardArtisanController', 'abonnement'],
    'GET /dashboard/artisan/documents' => ['DashboardArtisanController', 'documents'],
    'POST /dashboard/artisan/documents' => ['DashboardArtisanController', 'uploadDocument'],

    // Dashboard client
    'GET /dashboard/client'            => ['DashboardClientController', 'index'],
    'GET /dashboard/client/devis'      => ['DashboardClientController', 'devis'],
    'GET /dashboard/client/messages'   => ['DashboardClientController', 'messages'],
    'GET /dashboard/client/avis'       => ['DashboardClientController', 'avis'],
    'POST /dashboard/client/avis'      => ['DashboardClientController', 'createAvis'],
    'GET /dashboard/client/signature/{id}' => ['DashboardClientController', 'signature'],
    'POST /dashboard/client/signature' => ['DashboardClientController', 'sign'],
    'GET /dashboard/client/paiement/{id}'  => ['DashboardClientController', 'paiement'],

    // Dashboard admin
    'GET /admin'                        => ['AdminController', 'index'],
    'GET /admin/artisans'               => ['AdminController', 'artisans'],
    'GET /admin/artisan/{id}'           => ['AdminController', 'artisanDetail'],    // ← NOUVEAU
    'POST /admin/artisan/validate'      => ['AdminController', 'validateArtisan'],
    'POST /admin/artisan/note'          => ['AdminController', 'artisanNote'],      // ← NOUVEAU
    'GET /admin/document/{id}'          => ['AdminController', 'documentView'],     // ← NOUVEAU
    'POST /admin/document/validate'     => ['AdminController', 'documentValidate'], // ← NOUVEAU
    'GET /admin/devis'                  => ['AdminController', 'devis'],
    'GET /admin/users'                  => ['AdminController', 'users'],
    'GET /admin/blog'                   => ['AdminController', 'blog'],
    'POST /admin/blog/validate'         => ['AdminController', 'validateBlog'],
    'GET /admin/paiements'              => ['AdminController', 'paiements'],
    'GET /admin/abonnements'            => ['AdminController', 'abonnements'],
    'GET /admin/chatbot'                => ['AdminController', 'chatbot'],
    'GET /admin/metrics'                => ['AdminController', 'metrics'],
    'GET /admin/categories'             => ['AdminController', 'categories'],
    'POST /admin/categories/save'       => ['AdminController', 'saveCategory'],     // ← NOUVEAU
    'POST /admin/categories/delete'     => ['AdminController', 'deleteCategory'],

    // API
    'POST /api/auth/login'             => ['ApiAuthController',    'login'],
    'POST /api/auth/register'          => ['ApiAuthController',    'register'],
    'GET /api/devis'                   => ['ApiDevisController',   'index'],
    'POST /api/devis'                  => ['ApiDevisController',   'create'],
    'GET /api/artisans'                => ['ApiArtisansController', 'index'],
    'GET /api/artisans/{id}'           => ['ApiArtisansController', 'show'],
    'POST /api/messages'               => ['ApiMessagesController', 'send'],
    'GET /api/messages/{leadId}'       => ['ApiMessagesController', 'get'],
    'POST /api/payments/create-intent' => ['ApiPaymentsController', 'createIntent'],
    'POST /api/payments/webhook'       => ['ApiPaymentsController', 'webhook'],
    'POST /api/chatbot'                => ['ChatbotController',    'handle'],
    'GET /api/categories'              => ['ApiCategoryController', 'index'],

    // Utilitaires
    'GET /sitemap.xml'                 => ['SeoController', 'sitemap'],
    'GET /robots.txt'                  => ['SeoController', 'robots'],
];

// ── Résolution de route ───────────────────────────────────────
function matchRoute(array $routes, string $method, string $uri): ?array
{
    $key = "{$method} {$uri}";
    if (isset($routes[$key])) return $routes[$key] + ['params' => []];

    foreach ($routes as $pattern => $handler) {
        [$routeMethod, $routePath] = explode(' ', $pattern, 2);
        if ($routeMethod !== $method) continue;
        $regex = preg_replace('/\{[a-z_]+\}/', '([^/]+)', $routePath);
        if (preg_match('#^' . $regex . '$#', $uri, $matches)) {
            array_shift($matches);
            return $handler + ['params' => $matches];
        }
    }
    return null;
}

$matched = matchRoute($routes, $method, $uri);

if ($matched) {
    [$controllerName, $action, $params] = [$matched[0], $matched[1], $matched['params']];
    $file = ROOT . '/controllers/' . $controllerName . '.php';
    if (file_exists($file)) {
        require_once $file;
        $controller = new $controllerName();
        call_user_func_array([$controller, $action], $params);
    } else {
        http_response_code(500);
        die('Contrôleur introuvable : ' . $controllerName);
    }
} else {
    http_response_code(404);
    require_once ROOT . '/views/layout/404.php';
}
