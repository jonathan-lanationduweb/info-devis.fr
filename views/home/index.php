<?php
$catIcons = [
  'plomberie'               => '🔧',
  'electricite'             => '⚡',
  'peinture'                => '🖌️',
  'toiture'                 => '🏠',
  'chauffage'               => '🔥',
  'menuiserie'              => '🪚',
  'climatisation'           => '❄️',
  'isolation'               => '🧱',
  'maconnerie'              => '⬛',
  'carrelage'               => '◻️',
  'jardinage'               => '🌿',
  'renovation'              => '🏗️',
  'securite-domotique'      => '🛡️',
  'energies-renouvelables'  => '☀️',
  'amenagements-exterieurs' => '🏊',
  'services-b2b'            => '💼',
  'demenagement-services'   => '🚚',
  'traitement-protection'   => '🦺',
];

// Images de fond par catégorie (Unsplash libre de droit)
$catImages = [
  'plomberie'               => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800&q=80',
  'electricite'             => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&q=80',
  'peinture'                => 'https://images.unsplash.com/photo-1562259949-e8e7689d7828?w=800&q=80',
  'toiture'                 => 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=800&q=80',
  'chauffage'               => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
  'menuiserie'              => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=800&q=80',
  'climatisation'           => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800&q=80',
  'isolation'               => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80',
  'maconnerie'              => 'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80',
  'carrelage'               => 'https://images.unsplash.com/photo-1565538810643-b5bdb714032a?w=800&q=80',
  'jardinage'               => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800&q=80',
  'renovation'              => 'https://images.unsplash.com/photo-1572120360610-d971b9d7767c?w=800&q=80',
];
?>

<!-- ── Hero ── -->
<section class="relative min-h-[870px] flex items-center pt-20 overflow-hidden">
  <div class="absolute inset-0 z-0">
    <div style="position:absolute;inset:0;background-image:url('<?= APP_URL ?>/assets/img/artisan.png');background-size:cover;background-position:center center;"></div>
    <div class="absolute inset-0 bg-black/50"></div>
  </div>

  <div class="relative z-10 w-full max-w-screen-2xl mx-auto px-8">
    <div class="max-w-3xl">
      <h1 class="font-headline text-6xl md:text-8xl text-white mb-8 leading-[1.1] tracking-tight drop-shadow-lg">
        Trouvez l'artisan parfait pour vos
        <span class="text-primary italic"> travaux</span>
      </h1>

      <!-- Barre de recherche -->
      <div class="bg-white/95 backdrop-blur-md rounded-xl p-2 flex flex-col md:flex-row items-stretch gap-2 max-w-2xl shadow-2xl">
        <div class="flex-1 flex items-center px-4 py-3 border-r border-gray-200">
          <svg class="text-gray-400 mr-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z" />
          </svg>
          <select name="category" id="hero-category" class="w-full bg-transparent border-none focus:ring-0 text-on-background text-sm font-body">
            <option value="">Type de métier</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= Security::e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex-1 flex items-center px-4 py-3 border-r border-gray-200">
          <svg class="text-gray-400 mr-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
            <circle cx="12" cy="10" r="3" />
          </svg>
          <input type="text" id="hero-ville" placeholder="Code postal / Ville"
            class="w-full bg-transparent border-none focus:ring-0 text-on-background placeholder-gray-400 text-sm font-body">
        </div>
        <button id="hero-search-btn" class="bg-primary text-white px-8 py-3 rounded-lg font-semibold text-sm hover:opacity-90 transition-all">
          Chercher
        </button>
      </div>
    </div>
  </div>
</section>

<!-- ── Barre réassurance ── -->
<div class="bg-primary py-5">
  <div class="max-w-screen-2xl mx-auto px-8 flex flex-col md:flex-row justify-around items-center gap-6 text-white font-label text-xs tracking-[0.15em] font-bold">
    <div class="flex items-center gap-3">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" />
        <polyline points="12 6 12 12 16 14" />
      </svg>
      100% GRATUIT
    </div>
    <div class="flex items-center gap-3">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" />
        <polyline points="12 6 12 12 16 14" />
      </svg>
      RÉPONSE RAPIDE
    </div>
    <div class="flex items-center gap-3">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
        <polyline points="22 4 12 14.01 9 11.01" />
      </svg>
      ARTISANS CERTIFIÉS
    </div>
  </div>
</div>

<!-- ── Nos expertises ── -->
<section class="pt-24 pb-16 bg-surface-container-low">
  <div class="max-w-screen-2xl mx-auto px-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
      <div>
        <span class="font-label text-xs uppercase tracking-widest text-primary font-bold mb-4 block">Services Professionnels</span>
        <h2 class="font-headline text-5xl text-on-background">Nos expertises</h2>
      </div>
      <a href="<?= APP_URL ?>/categories" class="text-primary font-medium flex items-center gap-2 hover:gap-4 transition-all pb-2 text-sm">
        Voir tous les métiers
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <line x1="5" y1="12" x2="19" y2="12" />
          <polyline points="12 5 19 12 12 19" />
        </svg>
      </a>
    </div>

    <!-- Bento Grid -->
    <?php
    $cats = array_values($categories);
    $first  = $cats[0] ?? null;
    $second = $cats[1] ?? null;
    $rest   = array_slice($cats, 2, 3);
    ?>
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

      <!-- Grande carte gauche -->
      <?php if ($first): ?>
        <a href="<?= APP_URL ?>/categorie/<?= Security::e($first['slug']) ?>"
          class="md:col-span-8 group relative overflow-hidden rounded-xl bg-surface-container-low aspect-[16/9] md:aspect-auto md:h-[500px]">
          <img src="<?= $catImages[$first['slug']] ?? 'https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80' ?>"
            alt="<?= Security::e($first['name']) ?>"
            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
          <div class="absolute bottom-0 p-10 w-full">
            <span class="bg-primary-container/90 text-on-primary-container px-3 py-1 text-xs font-label uppercase tracking-widest mb-4 inline-block rounded">
              <?= $catIcons[$first['slug']] ?? '🛠️' ?> <?= Security::e($first['name']) ?>
            </span>
            <h3 class="text-3xl font-headline text-white mb-3"><?= Security::e($first['name']) ?></h3>
            <p class="text-white/80 max-w-md text-sm leading-relaxed mb-5">
              <?= Security::e(substr($first['description'] ?? 'Artisans qualifiés disponibles près de chez vous.', 0, 120)) ?>...
            </p>
            <span class="inline-flex items-center gap-2 text-white font-label text-sm group-hover:gap-4 transition-all">
              Découvrir le métier →
            </span>
          </div>
        </a>
      <?php endif; ?>

      <!-- Carte portrait droite -->
      <?php if ($second): ?>
        <a href="<?= APP_URL ?>/categorie/<?= Security::e($second['slug']) ?>"
          class="md:col-span-4 group relative overflow-hidden rounded-xl bg-surface-container-low h-[500px]">
          <img src="<?= $catImages[$second['slug']] ?? 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=800&q=80' ?>"
            alt="<?= Security::e($second['name']) ?>"
            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
          <div class="absolute bottom-0 p-8 w-full">
            <span class="bg-primary-container/90 text-on-primary-container px-3 py-1 text-xs font-label uppercase tracking-widest mb-4 inline-block rounded">
              <?= $catIcons[$second['slug']] ?? '🛠️' ?> <?= Security::e($second['name']) ?>
            </span>
            <h3 class="text-2xl font-headline text-white mb-3"><?= Security::e($second['name']) ?></h3>
            <p class="text-white/80 text-sm leading-relaxed mb-5">
              <?= Security::e(substr($second['description'] ?? 'Artisans qualifiés disponibles près de chez vous.', 0, 100)) ?>...
            </p>
            <span class="inline-flex items-center gap-2 text-white font-label text-sm group-hover:gap-4 transition-all">
              Voir les réalisations →
            </span>
          </div>
        </a>
      <?php endif; ?>

      <!-- 3 petites cartes carrées -->
      <?php foreach ($rest as $cat): ?>
        <a href="<?= APP_URL ?>/categorie/<?= Security::e($cat['slug']) ?>"
          class="md:col-span-4 group relative overflow-hidden rounded-xl bg-surface-container-low aspect-square">
          <img src="<?= $catImages[$cat['slug']] ?? 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800&q=80' ?>"
            alt="<?= Security::e($cat['name']) ?>"
            class="absolute inset-0 w-full h-full object-cover transition-all duration-700 group-hover:scale-105">
          <div class="absolute inset-0 bg-black/40 group-hover:bg-black/20 transition-colors duration-500"></div>
          <div class="absolute inset-0 flex flex-col justify-end p-8 bg-gradient-to-t from-black/80 to-transparent">
            <h3 class="text-xl font-headline text-white mb-2">
              <?= $catIcons[$cat['slug']] ?? '🛠️' ?> <?= Security::e($cat['name']) ?>
            </h3>
            <p class="text-white/70 text-xs leading-relaxed">
              <?= Security::e(substr($cat['description'] ?? 'Artisans qualifiés disponibles près de chez vous.', 0, 80)) ?>...
            </p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Comment ça marche ── -->
<section class="py-24 bg-surface">
  <div class="max-w-screen-2xl mx-auto px-8">
    <div class="max-w-2xl mx-auto text-center mb-20">
      <h2 class="font-headline text-5xl mb-6">Comment ça marche</h2>
      <p class="text-on-surface-variant text-lg">Trois étapes simples pour concrétiser vos projets de rénovation avec sérénité.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-16">
      <?php
      $steps = [
        ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'title' => 'Décrivez votre projet', 'desc' => 'Remplissez notre formulaire en 2 minutes pour détailler vos besoins spécifiques.'],
        ['icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'title' => 'Recevez des devis', 'desc' => "Jusqu'à 5 artisans qualifiés vous contactent pour proposer leurs services."],
        ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'title' => 'Choisissez &amp; Réalisez', 'desc' => "Comparez les offres et sélectionnez l'artisan qui vous correspond le mieux."],
      ];
      ?>
      <?php foreach ($steps as $s): ?>
        <div class="text-center group">
          <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-8 shadow-lg group-hover:bg-primary transition-colors duration-300">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" class="text-primary group-hover:text-white transition-colors">
              <path d="<?= $s['icon'] ?>" />
            </svg>
          </div>
          <h3 class="font-headline text-2xl mb-4"><?= $s['title'] ?></h3>
          <p class="text-on-surface-variant font-light px-4 text-sm leading-relaxed"><?= $s['desc'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Citation ── -->
<section class="py-24 bg-surface-container-highest/40">
  <div class="max-w-4xl mx-auto px-8">
    <div class="bg-surface-container-highest p-16 rounded-xl relative overflow-hidden">
      <span class="absolute top-0 left-0 text-[10rem] font-headline opacity-5 leading-none select-none">"</span>
      <div class="relative z-10 text-center">
        <p class="text-2xl font-headline italic text-on-surface leading-snug mb-8">
          "La qualité d'un ouvrage ne réside pas seulement dans les matériaux utilisés, mais dans l'intention et la précision de la main qui les façonne."
        </p>
        <span class="font-label font-bold text-primary tracking-widest text-xs uppercase block">L'équipe InfoDevis</span>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section class="py-20 bg-surface-container-low">
  <div class="max-w-screen-xl mx-auto px-8">
    <div class="bg-primary rounded-2xl p-12 md:p-20 text-center relative overflow-hidden">
      <div class="absolute top-0 right-0 w-1/3 h-full bg-white/5 skew-x-12 translate-x-1/2"></div>
      <h2 class="font-headline text-4xl md:text-6xl text-white mb-8 relative z-10">Prêt à lancer vos travaux ?</h2>
      <p class="text-white/80 text-xl mb-12 max-w-2xl mx-auto relative z-10 font-light">
        Rejoignez des milliers de particuliers qui font confiance à notre réseau d'artisans certifiés.
      </p>
      <div class="flex flex-col md:flex-row gap-6 justify-center relative z-10">
        <a href="<?= APP_URL ?>/devis" class="bg-white text-primary px-10 py-4 rounded font-bold text-lg hover:bg-green-50 transition-colors shadow-lg">
          Demander mon devis gratuit
        </a>
        <a href="<?= APP_URL ?>/tarifs-pro" class="border border-white/30 text-white px-10 py-4 rounded font-bold text-lg hover:bg-white/10 transition-colors">
          Consulter les tarifs
        </a>
      </div>
    </div>
  </div>
</section>

<script>
  document.getElementById('hero-search-btn').addEventListener('click', function() {
    var cat = document.getElementById('hero-category').value;
    var ville = encodeURIComponent(document.getElementById('hero-ville').value);
    window.location.href = '<?= APP_URL ?>/devis?categorie=' + cat + '&ville=' + ville;
  });
</script>