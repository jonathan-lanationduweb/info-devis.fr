<?php /* views/client/dashboard.php */ ?>
<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24
  }
</style>

<aside class="fixed left-0 top-20 h-[calc(100vh-5rem)] w-64 bg-[#faf9f8] border-r border-[#aeb3b2]/15 flex flex-col py-8 px-4 gap-2 z-40">
  <div class="mb-8 px-4">
    <h3 class="font-label font-medium uppercase tracking-widest text-xs text-stone-500">Espace Membre</h3>
    <p class="text-[10px] text-stone-400 mt-1">Espace client</p>
  </div>
  <nav class="flex-1 flex flex-col gap-1">
    <a href="<?= APP_URL ?>/dashboard/client" class="flex items-center gap-3 bg-primary/5 text-primary rounded-lg px-4 py-3 font-bold">
      <span class="material-symbols-outlined">dashboard</span>
      <span class="font-label uppercase tracking-widest text-xs">Tableau de bord</span>
    </a>
    <a href="<?= APP_URL ?>/dashboard/client/devis" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">architecture</span>
      <span class="font-label uppercase tracking-widest text-xs">Mes Projets</span>
    </a>
    <a href="<?= APP_URL ?>/dashboard/client/messages" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">chat_bubble</span>
      <span class="font-label uppercase tracking-widest text-xs">Messages</span>
    </a>
    <a href="<?= APP_URL ?>/dashboard/client/avis" class="flex items-center gap-3 text-stone-500 px-4 py-3 hover:bg-stone-100 transition-colors rounded-lg">
      <span class="material-symbols-outlined">star_rate</span>
      <span class="font-label uppercase tracking-widest text-xs">Mes Avis</span>
    </a>
  </nav>
  <div class="p-4 bg-surface-container-low rounded-xl border border-outline-variant/10">
    <p class="text-xs font-semibold text-on-surface mb-2">Besoin d'aide ?</p>
    <p class="text-[11px] text-on-surface-variant leading-relaxed">Un conseiller vous accompagne dans vos projets.</p>
    <a href="<?= APP_URL ?>/contact" class="mt-3 text-primary text-xs font-bold flex items-center gap-1 hover:gap-2 transition-all">
      Contactez-nous <span class="material-symbols-outlined text-sm">arrow_forward</span>
    </a>
  </div>
</aside>

<main class="ml-64 pt-32 px-12 pb-20">
  <header class="mb-12">
    <h1 class="font-headline text-5xl font-medium tracking-tight italic mb-2">
      Bienvenue, <?= Security::e(explode(' ', $_SESSION['user_name'] ?? 'Client')[0]) ?>
    </h1>
    <p class="text-on-surface-variant font-body opacity-80">Voici un aperçu de vos demandes de devis et interactions récentes.</p>
  </header>

  <?php
  $totalDevis = count($devis ?? []);
  $enCours    = count(array_filter($devis ?? [], fn($d) => in_array($d['status'], ['sent', 'in_progress'])));
  $termines   = count(array_filter($devis ?? [], fn($d) => $d['status'] === 'completed'));
  $nbNotifs   = count($notifs ?? []);
  ?>
  <section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-16">
    <div class="bg-surface-container-lowest p-8 rounded-2xl border border-outline-variant/10 shadow-sm hover:shadow-md transition-all">
      <span class="material-symbols-outlined text-primary mb-4 block" style="font-size:32px">description</span>
      <p class="text-3xl font-headline italic font-bold"><?= str_pad($totalDevis, 2, '0', STR_PAD_LEFT) ?></p>
      <p class="text-xs uppercase tracking-widest text-on-surface-variant mt-1 font-semibold">Total demandes</p>
    </div>
    <div class="bg-surface-container-lowest p-8 rounded-2xl border border-outline-variant/10 shadow-sm hover:shadow-md transition-all">
      <span class="material-symbols-outlined text-tertiary mb-4 block" style="font-size:32px">pending_actions</span>
      <p class="text-3xl font-headline italic font-bold"><?= str_pad($enCours, 2, '0', STR_PAD_LEFT) ?></p>
      <p class="text-xs uppercase tracking-widest text-on-surface-variant mt-1 font-semibold">En cours</p>
    </div>
    <div class="bg-surface-container-lowest p-8 rounded-2xl border border-outline-variant/10 shadow-sm hover:shadow-md transition-all">
      <span class="material-symbols-outlined text-secondary mb-4 block" style="font-size:32px">task_alt</span>
      <p class="text-3xl font-headline italic font-bold"><?= str_pad($termines, 2, '0', STR_PAD_LEFT) ?></p>
      <p class="text-xs uppercase tracking-widest text-on-surface-variant mt-1 font-semibold">Terminées</p>
    </div>
    <div class="bg-primary-container p-8 rounded-2xl border border-primary/10 shadow-sm hover:shadow-md transition-all relative overflow-hidden">
      <div class="relative z-10">
        <span class="material-symbols-outlined text-on-primary-container mb-4 block" style="font-size:32px">notifications_active</span>
        <p class="text-3xl font-headline italic font-bold text-on-primary-container"><?= str_pad($nbNotifs, 2, '0', STR_PAD_LEFT) ?></p>
        <p class="text-xs uppercase tracking-widest text-on-primary-container mt-1 font-bold">Notifications</p>
      </div>
      <div class="absolute -right-4 -bottom-4 opacity-10 pointer-events-none">
        <span class="material-symbols-outlined" style="font-size:120px">bolt</span>
      </div>
    </div>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
    <!-- Dernières demandes -->
    <div class="lg:col-span-2 space-y-8">
      <div class="flex justify-between items-baseline mb-4">
        <h2 class="font-headline text-2xl font-bold">Dernières Demandes</h2>
        <a href="<?= APP_URL ?>/dashboard/client/devis" class="text-xs font-bold uppercase tracking-widest text-primary border-b border-primary/30 pb-0.5 hover:border-primary transition-colors">Tout voir</a>
      </div>
      <?php if (empty($devis)): ?>
        <div class="p-12 rounded-2xl bg-surface-container-low/50 text-center">
          <span class="material-symbols-outlined text-5xl text-outline-variant mb-4 block">inbox</span>
          <p class="text-on-surface-variant mb-4">Aucune demande pour le moment</p>
          <a href="<?= APP_URL ?>/devis" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-label text-xs uppercase tracking-widest font-bold hover:opacity-90 inline-block">Faire ma première demande</a>
        </div>
      <?php else: ?>
        <div class="space-y-6">
          <?php
          $sLabels = ['sent' => ['en attente', 'bg-surface-container-high text-on-surface-variant'], 'in_progress' => ['en cours', 'bg-tertiary-container text-on-tertiary-container'], 'completed' => ['terminé', 'bg-primary/10 text-primary'], 'cancelled' => ['annulé', 'bg-error-container text-on-error-container']];
          foreach (array_slice($devis, 0, 5) as $d):
            [$sl, $sc] = $sLabels[$d['status']] ?? ['inconnu', 'bg-gray-100 text-gray-600'];
          ?>
            <div class="group flex items-start gap-6 p-6 rounded-2xl bg-surface-container-low/50 hover:bg-surface-container-lowest transition-all border border-transparent hover:border-outline-variant/10">
              <div class="w-24 h-24 rounded-xl bg-surface-container-high flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-4xl text-outline-variant">home_repair_service</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start mb-2">
                  <span class="<?= $sc ?> text-[10px] uppercase font-bold tracking-widest px-2 py-0.5 rounded"><?= $sl ?></span>
                  <span class="text-[11px] text-on-surface-variant font-medium flex-shrink-0 ml-2"><?= !empty($d['created_at']) ? date('d M Y', strtotime($d['created_at'])) : '' ?></span>
                </div>
                <h4 class="font-headline text-xl font-bold mb-1 truncate"><?= Security::e($d['title'] ?? 'Demande sans titre') ?></h4>
                <p class="text-sm text-on-surface-variant leading-relaxed line-clamp-2"><?= Security::e(substr($d['description'] ?? '', 0, 120)) ?></p>
                <div class="mt-4 flex items-center gap-4">
                  <span class="text-xs font-bold flex items-center gap-1"><span class="material-symbols-outlined text-base">location_on</span><?= Security::e($d['ville'] ?? '') ?></span>
                  <span class="text-xs font-bold flex items-center gap-1"><span class="material-symbols-outlined text-base">tag</span>Réf. <?= Security::e($d['reference'] ?? '') ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="space-y-10">
      <div class="bg-on-surface text-surface p-10 rounded-2xl shadow-xl relative overflow-hidden">
        <div class="relative z-10">
          <h3 class="font-headline text-3xl font-bold mb-4 leading-tight">Un nouveau projet en tête ?</h3>
          <p class="text-sm text-surface/70 mb-8 leading-relaxed">Laissez notre réseau d'artisans d'excellence s'occuper du reste.</p>
          <a href="<?= APP_URL ?>/devis" class="w-full bg-primary text-on-primary py-4 rounded-lg font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all flex justify-center items-center gap-2">
            Nouvelle demande <span class="material-symbols-outlined text-sm">add_circle</span>
          </a>
        </div>
        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent opacity-50 pointer-events-none"></div>
      </div>

      <div class="space-y-4">
        <h3 class="font-label uppercase tracking-widest text-[10px] text-stone-500 mb-2">Accès Rapide</h3>
        <?php
        $quickLinks = [
          ['/dashboard/client/messages', 'forum', 'Messages', 'Conversations avec vos artisans', 'text-primary'],
          ['/dashboard/client/avis', 'star_rate', 'Mes Avis', 'Partagez votre expérience', 'text-tertiary'],
          ['/dashboard/client/devis', 'receipt_long', 'Mes demandes', 'Suivre mes devis', 'text-secondary'],
        ];
        foreach ($quickLinks as [$url, $icon, $title, $sub, $color]):
        ?>
          <a href="<?= APP_URL . $url ?>" class="group flex items-center justify-between p-5 bg-surface-container-low rounded-xl border border-outline-variant/10 hover:border-primary/30 transition-all">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-full bg-surface-container-highest flex items-center justify-center <?= $color ?> group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined"><?= $icon ?></span>
              </div>
              <div>
                <p class="text-sm font-bold text-on-surface"><?= $title ?></p>
                <p class="text-[10px] text-on-surface-variant"><?= $sub ?></p>
              </div>
            </div>
            <span class="material-symbols-outlined text-outline group-hover:text-primary transition-colors">chevron_right</span>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="p-8 bg-surface-container-highest/30 rounded-2xl border-l-2 border-primary/20 relative">
        <span class="font-headline text-6xl text-primary/10 absolute top-4 left-4 select-none italic">"</span>
        <p class="font-headline text-lg italic text-on-tertiary-fixed leading-relaxed mb-4 relative z-10">
          La qualité d'un devis ne se mesure pas seulement au prix, mais à la précision des détails techniques.
        </p>
        <p class="text-[10px] uppercase font-extrabold tracking-widest text-primary">— L'Expert Info-Devis</p>
      </div>
    </div>
  </div>
</main>