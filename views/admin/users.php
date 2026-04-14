<?php /* views/admin/users.php */ ?>
<?php include BASE_PATH . '/views/admin/_sidebar.php'; ?>

<main class="ml-72 pt-32 px-12 pb-12">
  <div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="flex justify-between items-end mb-12">
      <div>
        <h2 class="font-headline text-4xl font-medium text-on-surface mb-2">Utilisateurs</h2>
        <p class="font-body text-stone-500">Gérez l'accès, les rôles et les statuts des membres de la plateforme.</p>
      </div>
    </div>

    <!-- Filtres + tri -->
    <div class="grid grid-cols-12 gap-6 mb-12">
      <div class="col-span-12 lg:col-span-8 bg-surface-container-low p-1.5 rounded-xl flex items-center gap-1">
        <?php foreach (['' => 'Tous', 'client' => 'Clients', 'artisan' => 'Artisans', 'admin' => 'Admins'] as $r => $l): ?>
          <a href="<?= APP_URL ?>/admin/users<?= $r ? '?role=' . $r : '' ?>"
            class="flex-1 py-3 px-4 rounded-lg font-body text-sm uppercase tracking-wider text-center transition-all
                <?= ($_GET['role'] ?? '') === $r ? 'bg-surface-container-lowest shadow-sm text-primary font-bold' : 'text-stone-500 hover:bg-stone-100/50 font-semibold' ?>">
            <?= $l ?>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="col-span-12 lg:col-span-4 bg-surface-container-low p-4 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-stone-400">filter_list</span>
          <span class="text-sm font-semibold font-body text-stone-600 uppercase tracking-wide">Page <?= $page ?></span>
        </div>
      </div>
    </div>

    <!-- Tableau -->
    <div class="bg-surface-container-lowest rounded-2xl overflow-hidden shadow-sm">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low">
            <th class="py-5 px-8 font-label text-xs font-extrabold uppercase tracking-[0.1em] text-stone-400 border-b border-stone-100">Utilisateur</th>
            <th class="py-5 px-8 font-label text-xs font-extrabold uppercase tracking-[0.1em] text-stone-400 border-b border-stone-100">Email</th>
            <th class="py-5 px-8 font-label text-xs font-extrabold uppercase tracking-[0.1em] text-stone-400 border-b border-stone-100">Rôle</th>
            <th class="py-5 px-8 font-label text-xs font-extrabold uppercase tracking-[0.1em] text-stone-400 border-b border-stone-100">Statut</th>
            <th class="py-5 px-8 font-label text-xs font-extrabold uppercase tracking-[0.1em] text-stone-400 border-b border-stone-100">Inscrit le</th>
          </tr>
        </thead>
        <tbody class="font-body text-sm divide-y divide-stone-50">
          <?php if (empty($users)): ?>
            <tr>
              <td colspan="5" class="text-center py-16 text-on-surface-variant">Aucun utilisateur</td>
            </tr>
          <?php else: ?>
            <?php foreach ($users as $u):
              $roleColors = ['admin' => 'bg-on-primary-container text-primary-container', 'artisan' => 'bg-primary-container text-on-primary-container border border-primary/10', 'client' => 'bg-secondary-container text-on-secondary-container border border-secondary/10'];
              $rc = $roleColors[$u['role']] ?? 'bg-stone-200 text-stone-600';
            ?>
              <tr class="hover:bg-stone-50/50 transition-colors">
                <td class="py-5 px-8">
                  <div class="flex items-center gap-4">
                    <div class="relative">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center font-bold text-primary text-sm">
                        <?= strtoupper(substr($u['first_name'] ?? 'U', 0, 1)) ?>
                      </div>
                      <span class="absolute bottom-0 right-0 w-3 h-3 <?= $u['is_active'] ? 'bg-emerald-500' : 'bg-stone-300' ?> border-2 border-white rounded-full"></span>
                    </div>
                    <div>
                      <div class="font-bold text-on-surface"><?= Security::e($u['first_name'] . ' ' . $u['last_name']) ?></div>
                      <div class="text-xs text-stone-400">ID: #<?= $u['id'] ?></div>
                    </div>
                  </div>
                </td>
                <td class="py-5 px-8 text-stone-500"><?= Security::e($u['email']) ?></td>
                <td class="py-5 px-8">
                  <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest <?= $rc ?>"><?= ucfirst($u['role']) ?></span>
                </td>
                <td class="py-5 px-8">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="w-1.5 h-1.5 rounded-full <?= $u['is_active'] ? 'bg-emerald-500' : 'bg-stone-300' ?>"></span>
                    <span class="<?= $u['is_active'] ? 'text-stone-700' : 'text-stone-400' ?> font-medium">
                      <?= $u['is_active'] ? 'Actif' : 'Inactif' ?>
                    </span>
                    <?php if (!$u['email_verified_at']): ?>
                      <span class="text-[10px] text-yellow-600 font-bold">⚠️ Non vérifié</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="py-5 px-8 text-stone-500">
                  <?= !empty($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : '—' ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Pagination footer -->
      <div class="bg-surface-container-low px-8 py-5 flex justify-between items-center">
        <p class="text-xs font-body text-stone-400 font-semibold uppercase tracking-wider">Page <?= $page ?></p>
        <div class="flex gap-2">
          <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
              class="w-10 h-10 rounded-lg flex items-center justify-center border border-outline-variant/30 text-stone-400 hover:bg-stone-50">
              <span class="material-symbols-outlined text-sm">chevron_left</span>
            </a>
          <?php endif; ?>
          <span class="w-10 h-10 rounded-lg flex items-center justify-center bg-primary text-on-primary text-xs font-bold"><?= $page ?></span>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
            class="w-10 h-10 rounded-lg flex items-center justify-center border border-outline-variant/30 text-stone-600 hover:bg-stone-50">
            <span class="material-symbols-outlined text-sm">chevron_right</span>
          </a>
        </div>
      </div>
    </div>

  </div>
</main>