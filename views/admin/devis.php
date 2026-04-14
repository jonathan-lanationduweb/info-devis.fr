<?php /* views/admin/devis.php */ ?>
<?php include BASE_PATH . '/views/admin/_sidebar.php'; ?>

<main class="pl-72 min-h-screen">

  <!-- TopAppBar -->
  <header class="fixed top-0 right-0 left-72 z-40 h-20 bg-[#faf9f8]/80 backdrop-blur-xl flex justify-between items-center px-12 shadow-sm shadow-primary/5">
    <div class="flex items-center gap-2">
      <span class="font-headline italic tracking-tight text-primary">Admin</span>
      <span class="material-symbols-outlined text-xs text-outline-variant">chevron_right</span>
      <span class="font-headline italic tracking-tight text-on-surface">Toutes les demandes</span>
    </div>
    <div class="flex items-center gap-6">
      <div class="relative">
        <span class="material-symbols-outlined text-on-surface/60 cursor-pointer hover:text-primary transition-colors">notifications</span>
        <span class="absolute -top-1 -right-1 w-2 h-2 bg-primary rounded-full"></span>
      </div>
      <span class="material-symbols-outlined text-on-surface/60 cursor-pointer hover:text-primary transition-colors">settings</span>
    </div>
  </header>

  <!-- Content Body -->
  <div class="pt-32 px-12 pb-20 max-w-7xl mx-auto">

    <!-- Breadcrumb -->
    <div class="mb-8">
      <a href="<?= APP_URL ?>/admin" class="font-headline italic text-primary hover:opacity-70 transition-opacity flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">arrow_back</span> Admin
      </a>
      <h1 class="font-headline text-5xl font-light text-on-background mt-4 tracking-tighter">Toutes les demandes</h1>
    </div>

    <!-- Filtres tabs style éditorial -->
    <nav class="flex gap-10 mb-12 border-b border-outline-variant/10 overflow-x-auto">
      <?php
      $tabs = ['' => 'Tous', 'sent' => '⏳ Envoyés', 'in_progress' => '🔨 En cours', 'completed' => '✅ Terminés', 'cancelled' => '❌ Annulés'];
      $activeStatus = $_GET['status'] ?? '';
      foreach ($tabs as $val => $label):
      ?>
        <a href="?status=<?= $val ?>"
          class="pb-4 text-[10px] font-label uppercase tracking-[0.2em] whitespace-nowrap transition-colors
                <?= $activeStatus === $val ? 'border-b-2 border-primary text-primary font-bold' : 'text-on-surface-variant/60 hover:text-primary' ?>">
          <?= $label ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <!-- Tableau -->
    <div class="bg-surface-container-lowest rounded-xl shadow-[0_40px_100px_-20px_rgba(14,108,72,0.05)] overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low/50">
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70 font-semibold">Référence</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70 font-semibold">Client</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70 font-semibold">Catégorie</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70 font-semibold">Ville</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70 font-semibold">Leads</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70 font-semibold">Statut</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant/70 font-semibold">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/5">
          <?php if (empty($devis)): ?>
            <tr class="bg-surface">
              <td colspan="7" class="py-32 text-center">
                <div class="flex flex-col items-center gap-4 opacity-40">
                  <span class="material-symbols-outlined text-6xl font-light">search_off</span>
                  <p class="font-headline italic text-xl">Aucune demande</p>
                  <p class="font-label text-[10px] uppercase tracking-widest">Ajustez vos filtres pour voir plus de résultats</p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php
            $statusCfg = [
              'sent'        => ['bg-yellow-100 text-yellow-700', 'Envoyé'],
              'in_progress' => ['bg-primary/10 text-primary',    'En cours'],
              'completed'   => ['bg-green-100 text-green-700',   'Terminé'],
              'cancelled'   => ['bg-stone-100 text-stone-500',   'Annulé'],
            ];
            foreach ($devis as $d):
              [$scls, $slabel] = $statusCfg[$d['status'] ?? ''] ?? ['bg-stone-100 text-stone-500', ucfirst($d['status'] ?? '?')];
              $initials = strtoupper(substr($d['first_name'] ?? '?', 0, 1) . substr($d['last_name'] ?? '', 0, 1));
              $leadsCount = (int)($d['leads_count'] ?? 0);
            ?>
              <tr class="hover:bg-surface-container-low transition-colors group cursor-pointer">
                <td class="px-8 py-6 font-mono text-xs text-primary"><?= Security::e($d['reference'] ?? '#DV-' . $d['id']) ?></td>
                <td class="px-8 py-6">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container text-[10px] font-bold"><?= $initials ?></div>
                    <div>
                      <p class="font-body text-sm font-medium"><?= Security::e(($d['first_name'] ?? '') . ' ' . ($d['last_name'] ?? '')) ?></p>
                      <p class="text-[10px] text-on-surface-variant"><?= Security::e($d['email'] ?? '') ?></p>
                    </div>
                  </div>
                </td>
                <td class="px-8 py-6 text-sm text-on-surface-variant italic font-headline"><?= Security::e($d['cat_name'] ?? '—') ?></td>
                <td class="px-8 py-6 text-sm"><?= Security::e($d['ville'] ?? '—') ?></td>
                <td class="px-8 py-6">
                  <?php if ($leadsCount > 0): ?>
                    <div class="flex -space-x-2 items-center">
                      <?php for ($i = 0; $i < min($leadsCount, 3); $i++): ?>
                        <div class="w-6 h-6 rounded-full border-2 border-white <?= $i === 0 ? 'bg-primary' : 'bg-tertiary' ?>"></div>
                      <?php endfor; ?>
                      <?php if ($leadsCount > 3): ?>
                        <div class="w-6 h-6 rounded-full border-2 border-white bg-surface-container-highest flex items-center justify-center text-[8px] font-bold">+<?= $leadsCount - 3 ?></div>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-on-surface-variant/40 text-xs">—</span>
                  <?php endif; ?>
                </td>
                <td class="px-8 py-6">
                  <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full <?= $scls ?>"><?= $slabel ?></span>
                </td>
                <td class="px-8 py-6 text-sm text-on-surface-variant/60">
                  <?= !empty($d['created_at']) ? date('d M Y', strtotime($d['created_at'])) : '—' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-12 flex justify-between items-center px-4">
      <div class="text-[10px] font-label uppercase tracking-widest text-on-surface-variant/60">
        Affichage page <?= $page ?> — <?= count($devis ?? []) ?> demande(s)
      </div>
      <div class="flex items-center gap-8">
        <?php if ($page > 1): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
            class="font-headline italic text-primary hover:-translate-x-1 transition-transform">← Précédent</a>
        <?php else: ?>
          <span class="font-headline italic text-primary opacity-50 cursor-not-allowed">← Précédent</span>
        <?php endif; ?>
        <div class="flex items-center gap-4">
          <span class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center text-xs font-bold"><?= $page ?></span>
          <span class="text-xs text-on-surface-variant">Page <?= $page ?></span>
        </div>
        <?php if (!empty($devis) && count($devis) >= 25): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
            class="font-headline italic text-primary hover:translate-x-1 transition-transform">Suivant →</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Section éditoriale signature -->
    <section class="mt-24 grid grid-cols-1 md:grid-cols-2 gap-12">
      <div class="relative bg-surface-container-highest/40 p-12 rounded-2xl overflow-hidden">
        <span class="absolute -top-10 -left-5 text-[180px] font-headline italic opacity-[0.03] select-none text-primary">"</span>
        <h3 class="font-headline text-2xl mb-6 relative z-10 italic">L'excellence dans la sélection</h3>
        <p class="font-body text-sm leading-relaxed text-on-surface-variant relative z-10">
          Chaque demande de devis sur notre plateforme est soumise à un processus de vérification rigoureux. Nous nous assurons que les projets sont qualifiés avant d'être transmis à nos partenaires artisans d'exception.
        </p>
        <div class="mt-8 pt-8 border-t border-outline-variant/20 flex items-center gap-4">
          <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
            <span class="material-symbols-outlined text-primary">person</span>
          </div>
          <div>
            <p class="font-label text-[10px] font-bold uppercase tracking-widest">Sophie Martin</p>
            <p class="font-headline italic text-xs opacity-60">Responsable Qualité</p>
          </div>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-6">
        <div class="bg-primary/5 p-8 rounded-2xl flex flex-col justify-between">
          <span class="material-symbols-outlined text-primary text-3xl">trending_up</span>
          <div>
            <p class="font-headline text-4xl font-light text-primary tracking-tighter"><?= $totalDevis ?? 0 ?></p>
            <p class="font-label text-[9px] uppercase tracking-widest mt-2 opacity-60">Total Demandes</p>
          </div>
        </div>
        <div class="bg-surface-container-high p-8 rounded-2xl flex flex-col justify-between">
          <span class="material-symbols-outlined text-on-surface text-3xl">verified_user</span>
          <div>
            <p class="font-headline text-4xl font-light tracking-tighter"><?= $devisToday ?? 0 ?></p>
            <p class="font-label text-[9px] uppercase tracking-widest mt-2 opacity-60">Aujourd'hui</p>
          </div>
        </div>
      </div>
    </section>

  </div>
</main>