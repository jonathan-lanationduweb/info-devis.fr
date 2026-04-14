<?php /* views/admin/categories.php */ ?>
<?php include BASE_PATH . '/views/admin/_sidebar.php'; ?>

<!-- TopAppBar -->
<header class="fixed top-0 w-full z-40 bg-[#faf9f8]/80 backdrop-blur-xl flex justify-between items-center px-8 h-20 shadow-sm shadow-primary/5">
  <div class="flex items-center gap-4">
    <span class="text-2xl font-headline italic text-primary lowercase tracking-tighter">info-devis</span>
    <div class="hidden md:flex ml-12 gap-8">
      <a href="<?= APP_URL ?>/admin" class="font-headline italic tracking-tight text-on-surface/60 hover:text-primary transition-colors cursor-pointer">Admin</a>
      <a href="<?= APP_URL ?>/admin/categories" class="font-headline italic tracking-tight text-primary font-semibold cursor-pointer">Gestion des Catégories</a>
    </div>
  </div>
  <div class="flex items-center gap-6">
    <div class="relative">
      <span class="material-symbols-outlined text-on-surface-variant cursor-pointer">notifications</span>
      <span class="absolute top-0 right-0 w-1.5 h-1.5 bg-primary rounded-full"></span>
    </div>
    <span class="material-symbols-outlined text-on-surface-variant cursor-pointer">settings</span>
  </div>
</header>

<main class="md:ml-72 pt-28 px-8 pb-12 min-h-screen">
  <div class="max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
      <div>
        <h1 class="text-4xl font-headline italic text-on-background tracking-tight mb-2">Gestion des Catégories</h1>
        <p class="text-on-surface-variant font-light max-w-md">Organisez et hiérarchisez les métiers de l'artisanat pour affiner l'expérience de vos utilisateurs.</p>
      </div>
      <button onclick="openAddCat()"
        class="flex items-center gap-3 bg-surface-container-highest px-6 py-3 rounded-xl text-primary font-bold transition-all duration-300 hover:bg-primary hover:text-on-primary group shadow-sm">
        <span class="material-symbols-outlined group-hover:rotate-90 transition-transform duration-300">add</span>
        <span class="font-label text-xs uppercase tracking-widest">Nouvelle catégorie</span>
      </button>
    </div>

    <!-- Stats éditoriales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
      <div class="bg-surface-container-low p-8 rounded-xl flex flex-col gap-2 relative overflow-hidden group">
        <span class="material-symbols-outlined text-primary/10 text-8xl absolute -right-4 -bottom-4 transition-transform group-hover:scale-110 duration-500">category</span>
        <span class="text-xs font-label uppercase tracking-widest text-on-surface-variant">Total Catégories</span>
        <span class="text-5xl font-headline italic text-primary"><?= count($categories) ?></span>
      </div>
      <div class="bg-surface-container-low p-8 rounded-xl flex flex-col gap-2 relative overflow-hidden group">
        <span class="material-symbols-outlined text-primary/10 text-8xl absolute -right-4 -bottom-4 transition-transform group-hover:scale-110 duration-500">handyman</span>
        <span class="text-xs font-label uppercase tracking-widest text-on-surface-variant">Services Actifs</span>
        <span class="text-5xl font-headline italic text-primary"><?= array_sum(array_column($categories, 'services_count')) ?></span>
      </div>
      <div class="bg-surface-container-low p-8 rounded-xl flex flex-col gap-2 relative overflow-hidden group">
        <span class="material-symbols-outlined text-primary/10 text-8xl absolute -right-4 -bottom-4 transition-transform group-hover:scale-110 duration-500">trending_up</span>
        <span class="text-xs font-label uppercase tracking-widest text-on-surface-variant">Dernière mise à jour</span>
        <span class="text-xl font-headline italic text-primary"><?= date('d/m/Y') ?></span>
      </div>
    </div>

    <!-- Tableau catégories -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm shadow-primary/5">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-surface-container-low">
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant">Icône</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant">Nom de la catégorie</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant">Slug</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant">Services</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant text-center">Ordre</th>
            <th class="px-8 py-5 text-[10px] font-label uppercase tracking-widest text-on-surface-variant text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant/10">
          <?php if (empty($categories)): ?>
            <tr>
              <td colspan="6" class="text-center py-16 text-on-surface-variant italic font-headline">Aucune catégorie</td>
            </tr>
          <?php else: ?>
            <?php foreach ($categories as $cat): ?>
              <tr class="hover:bg-surface-container/50 transition-colors duration-200" id="cat-<?= $cat['id'] ?>">
                <td class="px-8 py-6">
                  <div class="w-10 h-10 bg-primary-container/30 flex items-center justify-center rounded-lg">
                    <span class="material-symbols-outlined text-primary text-xl"><?= htmlspecialchars($cat['icon'] ?? 'home_repair_service') ?></span>
                  </div>
                </td>
                <td class="px-8 py-6">
                  <span class="font-headline text-lg text-on-background italic"><?= Security::e($cat['name']) ?></span>
                </td>
                <td class="px-8 py-6">
                  <code class="text-xs text-on-surface-variant bg-surface-container px-2 py-1 rounded"><?= Security::e($cat['slug'] ?? '') ?></code>
                </td>
                <td class="px-8 py-6">
                  <span class="text-on-surface-variant font-medium"><?= (int)($cat['services_count'] ?? 0) ?> services</span>
                </td>
                <td class="px-8 py-6 text-center">
                  <span class="px-3 py-1 bg-surface-container rounded-full text-xs font-mono">
                    <?= str_pad((int)($cat['sort_order'] ?? 0), 2, '0', STR_PAD_LEFT) ?>
                  </span>
                </td>
                <td class="px-8 py-6 text-right">
                  <button onclick="editCat(<?= $cat['id'] ?>, '<?= addslashes(htmlspecialchars($cat['name'])) ?>', '<?= addslashes($cat['slug'] ?? '') ?>', '<?= addslashes($cat['icon'] ?? 'home_repair_service') ?>', <?= (int)($cat['sort_order'] ?? 0) ?>)"
                    class="p-2 hover:bg-primary/5 rounded-full text-primary transition-colors">
                    <span class="material-symbols-outlined">edit_note</span>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Citation -->
    <div class="mt-20 relative bg-surface-container-highest p-12 rounded-xl overflow-hidden">
      <span class="font-headline text-9xl absolute -top-10 -left-4 opacity-10 text-primary">"</span>
      <div class="relative z-10 max-w-2xl">
        <p class="font-headline text-2xl text-on-surface italic mb-6 leading-relaxed">
          L'organisation rigoureuse des catégories n'est pas qu'une tâche administrative ; c'est le fondement de la confiance entre un client et son futur artisan.
        </p>
        <div class="flex items-center gap-4">
          <div class="w-10 h-[1px] bg-primary"></div>
          <span class="font-label text-[10px] uppercase tracking-widest font-bold">Note de la Rédaction</span>
        </div>
      </div>
    </div>

  </div>
</main>

<!-- Nav mobile -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-surface-container-lowest border-t border-outline-variant/10 flex items-center justify-around z-50">
  <a href="<?= APP_URL ?>/admin"><span class="material-symbols-outlined text-on-surface/40">dashboard</span></a>
  <a href="<?= APP_URL ?>/admin/artisans"><span class="material-symbols-outlined text-on-surface/40">architecture</span></a>
  <a href="<?= APP_URL ?>/admin/categories"><span class="material-symbols-outlined text-primary">category</span></a>
  <a href="<?= APP_URL ?>/admin/devis"><span class="material-symbols-outlined text-on-surface/40">description</span></a>
  <a href="<?= APP_URL ?>/admin/metrics"><span class="material-symbols-outlined text-on-surface/40">settings</span></a>
</nav>

<!-- Modal catégorie -->
<div id="cat-modal" class="fixed inset-0 z-50 bg-on-surface/50 backdrop-blur-sm flex items-center justify-center p-6" style="display:none!important">
  <div class="bg-surface w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">
    <div class="p-8">
      <h3 class="font-headline text-2xl italic mb-6" id="modal-title">Nouvelle catégorie</h3>
      <form id="cat-form" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="id" id="cat-id" value="">
        <div>
          <label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-bold block mb-2">Nom *</label>
          <input type="text" name="name" id="cat-name" required
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3 rounded-xl">
        </div>
        <div>
          <label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-bold block mb-2">Slug (auto)</label>
          <input type="text" name="slug" id="cat-slug"
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3 rounded-xl font-mono text-sm">
        </div>
        <div>
          <label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-bold block mb-2">Icône Material</label>
          <input type="text" name="icon" id="cat-icon" value="home_repair_service" placeholder="ex: format_paint"
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3 rounded-xl text-sm">
          <p class="text-xs text-on-surface-variant mt-1">Nom d'icône Material Symbols (ex: plumbing, electric_bolt, carpenter)</p>
        </div>
        <div>
          <label class="font-label text-xs uppercase tracking-widest text-on-surface-variant font-bold block mb-2">Ordre d'affichage</label>
          <input type="number" name="sort_order" id="cat-order" value="0" min="0"
            class="w-full bg-surface-container border-none focus:ring-1 focus:ring-primary p-3 rounded-xl">
        </div>
        <div id="cat-error" class="hidden text-sm text-red-600 bg-red-50 p-3 rounded-xl"></div>
        <div class="flex gap-3 pt-4">
          <button type="button" onclick="closeCat()"
            class="flex-1 py-3 border border-outline-variant/30 rounded-xl text-on-surface-variant font-label text-xs uppercase tracking-widest hover:bg-surface-container transition-all">
            Annuler
          </button>
          <button type="submit"
            class="flex-[2] py-3 bg-primary text-on-primary rounded-xl font-label text-xs uppercase tracking-widest font-bold hover:opacity-90 transition-all">
            Sauvegarder
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const catModal = document.getElementById('cat-modal');

  function openAddCat() {
    document.getElementById('modal-title').textContent = 'Nouvelle catégorie';
    document.getElementById('cat-id').value = '';
    document.getElementById('cat-name').value = '';
    document.getElementById('cat-slug').value = '';
    document.getElementById('cat-icon').value = 'home_repair_service';
    document.getElementById('cat-order').value = 0;
    catModal.style.cssText = 'display:flex!important';
  }

  function editCat(id, name, slug, icon, order) {
    document.getElementById('modal-title').textContent = 'Modifier la catégorie';
    document.getElementById('cat-id').value = id;
    document.getElementById('cat-name').value = name;
    document.getElementById('cat-slug').value = slug;
    document.getElementById('cat-icon').value = icon;
    document.getElementById('cat-order').value = order;
    catModal.style.cssText = 'display:flex!important';
  }

  function closeCat() {
    catModal.style.cssText = 'display:none!important';
  }

  // Auto-slug
  document.getElementById('cat-name').addEventListener('input', function() {
    const s = document.getElementById('cat-slug');
    if (!s.dataset.manual) {
      s.value = this.value.toLowerCase()
        .replace(/[éèêë]/g, 'e').replace(/[àâä]/g, 'a').replace(/[ùûü]/g, 'u')
        .replace(/[ôö]/g, 'o').replace(/[îï]/g, 'i').replace(/ç/g, 'c')
        .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
  });
  document.getElementById('cat-slug').addEventListener('input', function() {
    this.dataset.manual = '1';
  });

  // Submit
  document.getElementById('cat-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const errDiv = document.getElementById('cat-error');
    errDiv.classList.add('hidden');
    const fd = new FormData(this);
    const res = await fetch('<?= APP_URL ?>/admin/categories/save', {
      method: 'POST',
      body: fd
    });
    const d = await res.json();
    if (d.success) {
      closeCat();
      location.reload();
    } else {
      errDiv.textContent = d.error || 'Erreur';
      errDiv.classList.remove('hidden');
    }
  });
</script>