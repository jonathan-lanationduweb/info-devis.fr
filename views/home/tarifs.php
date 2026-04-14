<?php /* views/home/tarifs.php */ ?>
<main class="pt-32 pb-24">

  <!-- Hero -->
  <section class="max-w-7xl mx-auto px-8 mb-24">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
      <div>
        <h1 class="text-6xl md:text-7xl font-headline leading-tight mb-8 text-on-background">
          Propulsez votre <span class="italic text-primary">activité d'artisan</span>
        </h1>
        <p class="text-xl font-body text-on-surface-variant leading-relaxed max-w-xl mb-12">
          Accédez à des leads qualifiés, renforcez votre crédibilité avec nos badges vérifiés et gérez vos demandes depuis un seul tableau de bord.
        </p>
        <div class="flex flex-wrap gap-4">
          <div class="flex items-center gap-3 py-3 px-5 bg-surface-container rounded-full border border-outline-variant/15">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-primary">
              <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
              <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            <span class="font-label text-sm font-semibold uppercase tracking-wider">Badge Vérifié</span>
          </div>
          <div class="flex items-center gap-3 py-3 px-5 bg-surface-container rounded-full border border-outline-variant/15">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-primary">
              <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
            </svg>
            <span class="font-label text-sm font-semibold uppercase tracking-wider">Leads Prioritaires</span>
          </div>
        </div>
      </div>
      <div class="relative h-[500px] rounded-xl overflow-hidden shadow-2xl">
        <img src="<?= APP_URL ?>/assets/img/artisan.png" alt="Artisan professionnel" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-primary/40 to-transparent"></div>
        <div class="absolute bottom-8 left-8 right-8 p-8 bg-white/80 backdrop-blur-xl rounded-lg">
          <p class="font-headline italic text-xl text-on-background mb-3">"InfoDevis a transformé mon entreprise. La qualité des contacts est incomparable."</p>
          <p class="font-label text-sm font-bold uppercase tracking-widest text-primary">— Marc Lefebvre, Ébéniste</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Plans tarifaires -->
  <section class="max-w-7xl mx-auto px-8 mb-32">
    <div class="text-center mb-20">
      <h2 class="text-4xl font-headline text-on-background mb-4">Choisissez la formule qui vous correspond</h2>
      <p class="font-body text-on-surface-variant">Des solutions flexibles pour les artisans en quête d'excellence.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-stretch">

      <!-- Gratuit -->
      <div class="bg-surface-container-low p-8 flex flex-col border border-outline-variant/10 rounded-xl hover:border-primary/20 transition-all">
        <div class="mb-8">
          <span class="font-label text-xs font-bold uppercase tracking-widest text-on-surface-variant block mb-3">Essentiel</span>
          <h3 class="text-3xl font-headline mb-2">Gratuit</h3>
          <div class="flex items-baseline gap-1 mb-2"><span class="text-4xl font-headline font-bold">0€</span><span class="text-on-surface-variant font-body">/mois</span></div>
          <p class="text-on-surface-variant font-body text-sm">Pour découvrir la plateforme</p>
        </div>
        <div class="flex-grow space-y-3 mb-8">
          <?php foreach (['3 leads par mois', 'Profil basique', 'Chat clients'] as $f): ?>
            <div class="flex items-start gap-3"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="text-primary flex-shrink-0 mt-0.5">
                <polyline points="20 6 9 17 4 12" />
              </svg><span class="font-body text-sm"><?= $f ?></span></div>
          <?php endforeach; ?>
          <?php foreach (['Badge vérifié', 'Mise en avant', 'Statistiques avancées'] as $f): ?>
            <div class="flex items-start gap-3 opacity-35"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="flex-shrink-0 mt-0.5">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg><span class="font-body text-sm"><?= $f ?></span></div>
          <?php endforeach; ?>
        </div>
        <a href="<?= APP_URL ?>/inscription?type=artisan" class="w-full py-4 border border-primary text-primary font-bold text-center rounded-lg hover:bg-primary hover:text-white transition-all block">S'inscrire gratuitement</a>
      </div>

      <!-- Starter -->
      <div class="bg-surface-container-low p-8 flex flex-col border border-outline-variant/10 rounded-xl hover:border-primary/20 transition-all">
        <div class="mb-8">
          <span class="font-label text-xs font-bold uppercase tracking-widest text-on-surface-variant block mb-3">Croissance</span>
          <h3 class="text-3xl font-headline mb-2">Starter</h3>
          <div class="flex items-baseline gap-1 mb-2"><span class="text-4xl font-headline font-bold text-primary">49€</span><span class="text-on-surface-variant font-body">/mois</span></div>
          <p class="text-on-surface-variant font-body text-sm">Pour les artisans actifs</p>
        </div>
        <div class="flex-grow space-y-3 mb-8">
          <?php foreach (['20 leads par mois', 'Profil optimisé', 'Chat clients', 'Badge vérifié'] as $f): ?>
            <div class="flex items-start gap-3"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="text-primary flex-shrink-0 mt-0.5">
                <polyline points="20 6 9 17 4 12" />
              </svg><span class="font-body text-sm"><?= $f ?></span></div>
          <?php endforeach; ?>
          <?php foreach (['Mise en avant premium', 'Statistiques avancées'] as $f): ?>
            <div class="flex items-start gap-3 opacity-35"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="flex-shrink-0 mt-0.5">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
              </svg><span class="font-body text-sm"><?= $f ?></span></div>
          <?php endforeach; ?>
        </div>
        <a href="<?= APP_URL ?>/inscription?type=artisan&plan=starter" class="w-full py-4 border border-primary text-primary font-bold text-center rounded-lg hover:bg-primary hover:text-white transition-all block">Choisir Starter</a>
      </div>

      <!-- Pro -->
      <div class="bg-white p-8 flex flex-col relative shadow-xl border-t-4 border-primary rounded-xl scale-105 z-10">
        <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary text-white px-4 py-1 text-xs font-bold uppercase tracking-widest rounded-full whitespace-nowrap">⭐ Plus Populaire</div>
        <div class="mb-8">
          <span class="font-label text-xs font-bold uppercase tracking-widest text-primary block mb-3">Excellence</span>
          <h3 class="text-3xl font-headline mb-2">Pro</h3>
          <div class="flex items-baseline gap-1 mb-2"><span class="text-4xl font-headline font-bold text-primary">99€</span><span class="text-on-surface-variant font-body">/mois</span></div>
          <p class="text-on-surface-variant font-body text-sm">Pour les pros en croissance</p>
        </div>
        <div class="flex-grow space-y-3 mb-8">
          <?php foreach (['50 leads par mois', 'Profil premium mis en avant', 'Chat clients prioritaire', 'Badge vérifié Pro', 'Mise en avant premium', 'Statistiques avancées'] as $f): ?>
            <div class="flex items-start gap-3"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="text-primary flex-shrink-0 mt-0.5">
                <polyline points="20 6 9 17 4 12" />
              </svg><span class="font-body text-sm font-semibold"><?= $f ?></span></div>
          <?php endforeach; ?>
        </div>
        <a href="<?= APP_URL ?>/inscription?type=artisan&plan=pro" class="w-full py-4 bg-primary text-white font-bold text-center rounded-lg hover:opacity-90 transition-all block">Choisir Pro</a>
      </div>

      <!-- Illimité -->
      <div class="bg-surface-container-low p-8 flex flex-col border border-outline-variant/10 rounded-xl hover:border-primary/20 transition-all">
        <div class="mb-8">
          <span class="font-label text-xs font-bold uppercase tracking-widest text-on-surface-variant block mb-3">Prestige</span>
          <h3 class="text-3xl font-headline mb-2">Illimité</h3>
          <div class="flex items-baseline gap-1 mb-2"><span class="text-4xl font-headline font-bold">199€</span><span class="text-on-surface-variant font-body">/mois</span></div>
          <p class="text-on-surface-variant font-body text-sm">Pour les grandes entreprises</p>
        </div>
        <div class="flex-grow space-y-3 mb-8">
          <?php foreach (['Leads illimités', 'Top du classement garanti', 'Gestionnaire dédié', 'Badge Partenaire Premium', 'Mise en avant maximale', 'Dashboard personnalisé'] as $f): ?>
            <div class="flex items-start gap-3"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="text-primary flex-shrink-0 mt-0.5">
                <polyline points="20 6 9 17 4 12" />
              </svg><span class="font-body text-sm"><?= $f ?></span></div>
          <?php endforeach; ?>
        </div>
        <a href="<?= APP_URL ?>/inscription?type=artisan&plan=illimite" class="w-full py-4 border border-primary text-primary font-bold text-center rounded-lg hover:bg-primary hover:text-white transition-all block">Choisir Illimité</a>
      </div>
    </div>

    <!-- ── CORRECTION : Lead individuel → vers abonnement artisan ── -->
    <div class="mt-12 text-center p-8 bg-surface-container rounded-2xl max-w-lg mx-auto border border-outline-variant/10">
      <h3 class="font-headline text-xl mb-3">💡 Ou achetez des leads à l'unité</h3>
      <p class="text-on-surface-variant font-body mb-5">
        Pas d'abonnement ? Achetez des leads individuellement à <strong class="text-primary">9,90 €</strong> par lead.
      </p>
      <?php if (!empty($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'artisan'): ?>
        <a href="<?= APP_URL ?>/dashboard/artisan/abonnement"
          class="inline-block bg-primary text-white px-8 py-3 rounded-lg font-bold hover:opacity-90 transition-all">
          Acheter des leads
        </a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/inscription?type=artisan"
          class="inline-block bg-primary text-white px-8 py-3 rounded-lg font-bold hover:opacity-90 transition-all">
          Créer un compte et payer des leads
        </a>
      <?php endif; ?>
    </div>
  </section>

  <!-- Bento features -->
  <section class="max-w-7xl mx-auto px-8 mb-32">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4" style="min-height:400px">
      <div class="md:col-span-2 bg-surface-container p-12 flex flex-col justify-end rounded-xl relative overflow-hidden">
        <div class="relative z-10">
          <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="text-primary mb-5">
            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <h3 class="text-2xl font-headline mb-3">Analytique de Précision</h3>
          <p class="font-body text-on-surface-variant text-sm">Visualisez vos performances avec des rapports détaillés sur la conversion de vos leads.</p>
        </div>
      </div>
      <div class="md:col-span-2 bg-primary text-white p-8 flex flex-col justify-center rounded-xl">
        <h3 class="text-2xl font-headline mb-4 italic">Le Badge de Confiance</h3>
        <p class="font-body text-white/80 mb-5 text-sm">Les artisans arborant notre badge "Vérifié" voient leur taux de conversion augmenter de 42% en moyenne.</p>
        <a href="<?= APP_URL ?>/inscription?type=artisan" class="inline-flex items-center gap-2 font-label text-sm font-bold uppercase tracking-widest hover:gap-4 transition-all">En savoir plus →</a>
      </div>
      <div class="bg-surface-container-highest p-8 flex flex-col justify-center rounded-xl">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" class="text-primary mb-4">
          <path d="M18 20V10M12 20V4M6 20v-6" />
        </svg>
        <h4 class="font-headline text-lg mb-2">Conciergerie Pro</h4>
        <p class="font-body text-xs text-on-surface-variant">Un support humain, réactif et expert pour vous accompagner.</p>
      </div>
      <div class="bg-secondary-container p-8 flex flex-col justify-center rounded-xl">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" class="text-primary mb-4">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
          <path d="M7 11V7a5 5 0 0110 0v4" />
        </svg>
        <h4 class="font-headline text-lg mb-2">Paiements Sécurisés</h4>
        <p class="font-body text-xs text-on-surface-variant">Une infrastructure de paiement robuste pour votre sérénité.</p>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="max-w-3xl mx-auto px-8 mb-24">
    <h2 class="text-3xl font-headline text-center mb-12">Questions fréquentes</h2>
    <div class="space-y-8">
      <?php foreach (
        [
          ['Comment sont qualifiés les leads ?', 'Chaque demande passe par un processus de vérification en trois étapes : identité, nature du projet et délai de réalisation.'],
          ['Puis-je changer de forfait à tout moment ?', 'Absolument. Votre abonnement est flexible et peut être ajusté selon la saisonnalité de votre activité.'],
          ["Qu'est-ce que la visibilité prioritaire ?", "C'est la garantie que votre profil sera affiché dans les trois premiers résultats de recherche pour votre zone géographique."],
        ] as $faq
      ): ?>
        <div class="border-b border-outline-variant/20 pb-6">
          <h4 class="font-headline text-xl mb-2"><?= $faq[0] ?></h4>
          <p class="font-body text-on-surface-variant text-sm"><?= $faq[1] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

</main>