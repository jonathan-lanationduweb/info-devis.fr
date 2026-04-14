<?php /* views/admin/blog.php */ ?>
<?php include BASE_PATH . '/views/admin/_sidebar.php'; ?>

<main class="ml-72 min-h-screen relative">

  <!-- TopAppBar -->
  <header class="fixed top-0 right-0 left-72 z-40 bg-[#faf9f8]/80 backdrop-blur-xl flex justify-between items-center px-12 h-20 shadow-sm shadow-primary/5">
    <div class="flex items-center gap-4">
      <a href="<?= APP_URL ?>/admin" class="text-on-surface-variant/60 flex items-center gap-2 hover:text-primary transition-colors font-label text-[11px] uppercase tracking-[0.2em]">
        <span class="material-symbols-outlined text-sm">arrow_back</span> Admin
      </a>
    </div>
    <div class="flex items-center gap-8">
      <div class="relative group">
        <span class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-primary transition-colors">notifications</span>
        <span class="absolute -top-1 -right-1 w-2 h-2 bg-primary rounded-full"></span>
      </div>
      <span class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-primary transition-colors">settings</span>
    </div>
  </header>

  <div class="pt-32 px-12 pb-20 max-w-7xl mx-auto">

    <!-- Header -->
    <section class="mb-16">
      <h1 class="font-headline text-5xl italic text-primary tracking-tight mb-4">Gestion blog</h1>
      <p class="font-body text-on-surface-variant max-w-xl leading-relaxed opacity-70">
        Gérez et modérez les publications de la plateforme. Assurez la qualité éditoriale en révisant chaque article soumis par nos experts.
      </p>
    </section>

    <!-- Filtres navigation -->
    <div class="flex gap-12 mb-16 border-b border-outline-variant/10">
      <?php
      $tabs = [
        'pending'   => ['hourglass_empty', 'En attente'],
        'published' => ['check_circle',    'Publiés'],
        'rejected'  => ['cancel',          'Refusés'],
      ];
      $activeStatus = $status ?? 'pending';
      foreach ($tabs as $val => [$icon, $label]):
      ?>
        <a href="?status=<?= $val ?>"
          class="pb-6 flex items-center gap-2 font-label uppercase tracking-widest text-[11px] transition-all
                <?= $activeStatus === $val ? 'border-b-2 border-primary text-primary font-bold' : 'text-on-surface/40 hover:text-primary' ?>">
          <span class="material-symbols-outlined text-lg"><?= $icon ?></span>
          <?= $label ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Contenu articles ou empty state -->
    <?php if (empty($posts)): ?>
      <div class="relative min-h-[500px] flex items-center justify-center bg-surface-container-low rounded-xl overflow-hidden">
        <div class="absolute inset-0 opacity-5 pointer-events-none flex items-center justify-center">
          <span class="font-headline text-[20rem] italic leading-none select-none">Editorial</span>
        </div>
        <div class="relative z-10 text-center flex flex-col items-center">
          <div class="w-24 h-24 mb-8 bg-surface-container-lowest rounded-full flex items-center justify-center shadow-sm shadow-primary/5">
            <span class="material-symbols-outlined text-5xl text-primary" style="font-variation-settings:'FILL' 1,'wght' 300,'GRAD' 0,'opsz' 24">check</span>
          </div>
          <h2 class="font-headline text-3xl italic text-primary mb-3">Sérénité absolue</h2>
          <p class="font-body text-on-surface-variant/80 uppercase tracking-[0.15em] text-[10px] font-semibold">Aucun article dans ce statut</p>
          <div class="mt-12">
            <button onclick="location.reload()" class="flex items-center gap-3 px-8 py-4 bg-surface-container-highest/50 hover:bg-surface-container-highest transition-all rounded-lg group">
              <span class="material-symbols-outlined text-primary group-hover:rotate-12 transition-transform">refresh</span>
              <span class="font-label text-[10px] uppercase tracking-[0.2em] font-bold text-on-surface">Actualiser le flux</span>
            </button>
          </div>
        </div>
      </div>

    <?php else: ?>

      <div class="space-y-6">
        <?php
        $psCfg = [
          'pending'   => ['bg-yellow-100 text-yellow-700', '⏳ En attente'],
          'published' => ['bg-green-100 text-green-700',   '✅ Publié'],
          'rejected'  => ['bg-red-100 text-red-700',       '❌ Refusé'],
        ];
        foreach ($posts as $post):
          [$psCls, $psLabel] = $psCfg[$post['status'] ?? 'pending'] ?? ['bg-stone-100 text-stone-500', ucfirst($post['status'] ?? '')];
        ?>
          <div class="bg-surface-container-lowest rounded-xl border border-outline-variant/10 p-8 hover:shadow-sm transition-all" id="post-<?= $post['id'] ?>">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3 mb-3">
                  <?php if (!empty($post['category'])): ?>
                    <span class="text-[10px] font-bold px-2 py-1 bg-primary/10 text-primary rounded-full uppercase tracking-wider"><?= Security::e($post['category']) ?></span>
                  <?php endif; ?>
                  <span class="text-[10px] font-bold px-2 py-1 rounded-full <?= $psCls ?> uppercase tracking-wider"><?= $psLabel ?></span>
                </div>
                <h3 class="font-headline text-2xl italic font-bold text-on-surface mb-2 line-clamp-2"><?= Security::e($post['title'] ?? '') ?></h3>
                <?php if (!empty($post['excerpt'])): ?>
                  <p class="text-sm text-on-surface-variant line-clamp-2 leading-relaxed mb-3"><?= Security::e($post['excerpt']) ?></p>
                <?php endif; ?>
                <p class="text-xs text-outline-variant">
                  Par <span class="font-semibold text-on-surface"><?= Security::e(($post['first_name'] ?? '') . ' ' . ($post['last_name'] ?? '')) ?></span>
                  · <?= !empty($post['created_at']) ? date('d/m/Y à H:i', strtotime($post['created_at'])) : '' ?>
                </p>
              </div>
              <?php if (($post['status'] ?? '') === 'pending'): ?>
                <div class="flex gap-3 flex-shrink-0">
                  <button onclick="moderateBlog(<?= $post['id'] ?>, 'reject')"
                    class="border border-red-300 text-red-600 px-5 py-2 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-red-50 transition-all">
                    ❌ Refuser
                  </button>
                  <button onclick="moderateBlog(<?= $post['id'] ?>, 'publish')"
                    class="bg-primary text-on-primary px-6 py-2 rounded-xl text-xs font-bold uppercase tracking-widest hover:opacity-90 transition-all">
                    ✅ Publier
                  </button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>

    <!-- Bento stats rapides -->
    <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="bg-surface-container-lowest p-8 rounded-lg shadow-sm shadow-primary/5 border border-primary/5">
        <div class="flex justify-between items-start mb-6">
          <span class="material-symbols-outlined text-primary bg-primary-container p-2 rounded-lg">auto_graph</span>
          <span class="text-[10px] font-label text-primary font-bold uppercase tracking-widest">Ce mois</span>
        </div>
        <p class="font-headline text-2xl italic text-on-surface mb-1">Articles publiés</p>
        <p class="text-3xl font-body font-light text-primary">
          <?= Database::fetch("SELECT COUNT(*) as c FROM blog_posts WHERE status='published'")['c'] ?? 0 ?>
        </p>
      </div>
      <div class="bg-primary p-8 rounded-lg shadow-sm shadow-primary/20">
        <div class="flex justify-between items-start mb-6">
          <span class="material-symbols-outlined text-on-primary bg-on-primary/10 p-2 rounded-lg">hourglass_empty</span>
        </div>
        <p class="font-headline text-2xl italic text-on-primary mb-1">En attente</p>
        <p class="text-3xl font-body font-light text-on-primary">
          <?= Database::fetch("SELECT COUNT(*) as c FROM blog_posts WHERE status='pending'")['c'] ?? 0 ?>
        </p>
      </div>
      <div class="bg-surface-container-lowest p-8 rounded-lg shadow-sm shadow-primary/5 border border-primary/5 overflow-hidden relative">
        <div class="absolute -right-4 -bottom-4 opacity-5">
          <span class="material-symbols-outlined text-[100px]">verified</span>
        </div>
        <div class="flex justify-between items-start mb-6">
          <span class="material-symbols-outlined text-primary bg-primary-container p-2 rounded-lg">group</span>
        </div>
        <p class="font-headline text-2xl italic text-on-surface mb-1">Total articles</p>
        <p class="text-3xl font-body font-light text-primary">
          <?= Database::fetch("SELECT COUNT(*) as c FROM blog_posts")['c'] ?? 0 ?>
        </p>
      </div>
    </div>

  </div>
</main>

<script>
  async function moderateBlog(postId, action) {
    const fd = new FormData();
    fd.append('post_id', postId);
    fd.append('action', action);
    fd.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>');
    const res = await fetch('<?= APP_URL ?>/admin/blog/validate', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    if (data.success) {
      const el = document.getElementById('post-' + postId);
      if (el) el.remove();
    } else {
      alert(data.error || 'Erreur lors de la modération');
    }
  }
</script>