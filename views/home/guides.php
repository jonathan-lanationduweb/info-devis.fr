<?php

/**
 * Vue : views/home/guides.php
 * Prix supprimés — remplacés par des conseils et facteurs de coût
 */
$guides = [
  ['icon' => '🔧', 'cat' => 'Plomberie', 'img' => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=600&q=80', 'items' => [
    ['label' => 'Installation WC',    'info' => 'Dépend de l\'accès aux arrivées d\'eau et du modèle choisi'],
    ['label' => 'Rénovation douche',  'info' => 'Varie selon la surface, les matériaux et la plomberie existante'],
    ['label' => 'Chauffe-eau',        'info' => 'Électrique, thermodynamique ou solaire : des écarts importants'],
  ]],
  ['icon' => '🔥', 'cat' => 'Chauffage', 'img' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80', 'items' => [
    ['label' => 'Pompe à chaleur',       'info' => 'Surface à chauffer et configuration du terrain sont déterminants'],
    ['label' => 'Radiateur fonte',       'info' => 'Le remplacement ou l\'ajout au circuit existant influe sur le coût'],
    ['label' => 'Plancher chauffant',    'info' => 'Hydraulique ou électrique, neuf ou rénovation : très différent'],
  ]],
  ['icon' => '🧱', 'cat' => 'Isolation', 'img' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&q=80', 'items' => [
    ['label' => 'Combles perdus',        'info' => 'L\'accessibilité et l\'épaisseur souhaitée font varier le devis'],
    ['label' => 'Isolation murs ext.',   'info' => 'Le type de façade et la surface totale sont les principaux critères'],
    ['label' => 'Soufflage ouate',       'info' => 'Matériau écologique, coût dépendant du volume à traiter'],
  ]],
  ['icon' => '⚡', 'cat' => 'Électricité', 'img' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=600&q=80', 'items' => [
    ['label' => 'Mise aux normes',       'info' => 'La vétusté de l\'installation et la surface du logement comptent'],
    ['label' => 'Tableau électrique',    'info' => 'Nombre de circuits et puissance souscrite sont déterminants'],
    ['label' => 'Point lumineux',        'info' => 'Saignée, encastrement ou apparent : des coûts très différents'],
  ]],
  ['icon' => '🪚', 'cat' => 'Menuiserie', 'img' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=600&q=80', 'items' => [
    ['label' => 'Fenêtre PVC double',    'info' => 'Dimensions, vitrage et pose (dépose ancienne fenêtre incluse ?)'],
    ['label' => 'Porte entrée bois',     'info' => 'Essence du bois, vitrage et niveau de sécurité souhaité'],
    ['label' => 'Volet roulant élec.',   'info' => 'Motorisation, dimensions et type de coffre sont clés'],
  ]],
  ['icon' => '🖌️', 'cat' => 'Peinture & Sols', 'img' => 'https://images.unsplash.com/photo-1562259949-e8e7689d7828?w=600&q=80', 'items' => [
    ['label' => 'Peinture murs',         'info' => 'État des surfaces, nombre de couches et qualité de peinture'],
    ['label' => 'Parquet chêne',         'info' => 'Massif ou contrecollé, pose flottante ou clouée'],
    ['label' => 'Carrelage grand format', 'info' => 'Découpe, pose en diagonale et joints influencent le prix'],
  ]],
  ['icon' => '🏠', 'cat' => 'Toiture', 'img' => 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=600&q=80', 'items' => [
    ['label' => 'Réfection complète',    'info' => 'Surface, pente, type de tuiles et accessibilité du toit'],
    ['label' => 'Remplacement tuiles',   'info' => 'Nombre de tuiles, modèle disponible et hauteur de toit'],
    ['label' => 'Réparation fuite',      'info' => 'Localisation et origine de la fuite sont déterminants'],
  ]],
  ['icon' => '⬛', 'cat' => 'Maçonnerie', 'img' => 'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=600&q=80', 'items' => [
    ['label' => 'Réparation fissure',    'info' => 'Fissure structurelle ou esthétique : diagnostic préalable indispensable'],
    ['label' => 'Ouverture mur porteur', 'info' => 'Étude de structure obligatoire, largeur et hauteur de l\'ouverture'],
    ['label' => 'Dallage béton',         'info' => 'Surface, épaisseur, armature et finition souhaitée'],
  ]],
];
?>

<main class="pt-32 pb-24">

  <!-- Hero éditorial -->
  <section class="max-w-7xl mx-auto px-8 mb-24">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-end">
      <div class="lg:col-span-7">
        <span class="font-label uppercase tracking-widest text-primary text-sm font-semibold mb-4 block">Expertise &amp; Transparence</span>
        <h1 class="text-6xl md:text-7xl font-headline leading-[0.95] text-on-background mb-8">
          Comprendre <span class="italic text-primary">ce qui compte</span> pour vos travaux.
        </h1>
      </div>
      <div class="lg:col-span-5 pb-4">
        <p class="font-body text-xl text-on-surface-variant leading-relaxed">
          Nos guides sont élaborés par des experts du bâtiment pour vous aider à poser les bonnes questions et obtenir un devis juste.
        </p>
      </div>
    </div>
    <div class="mt-16 rounded-3xl overflow-hidden h-[400px] w-full shadow-2xl">
      <img src="<?= APP_URL ?>/assets/img/metier.png" alt="Guides travaux" class="w-full h-full object-cover">
    </div>
  </section>

  <!-- Intro -->
  <section class="max-w-7xl mx-auto px-8 mb-24">
    <div class="bg-surface-container-low rounded-3xl p-12 lg:p-16 relative overflow-hidden">
      <div class="relative z-10 max-w-3xl">
        <h2 class="text-3xl font-headline italic text-primary mb-6">Pourquoi les devis varient-ils autant ?</h2>
        <p class="font-body text-on-surface-variant mb-8">Un devis ne dépend jamais d'un seul facteur. Voici les trois grands critères que tout artisan sérieux évalue avant de vous répondre.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <?php foreach (
            [
              ["Main d'œuvre",  "Le coût horaire varie selon la spécialisation et la complexité technique du chantier."],
              ['Matériaux',     'La gamme choisie, la disponibilité et les quantités nécessaires font varier l\'estimation.'],
              ['Contraintes',   'Accessibilité, superficie, état du support existant et délais souhaités entrent en jeu.'],
            ] as $item
          ): ?>
            <div class="border-l border-primary/20 pl-6">
              <h4 class="font-bold text-primary mb-2 text-sm"><?= $item[0] ?></h4>
              <p class="text-xs text-on-surface-variant"><?= $item[1] ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Grille des guides -->
  <section class="max-w-7xl mx-auto px-8 mb-24">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php foreach ($guides as $guide): ?>
        <div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 border border-transparent hover:border-primary/10">
          <div class="h-48 overflow-hidden">
            <img src="<?= $guide['img'] ?>" alt="<?= htmlspecialchars($guide['cat']) ?>"
              class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy">
          </div>
          <div class="p-8">
            <div class="flex justify-between items-start mb-6">
              <h3 class="text-2xl font-headline"><?= htmlspecialchars($guide['cat']) ?></h3>
              <span class="text-2xl"><?= $guide['icon'] ?></span>
            </div>
            <ul class="space-y-4 mb-8">
              <?php foreach ($guide['items'] as $item): ?>
                <li class="border-b border-surface-container pb-3">
                  <p class="font-bold text-sm text-on-surface mb-1"><?= htmlspecialchars($item['label']) ?></p>
                  <p class="text-xs text-on-surface-variant font-body leading-relaxed"><?= htmlspecialchars($item['info']) ?></p>
                </li>
              <?php endforeach; ?>
            </ul>
            <a href="<?= APP_URL ?>/devis"
              class="block w-full py-3 rounded border border-outline-variant/30 text-primary font-label text-center text-sm hover:bg-primary hover:text-white transition-all duration-300">
              Demander un devis <?= htmlspecialchars($guide['cat']) ?> →
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Citation -->
  <section class="max-w-7xl mx-auto px-8 mb-16">
    <div class="bg-surface-container-highest rounded-3xl p-16 text-center">
      <p class="text-3xl md:text-4xl font-headline italic text-on-background mb-10 max-w-4xl mx-auto leading-tight">
        "Un devis transparent est la première pierre d'un projet réussi. Posez les bonnes questions, obtenez les bonnes réponses."
      </p>
      <cite class="font-label uppercase tracking-widest text-primary font-bold not-italic text-xs">— L'Équipe InfoDevis</cite>
    </div>
  </section>

  <!-- CTA -->
  <section class="max-w-7xl mx-auto px-8">
    <div class="bg-primary rounded-2xl p-12 text-center relative overflow-hidden">
      <h2 class="font-headline text-4xl text-white mb-6 relative z-10">Prêt à chiffrer votre projet ?</h2>
      <p class="text-white/80 mb-10 max-w-xl mx-auto font-body relative z-10">Recevez jusqu'à 5 devis gratuits d'artisans qualifiés sous 24h.</p>
      <a href="<?= APP_URL ?>/devis" class="inline-block bg-white text-primary px-10 py-4 rounded font-bold hover:bg-green-50 transition-colors shadow-lg relative z-10">
        Demander mon devis gratuit
      </a>
    </div>
  </section>

</main>