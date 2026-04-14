<?php

/**
 * views/blog/index.php
 * Liste des articles — design éditorial Tailwind
 */

$catIcons = [
  'Toiture'     => '🏠',
  'Chauffage'   => '🔥',
  'Maçonnerie'  => '⬛',
  'Plomberie'   => '🔧',
  'Isolation'   => '🧱',
  'Rénovation'  => '🏗️',
  'Électricité' => '⚡',
  'Peinture'    => '🖌️',
  'Jardinage'   => '🌿',
];
?>
<main class="pt-32 pb-24">

  <!-- En-tête -->
  <header class="max-w-screen-2xl mx-auto px-8 mb-20 text-center">
    <span class="inline-block mb-4 text-primary font-bold tracking-wide uppercase text-sm">
      ✏️ Conseils &amp; guides travaux
    </span>
    <h1 class="font-headline text-5xl md:text-7xl text-on-surface font-bold mb-6 tracking-tight">
      Blog InfoDevis
    </h1>
    <p class="font-body text-lg md:text-xl text-on-surface-variant max-w-3xl mx-auto leading-relaxed">
      Guides de prix, conseils de pros et astuces pour réussir vos travaux en toute sérénité.
    </p>
  </header>

  <!-- Grille articles -->
  <section class="max-w-screen-2xl mx-auto px-8">
    <?php if (empty($articles)): ?>
      <div class="text-center py-24">
        <div class="text-6xl mb-6">📝</div>
        <h2 class="font-headline text-3xl mb-4">Aucun article pour le moment</h2>
        <p class="text-on-surface-variant">Revenez bientôt, nos experts préparent du contenu pour vous.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-16">
        <?php foreach ($articles as $article): ?>
          <article class="group">

            <!-- Image -->
            <a href="<?= APP_URL ?>/blog/<?= Security::e($article['slug']) ?>"
              class="relative aspect-[16/10] overflow-hidden rounded-xl mb-6 bg-surface-container shadow-sm block">
              <?php if (!empty($article['image'])): ?>
                <img src="<?= Security::e($article['image']) ?>"
                  alt="<?= Security::e($article['title']) ?>"
                  class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                  loading="lazy">
              <?php else: ?>
                <div class="w-full h-full bg-gradient-to-br from-primary/10 to-surface-container-high flex items-center justify-center">
                  <span class="text-5xl"><?= $catIcons[$article['category']] ?? '📄' ?></span>
                </div>
              <?php endif; ?>
            </a>

            <!-- Meta -->
            <div class="flex items-center justify-between mb-4">
              <span class="font-label text-xs font-bold uppercase tracking-widest text-primary bg-primary/10 px-3 py-1 rounded-full flex items-center gap-1">
                <?= $catIcons[$article['category']] ?? '📄' ?> <?= Security::e($article['category']) ?>
              </span>
              <span class="text-on-surface-variant text-xs font-medium">
                <?= !empty($article['created_at']) ? date('d/m/Y', strtotime($article['created_at'])) : '' ?>
              </span>
            </div>

            <!-- Titre -->
            <h3 class="font-headline text-2xl font-bold mb-3 text-on-surface group-hover:text-primary transition-colors leading-snug">
              <a href="<?= APP_URL ?>/blog/<?= Security::e($article['slug']) ?>">
                <?= Security::e($article['title']) ?>
              </a>
            </h3>

            <!-- Extrait -->
            <p class="text-on-surface-variant text-sm mb-4 line-clamp-2 leading-relaxed">
              <?= Security::e($article['excerpt'] ?? '') ?>
            </p>

            <!-- Pied -->
            <div class="flex items-center justify-between border-t border-outline-variant/10 pt-4">
              <div class="flex items-center gap-2 text-on-surface-variant/80">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="flex-shrink-0">
                  <circle cx="12" cy="12" r="10" />
                  <polyline points="12 6 12 12 16 14" />
                </svg>
                <span class="text-xs font-medium"><?= Security::e($article['read_time'] ?? '5 min') ?> de lecture</span>
              </div>
              <a href="<?= APP_URL ?>/blog/<?= Security::e($article['slug']) ?>"
                class="text-primary text-xs font-label font-bold uppercase tracking-widest hover:gap-2 flex items-center gap-1 transition-all">
                Lire →
              </a>
            </div>

          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- CTA Banner -->
  <section class="max-w-screen-2xl mx-auto px-8 mt-32">
    <div class="relative overflow-hidden bg-primary py-20 px-12 rounded-2xl flex flex-col md:flex-row items-center justify-between gap-12 text-on-primary">
      <div class="relative z-10 max-w-2xl">
        <h2 class="font-headline text-4xl md:text-5xl font-bold mb-6">Prêt à lancer vos travaux ?</h2>
        <p class="text-lg opacity-90 leading-relaxed font-body">
          Obtenez jusqu'à 5 devis gratuits d'artisans qualifiés près de chez vous.
        </p>
      </div>
      <div class="relative z-10">
        <a href="<?= APP_URL ?>/devis"
          class="bg-white text-primary px-10 py-5 rounded-lg text-lg font-bold shadow-xl hover:scale-105 transition-transform duration-300 inline-block">
          Demander un devis gratuit
        </a>
      </div>
      <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-black/10 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
    </div>
  </section>

</main>