<?php
/**
 * Template Name: Espace client — Messages
 * Messagerie client ↔ artisans (fils liés aux demandes de devis).
 */

$idv_user    = idv_require_role('client');
$idv_threads = function_exists('idc_msg_threads_for_client') ? idc_msg_threads_for_client($idv_user) : [];

get_header();
get_template_part('template-parts/sidebar', 'client', ['user' => $idv_user]);
get_template_part('template-parts/messagerie', null, [
    'role'    => 'client',
    'threads' => $idv_threads,
    'user'    => $idv_user,
]);
get_footer();
