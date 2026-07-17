<?php
/**
 * data/category_icons.php
 *
 * Mapping slug catégorie → icône Font Awesome 7.
 * Utilisé pour afficher les tags spécialités sur les cartes artisans + listings.
 *
 * Usage :
 *   $icons = require BASE_PATH . '/data/category_icons.php';
 *   echo '<i class="' . ($icons[$slug] ?? 'fa-solid fa-screwdriver-wrench') . '"></i>';
 */
return [
    'plomberie'               => 'fa-solid fa-faucet-drip',
    'chauffage'               => 'fa-solid fa-fire',
    'climatisation'           => 'fa-solid fa-snowflake',
    'electricite'             => 'fa-solid fa-bolt',
    'energies-renouvelables'  => 'fa-solid fa-solar-panel',
    'isolation'               => 'fa-solid fa-shield',
    'jardinage'               => 'fa-solid fa-seedling',
    'maconnerie'              => 'fa-solid fa-trowel-bricks',
    'menuiserie'              => 'fa-solid fa-hammer',
    'peinture'                => 'fa-solid fa-paint-roller',
    'renovation'              => 'fa-solid fa-house-chimney',
    'securite-domotique'      => 'fa-solid fa-shield-halved',
    'services-b2b'            => 'fa-solid fa-briefcase',
    'toiture'                 => 'fa-solid fa-house-chimney-crack',
    'traitement-protection'   => 'fa-solid fa-spray-can-sparkles',
    'carrelage'               => 'fa-solid fa-grip',
    'amenagements-exterieurs' => 'fa-solid fa-tree',
    'demenagement-services'   => 'fa-solid fa-truck',
];
