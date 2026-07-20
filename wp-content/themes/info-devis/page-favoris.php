<?php
/**
 * Template de la page « Mes favoris » (slug: favoris).
 * Les favoris sont stockés côté client (localStorage) et rendus par mobile.js
 * → fonctionne hors ligne et sans compte. Concept « Favoris » de la spec §21.
 */
get_header();
?>
<div class="pt-28 md:pt-32 pb-16 px-6 md:px-10 max-w-screen-xl mx-auto idv-screen">

  <header class="mb-8 md:mb-12">
    <div class="flex items-center gap-3 mb-3">
      <span class="bg-primary/10 text-primary px-3 py-1 text-[10px] font-bold tracking-[0.2em] uppercase rounded-full">Ma sélection</span>
      <span class="h-px w-10 bg-outline-variant/30"></span>
    </div>
    <h1 class="font-headline italic text-4xl md:text-5xl font-bold text-on-surface leading-tight">Mes favoris</h1>
    <p class="text-on-surface-variant mt-3 max-w-xl">Retrouvez ici les professionnels et réalisations que vous avez enregistrés. Votre sélection est conservée sur cet appareil, même hors connexion.</p>
  </header>

  <!-- État vide -->
  <div id="idv-favoris-empty" class="text-center py-20">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#aeb3b2" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-6">
      <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
    </svg>
    <h2 class="font-headline italic text-2xl mb-3">Aucun favori pour l'instant</h2>
    <p class="text-on-surface-variant mb-8 max-w-md mx-auto">Parcourez les professionnels et touchez le cœur pour les ajouter à votre sélection.</p>
    <a href="<?php echo esc_url(home_url('/professionnels/')); ?>" class="inline-block bg-primary text-white px-8 py-4 rounded-lg font-bold uppercase tracking-widest text-xs hover:opacity-90 transition-all">Découvrir les professionnels</a>
  </div>

  <!-- Grille des favoris (remplie par mobile.js) -->
  <div id="idv-favoris-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"></div>

</div>
<?php get_footer(); ?>
