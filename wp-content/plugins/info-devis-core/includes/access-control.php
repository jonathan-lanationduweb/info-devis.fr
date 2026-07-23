<?php
/**
 * Info Devis Core — contrôle d'accès (fichier de sécurité isolé).
 *
 * Regroupe, en un seul endroit, les règles d'accès du site :
 *   1. Barre d'admin masquée sur le front pour les clients et artisans.
 *   2. wp-admin réservé aux administrateurs et à l'équipe (capacité idc_manage) ;
 *      les autres rôles (client, artisan, abonné) sont renvoyés vers leur espace.
 *   3. Connexion uniquement via /connexion/ : l'accès direct à wp-login.php est
 *      redirigé — SAUF déconnexion, mot de passe oublié, réinitialisation et la
 *      soumission POST du formulaire (sans quoi la connexion ne fonctionnerait plus).
 *
 * Dépendance : la page /connexion/ doit exister (page de login du site).
 * Réversible : ce sont des redirections, rien n'est supprimé. Compatible avec
 * l'échappatoire ?classic=1 gérée par le plugin infodevis-admin (les gestionnaires
 * conservent l'accès).
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ── 1. Barre d'admin masquée pour les clients & artisans ──────────────── */
add_filter('show_admin_bar', static function ($show) {
    $user = wp_get_current_user();
    if ($user->exists() && array_intersect(['client', 'artisan'], (array) $user->roles)) {
        return false;
    }
    return $show;
});

/* ── Aides internes ─────────────────────────────────────────────────────── */

/** URL de l'espace approprié selon le rôle (fallback : accueil). */
function idc_ac_role_home_url(): string
{
    $user = wp_get_current_user();
    if ($user->exists()) {
        $roles = (array) $user->roles;
        if (in_array('artisan', $roles, true)) {
            return home_url('/dashboard/artisan/');
        }
        if (in_array('client', $roles, true)) {
            return home_url('/dashboard/client/');
        }
    }
    return home_url('/');
}

/** L'utilisateur courant a-t-il un accès légitime à wp-admin ? */
function idc_ac_can_use_wp_admin(): bool
{
    return current_user_can('manage_options') || current_user_can('idc_manage');
}

/* ── 2. wp-admin réservé aux admins / gestionnaires ────────────────────── */
add_action('admin_init', static function (): void {
    // Laisser passer les appels techniques indispensables.
    if (wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    if (idc_ac_can_use_wp_admin()) {
        return; // administrateurs + équipe : accès conservé
    }
    // Clients, artisans, abonnés… : renvoyés vers leur espace (ou la connexion).
    wp_safe_redirect(is_user_logged_in() ? idc_ac_role_home_url() : home_url('/connexion/'));
    exit;
}, 1);

/* ── 3. Connexion uniquement via /connexion/ ───────────────────────────── */

// Actions de wp-login.php toujours autorisées (sinon on casserait ces parcours).
const IDC_LOGIN_ALLOWED_ACTIONS = [
    'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp',
    'postpass', 'confirmaction', 'confirm_admin_email',
];

add_action('login_init', static function (): void {
    $action = isset($_REQUEST['action']) ? (string) $_REQUEST['action'] : '';

    // a) Soumissions POST (connexion depuis /connexion/, reset MDP) : ne pas bloquer.
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST') {
        return;
    }
    // b) Actions techniques autorisées (déconnexion, mot de passe oublié…).
    if ($action !== '' && in_array($action, IDC_LOGIN_ALLOWED_ACTIONS, true)) {
        return;
    }
    // c) Inscription → page dédiée du site.
    if ($action === 'register') {
        wp_safe_redirect(home_url('/inscription/'));
        exit;
    }
    // d) Tout le reste (affichage du formulaire wp-login) → /connexion/.
    $suffix = '';
    if (isset($_GET['redirect_to'])) {
        $suffix = '?redirect_to=' . rawurlencode(wp_unslash($_GET['redirect_to']));
    }
    wp_safe_redirect(home_url('/connexion/' . $suffix));
    exit;
});

// Les liens de connexion générés par WordPress pointent vers /connexion/.
add_filter('login_url', static function ($login_url, $redirect, $force_reauth) {
    $url = home_url('/connexion/');
    if ($redirect) {
        $url = add_query_arg('redirect_to', rawurlencode($redirect), $url);
    }
    if ($force_reauth) {
        $url = add_query_arg('reauth', '1', $url);
    }
    return $url;
}, 10, 3);

// Échec de connexion → /connexion/?erreur=1 (la page affiche le message d'erreur).
add_action('wp_login_failed', static function (): void {
    $url = home_url('/connexion/?erreur=1');
    if (!empty($_REQUEST['redirect_to'])) {
        $url = add_query_arg('redirect_to', rawurlencode((string) wp_unslash($_REQUEST['redirect_to'])), $url);
    }
    wp_safe_redirect($url);
    exit;
});

// Après déconnexion → accueil.
add_filter('logout_redirect', static function ($redirect_to, $requested, $user) {
    return home_url('/');
}, 10, 3);
