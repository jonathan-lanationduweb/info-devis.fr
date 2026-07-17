<?php
/**
 * Page d'accueil administrable.
 * Les défauts reproduisent exactement le contenu actuellement codé en dur dans
 * front-page.php : tant que rien n'est modifié dans InfoDevis Admin, le rendu
 * du site est strictement identique.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Schéma + valeurs par défaut des sections de la page d'accueil. */
function ida_home_defaults(): array
{
    $theme_uri = get_template_directory_uri();

    return [
        'hero' => [
            'visible'        => true,
            'title'          => "Trouvez l'artisan parfait pour vos",
            'title_accent'   => 'travaux',
            'subtitle'       => '',
            'image'          => $theme_uri . '/assets/images/artisan.png',
            'image_mobile'   => '',
            'overlay'        => 50,          // % de voile noir
            'height'         => '870',       // min-height px
            'show_search'    => true,
            'btn1_label'     => 'Chercher',
            'btn1_url'       => '/devis/',
        ],
        'reassurance' => [
            'visible' => true,
            'items'   => ['100% GRATUIT', 'RÉPONSE RAPIDE', 'ARTISANS CERTIFIÉS'],
        ],
        'expertises' => [
            'visible'  => true,
            'kicker'   => 'Services Professionnels',
            'title'    => 'Nos expertises',
            'link'     => 'Voir tous les métiers',
        ],
        'etapes' => [
            'visible' => true,
            'title'   => 'Comment ça marche',
            'intro'   => 'Trois étapes simples pour concrétiser vos projets de rénovation avec sérénité.',
            'steps'   => [
                ['title' => 'Décrivez votre projet', 'desc' => 'Remplissez notre formulaire en 2 minutes pour détailler vos besoins spécifiques.'],
                ['title' => 'Recevez des devis', 'desc' => "Jusqu'à 5 artisans qualifiés vous contactent pour proposer leurs services."],
                ['title' => 'Choisissez & Réalisez', 'desc' => "Comparez les offres et sélectionnez l'artisan qui vous correspond le mieux."],
            ],
        ],
        'professionnels' => [
            'visible' => true,
            'title'   => 'Découvrez nos',
            'accent'  => 'professionnels',
            'intro'   => 'Des artisans certifiés, vérifiés et notés. Consultez leur portfolio et faites votre choix.',
        ],
        'realisations' => [
            'visible' => true,
            'title'   => 'Dernières',
            'accent'  => 'réalisations',
            'intro'   => "L'inspiration au quotidien. Découvrez les projets récents de nos artisans.",
        ],
        'citation' => [
            'visible' => true,
            'texte'   => "La qualité d'un ouvrage ne réside pas seulement dans les matériaux utilisés, mais dans l'intention et la précision de la main qui les façonne.",
            'auteur'  => "L'équipe InfoDevis",
        ],
        'cta' => [
            'visible'    => true,
            'title'      => 'Prêt à lancer vos travaux ?',
            'intro'      => "Rejoignez des milliers de particuliers qui font confiance à notre réseau d'artisans certifiés.",
            'btn1_label' => 'Demander mon devis gratuit',
            'btn1_url'   => '/devis/',
            'btn2_label' => 'Consulter les tarifs',
            'btn2_url'   => '/tarifs-pro/',
        ],
    ];
}

/** Section de l'accueil : valeurs enregistrées fusionnées avec les défauts. */
function ida_home_get(string $section): array
{
    $defaults = ida_home_defaults();
    if (!isset($defaults[$section])) {
        return [];
    }
    $saved = get_option('idv_home_' . $section, []);
    return is_array($saved) ? array_merge($defaults[$section], $saved) : $defaults[$section];
}

/** Toutes les sections (pour l'éditeur et l'API). */
function ida_home_all(): array
{
    $all = [];
    foreach (array_keys(ida_home_defaults()) as $key) {
        $all[$key] = ida_home_get($key);
    }
    return $all;
}

/**
 * Métiers dans l'ordre défini par l'admin (term meta _idc_order),
 * repli alphabétique. Utilisé par la nouvelle admin ET le thème.
 */
function ida_get_metiers_ordered(): array
{
    $terms = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'parent' => 0]);
    if (is_wp_error($terms)) {
        return [];
    }
    usort($terms, static function ($a, $b) {
        $oa = get_term_meta($a->term_id, '_idc_order', true);
        $ob = get_term_meta($b->term_id, '_idc_order', true);
        $oa = $oa === '' ? 9999 : (int) $oa;
        $ob = $ob === '' ? 9999 : (int) $ob;
        return $oa === $ob ? strcasecmp($a->name, $b->name) : $oa <=> $ob;
    });
    // Masquer les métiers cachés côté front.
    return array_values(array_filter($terms, static fn($t) => get_term_meta($t->term_id, '_idc_hidden', true) !== '1'));
}
