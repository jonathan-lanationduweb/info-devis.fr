<?php
/**
 * Template Name: Espace artisan — Messages
 * Messagerie artisan ↔ clients (fils liés aux opportunités/demandes).
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = function_exists('idc_current_artisan_fiche') ? idc_current_artisan_fiche() : null;
$idv_threads = ($idv_fiche && function_exists('idc_msg_threads_for_artisan'))
    ? idc_msg_threads_for_artisan($idv_fiche, $idv_user->ID)
    : [];

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);
get_template_part('template-parts/messagerie', null, [
    'role'    => 'artisan',
    'threads' => $idv_threads,
    'user'    => $idv_user,
]);
get_footer();
