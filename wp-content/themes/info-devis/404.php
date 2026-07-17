<?php
/**
 * Page 404 — d'après views/layout/404.php (bannière sombre + retour accueil).
 */

get_header();
?>
<section class="page-banner page-banner--metier" style="min-height:60vh;display:flex;align-items:center;">
  <div class="container max-w-screen-xl mx-auto px-8">
    <span class="page-banner__eyebrow">Erreur 404</span>
    <h1 class="page-banner__title">Cette page n'existe pas</h1>
    <p class="page-banner__subtitle">La page que vous cherchez a peut-être été déplacée ou supprimée.</p>
    <p style="margin-top:28px;position:relative;z-index:2;">
      <a href="<?php echo esc_url(home_url('/')); ?>"
        class="bg-primary text-white px-8 py-3 rounded-lg font-semibold text-sm hover:opacity-90 transition-all">
        Retour à l'accueil
      </a>
    </p>
  </div>
</section>
<?php
get_footer();
