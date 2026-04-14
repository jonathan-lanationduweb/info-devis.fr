<?php
/**
 * views/blog/show.php
 * Article individuel — design éditorial Tailwind
 * Variables : $article, $relatedArticles (optionnel)
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
$icon = $catIcons[$article['category'] ?? ''] ?? '📄';
?>
<main class="pt-32">

  <!-- En-tête éditorial -->
  <header class="max-w-5xl mx-auto px-8 mb-16">
    <div class="flex flex-col md:flex-row items-end gap-12 mb-16">
      <div class="md:w-2/3">
        <p class="font-label uppercase tracking-[0.2em] text-primary mb-6 text-sm font-semibold">
          Le Curateur de l'Habitat — Guide Expert
        </p>
        <h1 class="font-headline text-5xl md:text-7xl leading-tight text-on-surface italic">
          <?= Security::e($article['title']) ?>
        </h1>
      </div>
      <div class="md:w-1/3 pb-4">
        <div class="flex items-center gap-3 mb-4">
          <span class="font-label text-xs font-bold uppercase tracking-widest text-primary bg-primary/10 px-3 py-1 rounded-full">
            <?= $icon ?> <?= Security::e($article['category'] ?? '') ?>
          </span>
        </div>
        <p class="text-on-surface-variant font-label uppercase tracking-widest text-xs">
          Publié le <?= !empty($article['created_at']) ? date('d/m/Y', strtotime($article['created_at'])) : '' ?>
        </p>
        <div class="h-px w-24 bg-primary mt-4"></div>
      </div>
    </div>

    <!-- Image hero -->
    <?php if (!empty($article['image'])): ?>
    <div class="relative h-[500px] overflow-hidden rounded-xl">
      <img src="<?= Security::e($article['image']) ?>"
           alt="<?= Security::e($article['title']) ?>"
           class="w-full h-full object-cover">
      <div class="absolute inset-0 bg-gradient-to-t from-on-surface/40 to-transparent"></div>

      <?php if (!empty($article['excerpt'])): ?>
      <div class="absolute bottom-8 left-8 bg-surface-container-lowest/80 backdrop-blur-xl p-6 rounded-lg max-w-sm">
        <p class="font-headline italic text-lg text-on-surface">
          "<?= Security::e(substr($article['excerpt'], 0, 120)) ?>..."
        </p>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </header>

  <!-- Corps de l'article -->
  <div class="max-w-7xl mx-auto px-8 grid grid-cols-1 md:grid-cols-12 gap-16 mb-24">

    <!-- Sidebar -->
    <aside class="md:col-span-3 order-2 md:order-1">
      <div class="sticky top-32 space-y-10">

        <!-- Sommaire si disponible -->
        <div class="bg-surface-container-low p-8 rounded-xl">
          <h3 class="font-label uppercase tracking-widest text-xs font-bold text-primary mb-6">Sommaire</h3>
          <ul class="space-y-4 font-label text-xs uppercase tracking-wider text-on-surface-variant">
            <li><a href="#contenu" class="hover:text-primary transition-colors">→ Lire l'article</a></li>
            <li><a href="#devis" class="hover:text-primary transition-colors">→ Obtenir un devis</a></li>
          </ul>
        </div>

        <!-- Citation -->
        <div class="border-t border-outline-variant/20 pt-8">
          <p class="font-headline italic text-lg text-on-surface mb-4">
            "Un devis gratuit, c'est la première étape vers un projet réussi."
          </p>
          <p class="font-label text-xs uppercase tracking-widest text-on-surface-variant">— L'équipe InfoDevis</p>
        </div>

        <!-- CTA sidebar -->
        <div class="bg-primary p-8 rounded-xl text-on-primary">
          <h4 class="font-headline text-xl italic mb-3">Besoin d'un artisan ?</h4>
          <p class="text-on-primary/80 text-sm mb-6">Comparez jusqu'à 5 devis gratuits sous 24h.</p>
          <a href="<?= APP_URL ?>/devis"
             class="block w-full text-center bg-white text-primary py-3 rounded-lg font-label text-xs uppercase tracking-widest font-bold hover:bg-primary-container transition-all">
            Demander un devis
          </a>
        </div>

      </div>
    </aside>

    <!-- Contenu principal -->
    <article class="md:col-span-9 order-1 md:order-2" id="contenu">

      <!-- Intro -->
      <?php if (!empty($article['excerpt'])): ?>
      <p class="font-body text-xl text-on-surface-variant leading-relaxed mb-12 border-l-2 border-primary-container pl-6">
        <?= Security::e($article['excerpt']) ?>
      </p>
      <?php endif; ?>

      <!-- Contenu HTML de l'article -->
      <?php if (!empty($article['content'])): ?>
      <div class="prose prose-stone prose-lg max-w-none font-body
                  prose-headings:font-headline prose-headings:text-on-surface
                  prose-h2:text-4xl prose-h2:mb-8 prose-h2:mt-16
                  prose-h3:text-2xl prose-h3:mb-6
                  prose-p:text-on-surface-variant prose-p:leading-relaxed prose-p:mb-6
                  prose-strong:text-on-surface
                  prose-a:text-primary prose-a:no-underline hover:prose-a:underline
                  prose-li:text-on-surface-variant
                  prose-blockquote:border-primary prose-blockquote:font-headline prose-blockquote:italic prose-blockquote:text-xl">
        <?= $article['content'] ?>
      </div>
      <?php else: ?>
      <p class="text-on-surface-variant font-body">Contenu de l'article à venir.</p>
      <?php endif; ?>

      <!-- Citation éditoriale -->
      <div class="bg-surface-container-highest p-12 rounded-xl relative overflow-hidden my-16">
        <span class="absolute -top-10 -left-4 text-[10rem] font-headline text-primary opacity-5 leading-none select-none">"</span>
        <div class="relative z-10">
          <h4 class="font-label uppercase tracking-widest text-primary text-xs font-bold mb-6">L'avis de l'expert</h4>
          <blockquote class="font-headline text-2xl md:text-3xl italic text-on-surface leading-snug mb-6">
            "Un projet bien préparé, avec des artisans qualifiés et certifiés, c'est la garantie d'un résultat durable et d'un investissement rentable."
          </blockquote>
          <p class="font-label uppercase tracking-widest text-on-surface-variant text-xs">— L'équipe éditoriale InfoDevis</p>
        </div>
      </div>

      <!-- CTA inline -->
      <div class="bg-primary p-12 rounded-xl flex flex-col md:flex-row items-center justify-between gap-8" id="devis">
        <div>
          <h3 class="font-headline text-3xl text-on-primary mb-2 italic">
            Besoin d'un artisan pour vos travaux de <?= Security::e($article['category'] ?? 'rénovation') ?> ?
          </h3>
          <p class="text-on-primary/80 font-body text-sm">Mise en relation gratuite avec nos artisans certifiés InfoDevis.</p>
        </div>
        <a href="<?= APP_URL ?>/devis"
           class="bg-white text-primary px-10 py-4 rounded-lg font-label uppercase tracking-widest text-xs font-extrabold hover:bg-surface transition-all shadow-xl flex-shrink-0">
          Trouver un artisan
        </a>
      </div>

    </article>
  </div>

  <!-- Articles liés -->
  <?php if (!empty($relatedArticles)): ?>
  <section class="max-w-screen-2xl mx-auto px-8 mb-24">
    <div class="border-t border-outline-variant/20 pt-16">
      <h2 class="font-headline text-4xl mb-12">Articles similaires</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-x-12 gap-y-8">
        <?php foreach ($relatedArticles as $rel): ?>
        <article class="group">
          <a href="<?= APP_URL ?>/blog/<?= Security::e($rel['slug']) ?>"
             class="relative aspect-[16/10] overflow-hidden rounded-xl mb-4 bg-surface-container shadow-sm block">
            <?php if (!empty($rel['image'])): ?>
            <img src="<?= Security::e($rel['image']) ?>" alt="<?= Security::e($rel['title']) ?>"
                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy">
            <?php else: ?>
            <div class="w-full h-full bg-gradient-to-br from-primary/10 to-surface-container-high flex items-center justify-center">
              <span class="text-4xl"><?= $catIcons[$rel['category']] ?? '📄' ?></span>
            </div>
            <?php endif; ?>
          </a>
          <span class="text-primary text-xs font-label uppercase tracking-widest font-bold"><?= $catIcons[$rel['category']] ?? '' ?> <?= Security::e($rel['category'] ?? '') ?></span>
          <h3 class="font-headline text-xl font-bold mt-2 text-on-surface group-hover:text-primary transition-colors">
            <a href="<?= APP_URL ?>/blog/<?= Security::e($rel['slug']) ?>"><?= Security::e($rel['title']) ?></a>
          </h3>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

</main>
