<?php
/**
 * Menu mobile plein écran (section 21.2).
 * Ouverture/fermeture, Échap, verrou de défilement et piège de focus gérés par
 * mobile.js. Réservé au mobile (masqué ≥ 768px via mobile.css).
 */

if (!function_exists('idv_nav_links')) {
    return;
}
$idv_logged   = is_user_logged_in();
$idv_links    = idv_nav_links();
$idv_current  = '/' . trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

// Icône Material Symbols par mot-clé du libellé.
$idv_icon_for = static function (string $label): string {
    $l = function_exists('mb_strtolower') ? mb_strtolower($label) : strtolower($label);
    foreach ([
        'métier' => 'category', 'metier' => 'category', 'catégor' => 'category',
        'professionnel' => 'engineering', 'artisan' => 'engineering',
        'guide' => 'menu_book', 'tarif' => 'sell', 'blog' => 'article',
        'contact' => 'mail', 'devis' => 'request_quote',
    ] as $needle => $icon) {
        if (strpos($l, $needle) !== false) {
            return $icon;
        }
    }
    return 'chevron_right';
};
?>
<div id="idv-mmenu" class="idv-mmenu" role="dialog" aria-modal="true" aria-label="Menu principal" aria-hidden="true">
  <div class="idv-mmenu__head">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="font-headline text-2xl font-bold italic text-primary" data-mmenu-close>
      Info<span class="text-on-background">-Devis</span>
    </a>
    <button type="button" class="idv-mmenu__close" data-mmenu-close aria-label="Fermer le menu">
      <span class="material-symbols-outlined">close</span>
    </button>
  </div>

  <nav class="idv-mmenu__nav" aria-label="Navigation mobile">
    <?php
    foreach ($idv_links as [$idv_href, $idv_label]) :
        $idv_path   = '/' . trim((string) parse_url($idv_href, PHP_URL_PATH), '/');
        $idv_active = ($idv_path !== '/' && str_starts_with($idv_current, $idv_path));
    ?>
      <a href="<?php echo esc_url($idv_href); ?>"
         class="idv-mmenu__link <?php echo $idv_active ? 'idv-mmenu__link--active' : ''; ?>"
         data-mmenu-close <?php echo $idv_active ? 'aria-current="page"' : ''; ?>>
        <span class="material-symbols-outlined"><?php echo esc_html($idv_icon_for($idv_label)); ?></span>
        <span><?php echo esc_html($idv_label); ?></span>
      </a>
    <?php endforeach; ?>

    <a href="<?php echo esc_url(home_url('/favoris/')); ?>" class="idv-mmenu__link <?php echo str_starts_with($idv_current, '/favoris') ? 'idv-mmenu__link--active' : ''; ?>" data-mmenu-close>
      <span class="material-symbols-outlined">favorite</span><span>Favoris</span>
    </a>

    <?php if ($idv_logged) : ?>
      <a href="<?php echo esc_url(idv_dashboard_url()); ?>" class="idv-mmenu__link" data-mmenu-close>
        <span class="material-symbols-outlined">dashboard</span><span>Mon espace</span>
      </a>
      <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="idv-mmenu__link" data-mmenu-close>
        <span class="material-symbols-outlined">logout</span><span>Déconnexion</span>
      </a>
    <?php else : ?>
      <a href="<?php echo esc_url(home_url('/connexion/')); ?>" class="idv-mmenu__link" data-mmenu-close>
        <span class="material-symbols-outlined">login</span><span>Se connecter</span>
      </a>
      <a href="<?php echo esc_url(home_url('/inscription/')); ?>" class="idv-mmenu__link" data-mmenu-close>
        <span class="material-symbols-outlined">person_add</span><span>S'inscrire</span>
      </a>
      <a href="<?php echo esc_url(home_url('/inscription/?type=artisan')); ?>" class="idv-mmenu__link" data-mmenu-close>
        <span class="material-symbols-outlined">work</span><span>Espace Pro</span>
      </a>
    <?php endif; ?>
  </nav>

  <a href="<?php echo esc_url(home_url('/devis/')); ?>" class="idv-mmenu__cta" data-mmenu-close>Demander un devis</a>

  <div class="idv-mmenu__foot">
    <a href="<?php echo esc_url(home_url('/mentions-legales/')); ?>" data-mmenu-close>Mentions légales</a>
    <a href="<?php echo esc_url(home_url('/confidentialite/')); ?>" data-mmenu-close>Confidentialité</a>
    <a href="<?php echo esc_url(home_url('/contact/')); ?>" data-mmenu-close>Contact</a>
  </div>
</div>
