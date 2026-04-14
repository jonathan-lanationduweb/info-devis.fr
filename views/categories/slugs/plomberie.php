<?php /* views/categories/slugs/plomberie.php */ ?>
<main class="pt-20">

  <!-- Hero -->
  <section class="relative min-h-[870px] flex items-center overflow-hidden bg-surface-container-low">
    <div class="absolute inset-0 z-0">
      <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=1400&q=80" alt="Plomberie & Sanitaire">
      <div class="absolute inset-0 bg-gradient-to-r from-background via-background/40 to-transparent"></div>
    </div>
    <div class="relative z-10 max-w-[1440px] mx-auto px-8 w-full">
      <div class="max-w-2xl">
        <span class="font-label tracking-[0.2em] uppercase text-xs text-primary mb-6 block font-semibold">Services Premium</span>
        <h1 class="font-headline text-6xl md:text-8xl text-on-surface leading-tight mb-8">Plomberie &amp; Sanitaire</h1>
        <p class="text-xl font-body text-on-surface-variant mb-10 max-w-lg leading-relaxed"><?= Security::e($category['description'] ?? 'L\'excellence technique au service de votre confort. Des interventions précises et durables.') ?></p>
        <div class="flex items-baseline gap-4 mb-10">
          <span class="text-stone-400 font-label text-sm uppercase tracking-widest">À partir de</span>
          <span class="text-4xl font-headline text-primary italic"><?= !empty($category['prix_min']) ? number_format($category['prix_min'], 0, ',', ' ') . ' €' : '200 €' ?></span>
        </div>
        <div class="flex flex-wrap gap-4">
          <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="bg-primary text-on-primary px-10 py-5 rounded-xl font-label tracking-widest uppercase text-xs hover:opacity-90 transition-all shadow-2xl shadow-primary/20">Demander un devis</a>
          <a href="<?= APP_URL ?>/categories" class="border border-outline-variant/30 text-on-surface px-10 py-5 rounded-xl font-label tracking-widest uppercase text-xs hover:bg-surface-container-low transition-all">Voir les réalisations</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Galerie bento -->
  <section class="py-24 px-8 bg-surface-container-low">
    <div class="max-w-[1440px] mx-auto">
      <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-8">
        <div class="max-w-xl">
          <h2 class="text-5xl font-headline italic mb-4">Inspirations &amp; Réalisations</h2>
          <p class="text-on-surface-variant font-body">Des chantiers d'exception réalisés par nos artisans partenaires.</p>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6" style="height:500px">
        <div class="md:col-span-8 relative overflow-hidden rounded-xl group"><img src="https://images.unsplash.com/photo-1616594039964-ae9021a400a0?w=800&q=80" alt="Réalisation" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
          <div class="absolute bottom-8 left-8 text-white">
            <h3 class="font-headline text-2xl italic">Plomberie & Sanitaire — Finitions premium</h3>
          </div>
        </div>
        <div class="md:col-span-4 grid grid-rows-2 gap-6">
          <div class="relative overflow-hidden rounded-xl group"><img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80" alt="Détail" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"></div>
          <div class="relative overflow-hidden rounded-xl group"><img src="https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800&q=80" alt="Chantier" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Services -->
  <section class="py-24 px-8 bg-surface">
    <div class="max-w-[1440px] mx-auto">
      <div class="text-center mb-16">
        <span class="font-label text-primary tracking-[0.2em] uppercase text-xs mb-4 block">Nos Expertises</span>
        <h2 class="text-5xl font-headline">Des prestations <span class="italic">sur-mesure</span></h2>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-px bg-outline-variant/10 overflow-hidden rounded-3xl border border-outline-variant/10 shadow-sm">
        <?php $svcs = !empty($services) ? $services : array_map(function ($s) {
          return ['nom' => $s, 'description' => 'Artisans qualifies pour ce type de prestation.'];
        }, ['Reparation de fuites', 'Installation sanitaire', 'Debouchage canalisations', 'Renovation salle de bain']);
        foreach ($svcs as $svc): ?>
          <div class="bg-surface p-10 hover:bg-surface-container-high transition-colors group">
            <div class="w-12 h-12 rounded-full border border-primary/20 flex items-center justify-center mb-8 group-hover:bg-primary transition-all"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-primary group-hover:text-on-primary transition-colors">
                <polyline points="20 6 9 17 4 12" />
              </svg></div>
            <h3 class="text-xl font-headline mb-4"><?= Security::e($svc['nom']) ?></h3>
            <p class="text-on-surface-variant font-body text-sm leading-relaxed mb-6"><?= Security::e(substr($svc['description'] ?? '', 0, 100)) ?></p>
            <?php if (!empty($svc['prix_min'])): ?><p class="text-primary font-label text-xs font-bold uppercase tracking-widest mb-4"><?= number_format($svc['prix_min'], 0, ',', ' ') ?> – <?= number_format($svc['prix_max'], 0, ',', ' ') ?> €</p><?php endif; ?>
            <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="text-primary font-label text-xs tracking-widest uppercase flex items-center gap-2 group-hover:gap-4 transition-all">En savoir plus →</a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Pourquoi + Processus -->
  <section class="py-24 px-8">
    <div class="max-w-[1440px] mx-auto grid grid-cols-1 lg:grid-cols-2 gap-24">
      <div>
        <h2 class="text-4xl font-headline mb-12 italic">Pourquoi nous faire confiance ?</h2>
        <div class="space-y-10">
          <?php foreach ([["Artisans certifiés", "Une sélection rigoureuse des meilleurs professionnels de votre région."], ["Devis gratuit", "Estimation transparente et détaillée, sans engagement."], ["Garantie décennale", "Tous nos travaux sont couverts par une assurance décennale."]] as $r): ?>
            <div class="flex gap-6 items-start">
              <div class="p-3 bg-tertiary-container rounded-lg flex-shrink-0"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-on-tertiary-container">
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
          <?php foreach ([["Demande de devis", "Décrivez votre projet via notre formulaire en 2 minutes."], ["Mise en relation", "Jusqu\'à 5 artisans locaux qualifiés vous contactent."], ["Réalisation", "Choisissez votre prestataire et lancez vos travaux."]] as $i => $s): ?>
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

  <!-- Top artisans -->
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
              <div class="flex items-center gap-2 mb-3"><span class="text-yellow-400"><?= str_repeat('★', (int)round($a['rating_avg'] ?? 0)) . str_repeat('☆', 5 - (int)round($a['rating_avg'] ?? 0)) ?></span><span class="text-sm font-semibold"><?= number_format((float)($a['rating_avg'] ?? 0), 1) ?></span></div>
              <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="block w-full text-center bg-primary text-on-primary py-2.5 rounded-full text-sm font-semibold hover:opacity-90 transition-all">Demander un devis</a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- FAQ -->
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

  <!-- Catégories liées -->
  <?php if (!empty($relatedCats)): ?>
    <section class="py-16 px-8">
      <div class="max-w-[1440px] mx-auto">
        <h2 class="font-headline text-3xl mb-8 text-center">Autres corps de métiers</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <?php foreach ($relatedCats as $rel): ?><a href="<?= APP_URL ?>/categorie/<?= Security::e($rel['slug']) ?>" class="group bg-surface-container-low border border-outline-variant/20 rounded-xl p-6 text-center hover:border-primary hover:-translate-y-1 hover:shadow-md transition-all">
              <h3 class="font-headline text-lg mb-1 group-hover:text-primary transition-colors"><?= Security::e($rel['name']) ?></h3><?php if (!empty($rel['prix_min'])): ?><p class="text-xs text-primary font-semibold">À partir de <?= number_format($rel['prix_min'], 0, ',', ' ') ?> €</p><?php endif; ?>
            </a><?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- CTA final -->
  <section class="py-16 px-8">
    <div class="max-w-[1440px] mx-auto">
      <div class="bg-primary rounded-[2rem] p-16 md:p-24 text-center relative overflow-hidden">
        <div class="absolute top-0 right-0 w-1/3 h-full bg-white/5 skew-x-12 translate-x-1/2"></div>
        <h2 class="font-headline text-5xl md:text-6xl text-on-primary mb-8 italic relative z-10">Prêt pour vos travaux de <span class="not-italic"><?= Security::e($category['name']) ?></span> ?</h2>
        <p class="text-on-primary/80 text-lg mb-12 max-w-2xl mx-auto font-body relative z-10">Obtenez des offres comparatives gratuites de la part d'artisans certifiés près de chez vous.</p>
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