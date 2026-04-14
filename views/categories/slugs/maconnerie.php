<?php /* views/categories/slugs/maconnerie.php */ ?>
<main class="pt-24">
  <section class="relative px-8 mb-24 max-w-[1440px] mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
      <div class="lg:col-span-5 z-10">
        <span class="font-label text-primary tracking-[0.2em] uppercase text-xs mb-6 block">Artisanat de Précision</span>
        <h1 class="text-6xl lg:text-7xl font-headline font-light leading-tight mb-8 text-on-surface">L'Excellence de la <span class="italic">Maçonnerie</span></h1>
        <p class="text-xl font-body text-on-surface-variant mb-10 max-w-md">Des fondations solides pour vos rêves architecturaux. Expertise artisanale et matériaux nobles.</p>
        <div class="flex items-baseline gap-4 mb-10">
          <span class="text-stone-400 font-label text-xs uppercase tracking-widest">À partir de</span>
          <span class="text-4xl font-headline text-primary font-bold"><?= !empty($category['prix_min']) ? number_format($category['prix_min'], 0, ',', ' ') . ' €' : '500 €' ?></span>
        </div>
        <div class="flex flex-wrap gap-4">
          <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="bg-primary text-on-primary px-8 py-4 rounded-xl font-label tracking-widest uppercase text-xs hover:opacity-90 transition-all">Demander un devis</a>
          <a href="<?= APP_URL ?>/categories" class="border border-outline-variant/30 text-on-surface px-8 py-4 rounded-xl font-label tracking-widest uppercase text-xs hover:bg-surface-container-low transition-all">Voir nos réalisations</a>
        </div>
      </div>
      <div class="lg:col-span-7 relative h-[600px] rounded-2xl overflow-hidden group">
        <img class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=1200&q=80" alt="Maçonnerie">
        <div class="absolute inset-0 bg-gradient-to-r from-background/40 to-transparent"></div>
        <div class="absolute bottom-8 right-8 bg-surface-container-lowest/80 backdrop-blur-md p-6 rounded-xl border border-outline-variant/10 shadow-2xl max-w-xs">
          <p class="text-xs font-label tracking-widest uppercase text-on-surface-variant mb-2">Qualité Garantie</p>
          <p class="text-xs font-body text-on-surface leading-relaxed italic">"Une finition irréprochable sur notre mur d'enceinte en pierre sèche."</p>
        </div>
      </div>
    </div>
  </section>

  <section class="px-8 mb-32 max-w-[1440px] mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-4 grid-rows-2 gap-4 h-[800px]">
      <div class="md:col-span-2 md:row-span-2 rounded-2xl overflow-hidden relative group"><img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80" alt="Fondations">
        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-8"><span class="text-white font-headline text-2xl italic">Fondations Structurelles</span></div>
      </div>
      <div class="rounded-2xl overflow-hidden relative group"><img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80" alt="Brique"></div>
      <div class="rounded-2xl overflow-hidden relative group"><img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800&q=80" alt="Pierre"></div>
      <div class="md:col-span-2 rounded-2xl overflow-hidden relative group"><img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" src="https://images.unsplash.com/photo-1590856029826-c7a73142bbf1?w=800&q=80" alt="Maçonnerie artisanale"></div>
    </div>
  </section>

  <section class="bg-surface-container-low py-32 px-8">
    <div class="max-w-[1440px] mx-auto">
      <div class="flex flex-col md:flex-row justify-between items-end mb-20 gap-8">
        <div class="max-w-xl"><span class="font-label text-primary tracking-[0.2em] uppercase text-xs mb-4 block">Nos Expertises</span>
          <h2 class="text-5xl font-headline leading-tight">Des prestations <span class="italic">sur-mesure</span> pour chaque projet.</h2>
        </div>
        <div class="text-on-surface-variant font-body italic border-l border-primary/30 pl-8">L'art de bâtir pour durer, alliant techniques traditionnelles et matériaux innovants.</div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-px bg-outline-variant/10 overflow-hidden rounded-3xl border border-outline-variant/10 shadow-sm">
        <?php $svcs = !empty($services) ? $services : [['nom' => 'Construction de murs', 'description' => 'Murs de clôture, séparations intérieures ou façades porteuses.'], ['nom' => 'Fondations', 'description' => "L'assurance d'une stabilité pérenne pour vos extensions."], ['nom' => 'Rénovation de façades', 'description' => 'Redonnez du cachet à votre bâti ancien.'], ['nom' => 'Dallage béton', 'description' => 'Terrasses, allées ou garages : des surfaces planes et résistantes.']];
        foreach ($svcs as $svc): ?>
          <div class="bg-surface p-10 hover:bg-surface-container-high transition-colors group">
            <div class="w-12 h-12 rounded-full border border-primary/20 flex items-center justify-center mb-8 group-hover:bg-primary transition-all"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-primary group-hover:text-on-primary transition-colors">
                <polyline points="20 6 9 17 4 12" />
              </svg></div>
            <h3 class="text-xl font-headline mb-4"><?= Security::e($svc['nom']) ?></h3>
            <p class="text-on-surface-variant font-body text-sm leading-relaxed mb-6"><?= Security::e(substr($svc['description'] ?? '', 0, 100)) ?></p>
            <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="text-primary font-label text-xs tracking-widest uppercase flex items-center gap-2 group-hover:gap-4 transition-all">En savoir plus →</a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="py-32 px-8 max-w-[1440px] mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-32">
      <div>
        <h2 class="text-4xl font-headline mb-12 italic">Pourquoi nous faire confiance ?</h2>
        <div class="space-y-12">
          <?php foreach ([['Artisans certifiés', 'Une sélection rigoureuse des meilleurs maîtres maçons de votre région.'], ['Devis gratuit', 'Une estimation transparente et détaillée, sans engagement.'], ['Garantie décennale', 'Tous nos travaux sont couverts par une assurance décennale.']] as $r): ?>
            <div class="flex gap-6 items-start">
              <div class="p-3 bg-tertiary-container rounded-lg"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-on-tertiary-container">
                  <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                  <polyline points="22 4 12 14.01 9 11.01" />
                </svg></div>
              <div>
                <h4 class="font-headline text-xl mb-2"><?= $r[0] ?></h4>
                <p class="text-on-surface-variant text-sm leading-relaxed"><?= $r[1] ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="bg-surface-container rounded-[2rem] p-12 relative overflow-hidden">
        <h3 class="text-3xl font-headline mb-12">Le Processus <span class="italic text-primary">InfoDevis</span></h3>
        <div class="space-y-8 relative">
          <div class="absolute left-6 top-8 bottom-8 w-px bg-primary/20"></div>
          <?php foreach ([['Demande de devis', 'Décrivez votre projet en moins de 2 minutes.'], ['Mise en relation', 'Jusqu\'à 5 artisans locaux qualifiés vous contactent.'], ['Réalisation', 'Choisissez et lancez vos travaux en toute sérénité.']] as $i => $s): ?>
            <div class="flex gap-8 relative">
              <div class="w-12 h-12 rounded-full bg-primary text-on-primary flex items-center justify-center font-headline text-lg shrink-0 z-10 shadow-lg"><?= $i + 1 ?></div>
              <div>
                <p class="font-label text-xs uppercase tracking-widest text-primary mb-1">Étape <?= $i + 1 ?></p>
                <h4 class="text-xl font-headline mb-2"><?= $s[0] ?></h4>
                <p class="text-on-surface-variant text-sm"><?= $s[1] ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <?php if (!empty($topArtisans)): ?>
    <section class="py-16 px-8 bg-surface-container-low">
      <div class="max-w-[1440px] mx-auto">
        <h2 class="font-headline text-3xl text-center mb-10">Artisans recommandés</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <?php foreach ($topArtisans as $a): ?>
            <div class="bg-white rounded-2xl p-6 border border-outline-variant/20 hover:-translate-y-1 hover:shadow-lg transition-all">
              <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#0d2b33] to-primary flex items-center justify-center text-white font-bold text-lg"><?= strtoupper(substr($a['first_name'] ?? 'A', 0, 1)) ?></div>
                <div>
                  <div class="font-semibold text-sm"><?= Security::e($a['company_name'] ?: ($a['first_name'] . ' ' . $a['last_name'])) ?></div>
                  <div class="text-xs text-on-surface-variant"><?= Security::e($a['ville'] ?? '') ?></div>
                </div>
              </div>
              <div class="flex items-center gap-2 mb-4"><span class="text-yellow-400"><?= str_repeat('★', (int)round($a['rating_avg'] ?? 0)) . str_repeat('☆', 5 - (int)round($a['rating_avg'] ?? 0)) ?></span><span class="text-sm font-semibold"><?= number_format((float)($a['rating_avg'] ?? 0), 1) ?></span></div>
              <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="block w-full text-center bg-primary text-on-primary py-2.5 rounded-full text-sm font-semibold hover:opacity-90 transition-all">Demander un devis</a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if (!empty($faq)): ?>
    <section class="py-16 px-8 bg-surface-container-low">
      <div class="max-w-3xl mx-auto">
        <h2 class="font-headline text-3xl text-center mb-10">Questions fréquentes</h2>
        <div class="space-y-4"><?php foreach ($faq as $i => $item): ?><div class="bg-white rounded-xl border border-outline-variant/20 overflow-hidden"><button onclick="toggleFaq(<?= $i ?>)" class="w-full text-left px-6 py-5 flex justify-between items-center font-semibold text-sm font-body bg-transparent border-none cursor-pointer"><?= Security::e($item['q']) ?><span id="faq-icon-<?= $i ?>" class="text-primary text-xl flex-shrink-0">+</span></button>
              <div id="faq-<?= $i ?>" style="display:none" class="px-6 pb-5 text-on-surface-variant text-sm leading-relaxed"><?= Security::e($item['a']) ?></div>
            </div><?php endforeach; ?></div>
      </div>
    </section>
  <?php endif; ?>

  <section class="px-8 mb-32 max-w-[1440px] mx-auto">
    <div class="bg-primary rounded-[3rem] p-16 md:p-24 text-center relative overflow-hidden">
      <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-br from-white to-transparent"></div>
      </div>
      <div class="relative z-10">
        <h2 class="text-5xl md:text-6xl font-headline text-on-primary mb-8 italic">Prêt à concrétiser votre <span class="not-italic">vision</span> ?</h2>
        <p class="text-on-primary/80 font-body text-lg mb-12 max-w-2xl mx-auto">Obtenez des offres comparatives gratuites de la part d'artisans maçons certifiés près de chez vous.</p>
        <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>"
          class="inline-block bg-white text-primary px-12 py-5 rounded-full font-label tracking-widest uppercase text-xs hover:scale-105 transition-all shadow-2xl relative z-10">
          Obtenir mon devis gratuit
        </a>
      </div>
    </div>
  </section>
</main>
<script>
  function toggleFaq(i) {
    var el = document.getElementById('faq-' + i);
    var icon = document.getElementById('faq-icon-' + i);
    var open = el.style.display !== 'none';
    el.style.display = open ? 'none' : 'block';
    icon.textContent = open ? '+' : '−';
  }
</script>