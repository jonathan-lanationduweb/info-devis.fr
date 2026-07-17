<?php
/**
 * data/category_images.php
 *
 * Mapping slug → image distante (Pexels CDN, libre de droits via API officielle).
 * Images téléchargées via la skill pexels-images (rate-limited, attribution requise).
 *
 * Workflow :
 *   1. Si /assets/img/categories/{slug}/cover.jpg existe → on l'utilise (rapide, offline)
 *   2. Sinon → URL Pexels distante (immédiat, mais dépend du réseau)
 *   3. Fallback JS onerror → /assets/img/metier.png
 *
 * ATTRIBUTION OBLIGATOIRE (licence Pexels) : afficher photographer + lien Pexels
 * près de l'image ou dans le footer/mentions légales.
 */

return [
    'amenagements-exterieurs' => [
        'remote'      => 'https://images.pexels.com/photos/7587879/pexels-photo-7587879.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 7587879,
        'pexels_url'  => 'https://www.pexels.com/photo/terrace-in-house-7587879/',
        'photographer'=> 'Max Vakhtbovych',
        'photographer_url' => 'https://www.pexels.com/@artbovich',
        'alt'         => 'Terrasse extérieure aménagée',
    ],
    'carrelage' => [
        'remote'      => 'https://images.pexels.com/photos/18861859/pexels-photo-18861859.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 18861859,
        'pexels_url'  => 'https://www.pexels.com/photo/tiling-equipment-laid-out-on-a-job-18861859/',
        'photographer'=> 'Bimbim Sindu',
        'photographer_url' => 'https://www.pexels.com/@bimbim-sindu-96724782',
        'alt'         => 'Pose de carrelage en chantier',
    ],
    'chauffage' => [
        'remote'      => 'https://images.pexels.com/photos/29226620/pexels-photo-29226620.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 29226620,
        'pexels_url'  => 'https://www.pexels.com/photo/professional-plumber-installing-a-radiator-pipe-29226620/',
        'photographer'=> 'Sergei Starostin',
        'photographer_url' => 'https://www.pexels.com/@sejio402',
        'alt'         => 'Installation de radiateur de chauffage',
    ],
    'climatisation' => [
        'remote'      => 'https://images.pexels.com/photos/27134985/pexels-photo-27134985.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 27134985,
        'pexels_url'  => 'https://www.pexels.com/photo/view-of-the-air-conditioning-unit-outside-the-building-27134985/',
        'photographer'=> 'FOX',
        'photographer_url' => 'https://www.pexels.com/@fox-58267',
        'alt'         => 'Unité extérieure de climatisation',
    ],
    'demenagement-services' => [
        'remote'      => 'https://images.pexels.com/photos/7203701/pexels-photo-7203701.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 7203701,
        'pexels_url'  => 'https://www.pexels.com/photo/stack-of-carton-boxes-on-floor-in-rented-house-7203701/',
        'photographer'=> 'SHVETS production',
        'photographer_url' => 'https://www.pexels.com/@shvets-production',
        'alt'         => 'Cartons de déménagement empilés',
    ],
    'electricite' => [
        'remote'      => 'https://images.pexels.com/photos/28265032/pexels-photo-28265032.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 28265032,
        'pexels_url'  => 'https://www.pexels.com/photo/an-electrical-panel-with-wires-and-switches-28265032/',
        'photographer'=> 'ranjeet',
        'photographer_url' => 'https://www.pexels.com/@ranjeet-860714737',
        'alt'         => 'Tableau électrique avec disjoncteurs',
    ],
    'energies-renouvelables' => [
        'remote'      => 'https://images.pexels.com/photos/11645008/pexels-photo-11645008.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 11645008,
        'pexels_url'  => 'https://www.pexels.com/photo/installation-of-solar-panels-11645008/',
        'photographer'=> 'Trinh Trần',
        'photographer_url' => 'https://www.pexels.com/@trinh-tr-n-191284110',
        'alt'         => 'Installation de panneaux solaires',
    ],
    'isolation' => [
        'remote'      => 'https://images.pexels.com/photos/8082327/pexels-photo-8082327.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 8082327,
        'pexels_url'  => 'https://www.pexels.com/photo/room-on-attic-8082327/',
        'photographer'=> 'Max Vakhtbovych',
        'photographer_url' => 'https://www.pexels.com/@artbovich',
        'alt'         => 'Combles aménagés et isolés',
    ],
    'jardinage' => [
        'remote'      => 'https://images.pexels.com/photos/27947714/pexels-photo-27947714.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 27947714,
        'pexels_url'  => 'https://www.pexels.com/photo/gruner-garten-in-der-orangerie-in-potsdam-27947714/',
        'photographer'=> 'Tim Heckmann',
        'photographer_url' => 'https://www.pexels.com/@lonnyphotography',
        'alt'         => 'Jardin verdoyant entretenu',
    ],
    'maconnerie' => [
        'remote'      => 'https://images.pexels.com/photos/19688828/pexels-photo-19688828.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 19688828,
        'pexels_url'  => 'https://www.pexels.com/photo/close-up-of-man-building-bricks-wall-19688828/',
        'photographer'=> 'GOWTHAM AGM',
        'photographer_url' => 'https://www.pexels.com/@gowtham-agm-609630353',
        'alt'         => 'Maçon construisant un mur de briques',
    ],
    'menuiserie' => [
        'remote'      => 'https://images.pexels.com/photos/30907888/pexels-photo-30907888.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 30907888,
        'pexels_url'  => 'https://www.pexels.com/photo/close-up-of-hands-in-woodworking-craftsmanship-30907888/',
        'photographer'=> 'Đậu Photograph',
        'photographer_url' => 'https://www.pexels.com/@dauphotographer',
        'alt'         => 'Mains de menuisier travaillant le bois',
    ],
    'peinture' => [
        'remote'      => 'https://images.pexels.com/photos/7218683/pexels-photo-7218683.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 7218683,
        'pexels_url'  => 'https://www.pexels.com/photo/woman-painting-the-wall-with-a-roller-brush-7218683/',
        'photographer'=> 'Blue Bird',
        'photographer_url' => 'https://www.pexels.com/@blue-bird',
        'alt'         => 'Peinture murale au rouleau',
    ],
    'plomberie' => [
        'remote'      => 'https://images.pexels.com/photos/6419128/pexels-photo-6419128.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 6419128,
        'pexels_url'  => 'https://www.pexels.com/photo/plumber-installs-pipe-fittings-6419128/',
        'photographer'=> 'Anıl Karakaya',
        'photographer_url' => 'https://www.pexels.com/@anilkarakaya',
        'alt'         => 'Plombier installant des raccords',
    ],
    'renovation' => [
        'remote'      => 'https://images.pexels.com/photos/15798782/pexels-photo-15798782.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 15798782,
        'pexels_url'  => 'https://www.pexels.com/photo/building-under-renovation-15798782/',
        'photographer'=> 'Francesco Ungaro',
        'photographer_url' => 'https://www.pexels.com/@francesco-ungaro',
        'alt'         => 'Chantier de rénovation intérieure',
    ],
    'securite-domotique' => [
        'remote'      => 'https://images.pexels.com/photos/14596555/pexels-photo-14596555.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 14596555,
        'pexels_url'  => 'https://www.pexels.com/photo/camera-on-table-14596555/',
        'photographer'=> 'Danial ZH',
        'photographer_url' => 'https://www.pexels.com/@danielzh1',
        'alt'         => 'Caméra de sécurité connectée',
    ],
    'services-b2b' => [
        'remote'      => 'https://images.pexels.com/photos/36303748/pexels-photo-36303748.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 36303748,
        'pexels_url'  => 'https://www.pexels.com/photo/industrial-hallway-with-cleaning-cart-setup-36303748/',
        'photographer'=> 'Thomas balabaud',
        'photographer_url' => 'https://www.pexels.com/@thomas-balabaud-735585',
        'alt'         => 'Service de nettoyage professionnel',
    ],
    'toiture' => [
        'remote'      => 'https://images.pexels.com/photos/31762405/pexels-photo-31762405.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 31762405,
        'pexels_url'  => 'https://www.pexels.com/photo/roofer-performing-maintenance-on-red-brick-building-31762405/',
        'photographer'=> 'Gundula Vogel',
        'photographer_url' => 'https://www.pexels.com/@guvo59',
        'alt'         => 'Couvreur en maintenance de toiture',
    ],
    'traitement-protection' => [
        'remote'      => 'https://images.pexels.com/photos/5149756/pexels-photo-5149756.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pexels_id'   => 5149756,
        'pexels_url'  => 'https://www.pexels.com/photo/cockroaches-on-white-background-5149756/',
        'photographer'=> 'Roger Brown',
        'photographer_url' => 'https://www.pexels.com/@roger-brown-3435524',
        'alt'         => 'Traitement anti-nuisibles',
    ],
];
