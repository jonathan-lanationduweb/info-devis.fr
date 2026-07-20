<?php
/**
 * Barre de navigation inférieure mobile (section 21.2) + invite d'installation PWA.
 * Accueil / Professionnels / Favoris / Rendez-vous / Menu.
 * Réservée au mobile (masquée ≥ 768px) et jamais affichée sur les pages de
 * dashboard (qui ont leur propre navigation).
 */

$idv_roles = is_user_logged_in() ? (array) wp_get_current_user()->roles : [];

$idv_cur = '/' . trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$idv_is  = static function (string $prefix) use ($idv_cur): bool {
    return $prefix === '/' ? ($idv_cur === '/') : str_starts_with($idv_cur, $prefix);
};

// 4ᵉ entrée adaptative : « RDV » pour un connecté (page distincte), sinon
// « Devis » pour un visiteur — évite de dupliquer « Pros ».
if (in_array('artisan', $idv_roles, true)) {
    $idv_action = ['__match__', home_url('/dashboard/artisan/rdv/'), 'RDV', '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'];
} elseif (in_array('client', $idv_roles, true)) {
    $idv_action = ['__match__', home_url('/mes-rdv/'), 'RDV', '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'];
} else {
    $idv_action = ['__match__', home_url('/devis/'), 'Devis', '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h4"/>'];
}

$idv_items = [
    ['/', home_url('/'), 'Accueil', '<path d="M3 9.5 12 3l9 6.5"/><path d="M5 10v10h14V10"/>'],
    ['/professionnels', home_url('/professionnels/'), 'Pros', '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
    ['/favoris', home_url('/favoris/'), 'Favoris', '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>'],
    $idv_action,
];
?>
<nav class="idv-bottomnav" aria-label="Navigation rapide">
  <?php foreach ($idv_items as [$idv_match, $idv_url, $idv_label, $idv_svg]) :
      $idv_active = $idv_match === '__match__'
          ? ($idv_cur === rtrim((string) parse_url($idv_url, PHP_URL_PATH), '/') || $idv_cur === parse_url($idv_url, PHP_URL_PATH))
          : $idv_is($idv_match);
      $idv_is_fav = $idv_match === '/favoris';
  ?>
    <a href="<?php echo esc_url($idv_url); ?>" class="idv-bottomnav__item <?php echo $idv_active ? 'idv-bottomnav__item--active' : ''; ?>" <?php echo $idv_active ? 'aria-current="page"' : ''; ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $idv_svg; // phpcs:ignore ?></svg>
      <span><?php echo esc_html($idv_label); ?></span>
      <?php if ($idv_is_fav) : ?><span id="idv-fav-badge" class="idv-bottomnav__badge" aria-hidden="true">0</span><?php endif; ?>
    </a>
  <?php endforeach; ?>
  <button type="button" class="idv-bottomnav__item" data-open-menu aria-label="Ouvrir le menu" aria-haspopup="dialog">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    <span>Menu</span>
  </button>
</nav>

<!-- Invite d'installation PWA (section 22.4) -->
<div id="idv-install" class="idv-install" role="dialog" aria-label="Installer l'application">
  <img class="idv-install__icon" src="<?php echo esc_url(home_url('/assets/icons/icon-192x192.png')); ?>" alt="" width="40" height="40">
  <div class="idv-install__txt">
    <div class="idv-install__title">Installer InfoDevis</div>
    <div class="idv-install__sub" data-install-mode="android" style="display:none;">Accès rapide depuis votre écran d'accueil.</div>
    <div class="idv-install__sub" data-install-mode="ios" style="display:none;">Appuyez sur <strong>Partager</strong> puis <strong>« Sur l'écran d'accueil »</strong>.</div>
  </div>
  <button type="button" class="idv-install__btn" data-install-action data-install-mode="android" style="display:none;">Installer</button>
  <button type="button" class="idv-install__close" data-install-close aria-label="Fermer">&times;</button>
</div>
