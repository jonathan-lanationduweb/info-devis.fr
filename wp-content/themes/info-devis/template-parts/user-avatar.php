<?php
/**
 * Pastille « connecté » — initiales de l'utilisateur.
 * Indicateur visuel de connexion, placé entre la cloche et le menu.
 * Cliquable → mène à l'espace de l'utilisateur. Ne s'affiche que si connecté.
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

$idv_dash = function_exists('idv_dashboard_url') ? idv_dashboard_url() : home_url('/');
?>
<a href="<?php echo esc_url($idv_dash); ?>"
   class="idv-tap inline-flex items-center justify-center w-9 h-9 rounded-full bg-primary text-white text-[11px] font-bold tracking-wide shadow-sm hover:opacity-90 transition-opacity shrink-0"
   title="<?php echo esc_attr('Mon espace — ' . $idv_name); ?>"
   aria-label="<?php echo esc_attr('Connecté : ' . $idv_name . '. Ouvrir mon espace.'); ?>">
  <?php echo esc_html($idv_initials); ?>
</a>
