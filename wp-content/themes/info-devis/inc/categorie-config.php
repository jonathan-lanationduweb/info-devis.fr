<?php
/**
 * Données par slug des pages catégories — extraites 1:1 des vues originales
 * views/categories/slugs/{slug}.php (hero, galerie, prestations par défaut).
 * `h1` contient le HTML original (accent italique éventuel).
 */

function idv_categorie_slug_config(): array
{
    return [
        'carrelage' => [
            'kicker'   => 'Artisanat & Excellence',
            'h1'       => 'Carrelage <span class="italic text-primary">d\'Exception</span>',
            'title'    => 'Carrelage d\'Exception',
            'alt'      => 'Carrelage',
            'desc'     => 'Transformez vos espaces avec une pose millimétrée. Du grès cérame haute résistance à la faïence artisanale.',
            'hero'     => 'https://images.unsplash.com/photo-1565538810643-b5bdb714032a?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&q=80',
                'https://images.unsplash.com/photo-1599619351208-3e6c839d6828?w=800&q=80',
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
            ],
            'services' => ['Pose au Sol', 'Habillage Mural', 'Faience et Mosaique'],
        ],
        'chauffage' => [
            'kicker'   => 'Excellence Thermique',
            'h1'       => 'Chauffage',
            'title'    => 'Chauffage',
            'alt'      => 'Chauffage',
            'desc'     => 'L\'art de maîtriser le climat intérieur. Des solutions d\'ingénierie avancées pour un confort absolu.',
            'hero'     => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1581235720704-06d3acfcb36f?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
                'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
            ],
            'services' => ['Chaudieres a Condensation', 'Pompes a Chaleur', 'Planchers Rayonnants', 'Radiateurs Design'],
        ],
        'climatisation' => [
            'kicker'   => 'Maîtrise Thermique',
            'h1'       => 'Climatisation',
            'title'    => 'Climatisation',
            'alt'      => 'Climatisation',
            'desc'     => 'L\'art du confort intérieur. Des solutions invisibles, silencieuses et durables pour sublimer votre habitat.',
            'hero'     => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
                'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
            ],
            'services' => ['Systemes Gainables', 'Splits Design', 'Etude Thermique', 'Garantie 10 Ans'],
        ],
        'demenagement-services' => [
            'kicker'   => 'Expertise Logistique',
            'h1'       => 'Déménagement &amp; Services',
            'title'    => 'Déménagement & Services',
            'alt'      => 'Déménagement & Services',
            'desc'     => 'Une transition fluide vers votre nouvel horizon. Excellence, soin du détail et sérénité absolue.',
            'hero'     => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
                'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
            ],
            'services' => ['Nettoyage de Transition', 'Debarras et Valorisation', 'Mise en boite securisee', 'Garde-meuble VIP'],
        ],
        'electricite' => [
            'kicker'   => 'Artisanat de Précision',
            'h1'       => 'Électricité &amp; Domotique',
            'title'    => 'Électricité & Domotique',
            'alt'      => 'Électricité & Domotique',
            'desc'     => 'L\'art de l\'énergie intelligente pour des intérieurs d\'exception.',
            'hero'     => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1555664424-778a1e5e1b48?w=800&q=80',
                'https://images.unsplash.com/photo-1609429019995-8c40f49535a5?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
            ],
            'services' => ['Mise aux normes NFC 15-100', 'Depannage urgent', 'Installation domotique', 'Design d Eclairage'],
        ],
        'energies-renouvelables' => [
            'kicker'   => 'Solutions Durables',
            'h1'       => 'Énergies Renouvelables',
            'title'    => 'Énergies Renouvelables',
            'alt'      => 'Énergies Renouvelables',
            'desc'     => 'Sublimez votre habitat tout en préservant l\'environnement. Solutions haute performance pour une autonomie énergétique.',
            'hero'     => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
                'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
            ],
            'services' => ['Photovoltaique', 'Pompes a Chaleur', 'Chauffe-eau Solaire', 'Bornes IRVE'],
        ],
        'isolation' => [
            'kicker'   => 'Expertise & Efficacité Énergétique',
            'h1'       => 'Isolation',
            'title'    => 'Isolation',
            'alt'      => 'Isolation',
            'desc'     => 'L\'art de maîtriser votre climat intérieur. Une isolation performante pour le confort et les économies.',
            'hero'     => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
            ],
            'services' => ['Audit Thermique', 'Materiaux Biosources', 'Dossier Aides et Subventions', 'Garantie Decennale'],
        ],
        'jardinage' => [
            'kicker'   => 'Art & Nature',
            'h1'       => 'Jardinage',
            'title'    => 'Jardinage',
            'alt'      => 'Jardinage',
            'desc'     => 'Sublimez vos extérieurs avec une approche éditoriale du paysage. L\'excellence horticole pour des espaces d\'exception.',
            'hero'     => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
                'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800&q=80',
            ],
            'services' => ['Entretien et Tonte', 'Taille Architecturale', 'Creation Paysagere'],
        ],
        'menuiserie' => [
            'kicker'   => 'L\'Art de la Matière',
            'h1'       => 'Menuiserie &amp; Agencement',
            'title'    => 'Menuiserie & Agencement',
            'alt'      => 'Menuiserie & Agencement',
            'desc'     => 'Sublimez votre habitat avec des ouvrages sur mesure alliant savoir-faire artisanal et design contemporain.',
            'hero'     => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&q=80',
                'https://images.unsplash.com/photo-1581235720704-06d3acfcb36f?w=800&q=80',
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
            ],
            'services' => ['Pose de fenetres', 'Portes d entree', 'Parquet', 'Escaliers sur mesure'],
        ],
        'peinture' => [
            'kicker'   => 'Direction Artistique',
            'h1'       => 'Peinture',
            'title'    => 'Peinture',
            'alt'      => 'Peinture',
            'desc'     => 'L\'art de sublimer vos espaces à travers une palette de textures exclusives et de finitions haute couture.',
            'hero'     => 'https://images.unsplash.com/photo-1562259949-e8e7689d7828?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=800&q=80',
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
            ],
            'services' => ['Ravalement de Facade', 'Peinture Interieure', 'Effets et Matieres'],
        ],
        'plomberie' => [
            'kicker'   => 'Services Premium',
            'h1'       => 'Plomberie &amp; Sanitaire',
            'title'    => 'Plomberie & Sanitaire',
            'alt'      => 'Plomberie & Sanitaire',
            'desc'     => 'L\'excellence technique au service de votre confort. Des interventions précises et durables.',
            'hero'     => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?w=800&q=80',
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
            ],
            'services' => ['Reparation de fuites', 'Installation sanitaire', 'Debouchage canalisations', 'Renovation salle de bain'],
        ],
        'renovation' => [
            'kicker'   => 'Excellence & Artisanat',
            'h1'       => 'Rénovation',
            'title'    => 'Rénovation',
            'alt'      => 'Rénovation',
            'desc'     => 'Transformez votre patrimoine en un chef-d\'œuvre contemporain. De la conception à la finition minutieuse.',
            'hero'     => 'https://images.unsplash.com/photo-1572120360610-d971b9d7767c?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
                'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
            ],
            'services' => ['Architecture d Interieur', 'Gros Oeuvre et Structure', 'Second Oeuvre et Finitions'],
        ],
        'securite-domotique' => [
            'kicker'   => 'L\'intelligence au service du confort',
            'h1'       => 'Sécurité &amp; Domotique',
            'title'    => 'Sécurité & Domotique',
            'alt'      => 'Sécurité & Domotique',
            'desc'     => 'Transformez votre habitat en sanctuaire technologique. Protection périmétrale et automatisation intuitive.',
            'hero'     => 'https://images.unsplash.com/photo-1558002038-1055907df827?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
                'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
            ],
            'services' => ['Securite Perimetrale', 'Maison Connectee', 'Videosurveillance AI'],
        ],
        'services-b2b' => [
            'kicker'   => 'Expertise & Performance',
            'h1'       => 'Services B2B',
            'title'    => 'Services B2B',
            'alt'      => 'Services B2B',
            'desc'     => 'L\'excellence opérationnelle pour vos infrastructures professionnelles. Solutions sur mesure pour le tertiaire.',
            'hero'     => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
                'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
            ],
            'services' => ['Nettoyage Industriel et Tertiaire', 'Infrastructure IT', 'Securite et Gardiennage', 'Maintenance Multi-technique'],
        ],
        'toiture' => [
            'kicker'   => 'Artisanat d\'Exception',
            'h1'       => 'Toiture',
            'title'    => 'Toiture',
            'alt'      => 'Toiture',
            'desc'     => 'Protégez votre patrimoine avec des solutions de couverture haut de gamme. Expertise séculaire et matériaux nobles.',
            'hero'     => 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
                'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
            ],
            'services' => ['Zinguerie et Ornement', 'Reparation et Restauration', 'Ardoises et Tuiles', 'Isolation et Combles'],
        ],
        'traitement-protection' => [
            'kicker'   => 'Artisanat & Préservation',
            'h1'       => 'Traitement &amp; Protection',
            'title'    => 'Traitement & Protection',
            'alt'      => 'Traitement & Protection',
            'desc'     => 'Préservez l\'intégrité de votre patrimoine avec nos solutions expertes de traitement préventif et curatif.',
            'hero'     => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=1400&q=80',
            'gallery'  => [
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
                'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
            ],
            'services' => ['Hydrofugation des Facades', 'Diagnostic Thermique', 'Lutte contre les Nuisibles', 'Maitrise de l Humidite'],
        ],
    ];
}

/**
 * Top 3 artisans publiés d'un métier (équivalent getTopArtisansForCat :
 * fiches vérifiées, meilleures d'abord).
 */
function idv_categorie_top_artisans(int $term_id): array
{
    $fiches = get_posts([
        'post_type'   => 'artisan',
        'post_status' => 'publish',
        'numberposts' => 24,
        'tax_query'   => [['taxonomy' => 'metier', 'field' => 'term_id', 'terms' => $term_id]],
    ]);
    usort($fiches, function ($a, $b) {
        return (float) get_post_meta($b->ID, '_idc_rating_avg', true) <=> (float) get_post_meta($a->ID, '_idc_rating_avg', true);
    });
    return array_slice($fiches, 0, 3);
}

/** 4 autres métiers au hasard (équivalent getRelated). */
function idv_categorie_related(int $term_id): array
{
    $terms = get_terms(['taxonomy' => 'metier', 'hide_empty' => false, 'exclude' => [$term_id]]);
    if (is_wp_error($terms)) {
        return [];
    }
    shuffle($terms);
    return array_slice($terms, 0, 4);
}
