<?php
/**
 * Espace membre — aiguillage pur (équivalent /dashboard de l'original) :
 * déconnecté → connexion ; client → espace client ; artisan → espace
 * artisan ; admin/gestionnaire → administration. Aucun contenu propre.
 */

if (!is_user_logged_in()) {
    wp_safe_redirect(home_url('/connexion/'));
    exit;
}
wp_safe_redirect(idv_dashboard_url());
exit;
