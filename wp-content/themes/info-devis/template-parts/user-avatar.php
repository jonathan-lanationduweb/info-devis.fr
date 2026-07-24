<?php
/**
 * Pastille « connecté » avec menu déroulant.
 * Clic sur les initiales → menu : Mon espace + Déconnexion.
 * Indicateur visuel de connexion. Ne s'affiche que si connecté.
 * Utilise <details> (natif, sans dépendance JS).
 */

if (!is_user_logged_in()) {
    return;
}

$idv_u    = wp_get_current_user();
$idv_name = trim($idv_u->display_name) ?: $idv_u->user_login;

// Initiales : 1re lettre des 2 premiers mots (sinon 1re lettre du nom).
$idv_initials = '';
foreach (preg_split('/\s+/', $idv_name) as $idv_word) {
    if ($idv_word !== '') {
        $idv_initials .= mb_strtoupper(mb_substr($idv_word, 0, 1));
    }
    if (mb_strlen($idv_initials) >= 2) {
        break;
    }
}
if ($idv_initials === '') {
    $idv_initials = mb_strtoupper(mb_substr($idv_name, 0, 1));
}

$idv_dash   = function_exists('idv_dashboard_url') ? idv_dashboard_url() : home_url('/');
$idv_logout = wp_logout_url(home_url('/'));
?>
<details class="idv-avatar-menu relative shrink-0">
  <summary class="idv-tap inline-flex items-center justify-center w-9 h-9 rounded-full bg-primary text-white text-[11px] font-bold tracking-wide shadow-sm hover:opacity-90 transition-opacity cursor-pointer select-none"
           title="<?php echo esc_attr('Mon compte — ' . $idv_name); ?>"
           aria-label="<?php echo esc_attr('Menu du compte : ' . $idv_name); ?>">
    <?php echo esc_html($idv_initials); ?>
  </summary>
  <div class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
    <div class="px-4 py-2 border-b border-gray-100">
      <p class="text-sm font-semibold text-on-background truncate"><?php echo esc_html($idv_name); ?></p>
      <p class="text-[11px] text-on-surface-variant uppercase tracking-wider">Connecté</p>
    </div>
    <a href="<?php echo esc_url($idv_dash); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-on-background hover:bg-gray-50 transition-colors">
      <span class="material-symbols-outlined text-[18px]">dashboard</span> Mon espace
    </a>
    <a href="<?php echo esc_url($idv_logout); ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
      <span class="material-symbols-outlined text-[18px]">logout</span> Déconnexion
    </a>
  </div>
</details>
