<?php /* views/client/avis.php */ ?>
<?php include BASE_PATH . '/views/client/_sidebar.php'; ?>

<div class="flex pt-20">
  <main class="flex-1 md:ml-72 min-h-screen px-8 py-12 md:px-16">

    <header class="mb-16">
      <h1 class="text-5xl font-headline font-bold text-on-surface mb-2">Mes avis</h1>
      <p class="text-stone-500 font-body text-lg italic max-w-2xl">Exprimez votre satisfaction et partagez votre expérience avec la communauté InfoDevis.</p>
    </header>

    <!-- Avis en attente -->
    <?php if (!empty($pending)): ?>
      <section class="mb-20">
        <div class="flex items-center justify-between mb-8">
          <h3 class="font-label uppercase tracking-[0.2em] text-sm font-bold text-stone-400">Avis en attente</h3>
          <div class="h-px flex-1 bg-outline-variant/20 ml-6"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <?php foreach ($pending as $p): ?>
            <div class="group relative bg-surface-container-low p-8 rounded-xl border-l-4 border-amber-400 transition-all duration-300 hover:shadow-lg">
              <div class="flex justify-between items-start mb-6">
                <div>
                  <h4 class="font-headline text-2xl font-bold mb-1"><?= Security::e($p['company_name']) ?></h4>
                  <p class="text-stone-500 font-label uppercase text-[10px] tracking-widest">Réf. <?= Security::e($p['reference']) ?></p>
                </div>
                <span class="material-symbols-outlined text-amber-500 text-3xl">pending_actions</span>
              </div>
              <button onclick="openAvis(<?= $p['id'] ?>, <?= $p['artisan_id'] ?>)"
                class="w-full py-4 bg-primary text-on-primary font-label uppercase tracking-widest text-xs font-bold rounded-lg hover:opacity-90 transition-opacity">
                Laisser un avis
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Avis publiés -->
    <?php if (!empty($myAvis)): ?>
      <section>
        <div class="flex items-center justify-between mb-12">
          <h3 class="font-label uppercase tracking-[0.2em] text-sm font-bold text-stone-400">Avis publiés</h3>
          <div class="h-px flex-1 bg-outline-variant/20 ml-6"></div>
        </div>
        <div class="space-y-12">
          <?php foreach ($myAvis as $av): ?>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
              <div class="lg:col-span-3">
                <div class="flex gap-1 mb-2">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="material-symbols-outlined text-amber-400" style="font-variation-settings:'FILL' <?= $i <= $av['rating'] ? '1' : '0' ?>">star</span>
                  <?php endfor; ?>
                </div>
                <p class="font-headline font-bold text-xl mb-1"><?= Security::e($av['company_name']) ?></p>
                <p class="text-stone-500 font-label uppercase text-[10px] tracking-widest">
                  <?= !empty($av['created_at']) ? date('F Y', strtotime($av['created_at'])) : '' ?>
                </p>
              </div>
              <div class="lg:col-span-7 relative" style="position:relative">
                <span class="absolute -top-5 -left-3 font-headline text-5xl text-primary opacity-10 select-none">"</span>
                <p class="font-headline italic text-2xl text-on-surface leading-relaxed">
                  <?= Security::e($av['comment'] ?? 'Aucun commentaire') ?>
                </p>
                <div class="mt-6 flex items-center gap-3">
                  <span class="w-8 h-px bg-primary"></span>
                  <p class="font-label text-xs uppercase tracking-widest text-stone-500">
                    Réf. <span class="font-bold text-primary"><?= Security::e($av['reference']) ?></span>
                  </p>
                </div>
              </div>
            </div>
            <div class="h-px w-full bg-surface-container"></div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php elseif (empty($pending)): ?>
      <div class="text-center py-24">
        <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">reviews</span>
        <h2 class="font-headline text-3xl mb-4">Aucun avis pour le moment</h2>
        <p class="text-on-surface-variant">Vos avis apparaîtront ici après la fin de vos travaux</p>
      </div>
    <?php endif; ?>

  </main>
</div>

<!-- Modal avis -->
<div id="avis-modal" class="fixed inset-0 z-50 bg-on-surface/60 backdrop-blur-md flex items-center justify-center p-6" style="display:none">
  <div class="bg-surface w-full max-w-2xl rounded-2xl overflow-hidden shadow-2xl relative">
    <button onclick="closeAvis()" class="absolute top-6 right-6 text-stone-400 hover:text-on-surface transition-colors">
      <span class="material-symbols-outlined text-3xl">close</span>
    </button>
    <div class="p-12">
      <header class="text-center mb-10">
        <h2 class="font-headline text-4xl font-bold mb-4">Laissez votre avis</h2>
        <p class="text-stone-500 italic font-headline text-lg" id="modal-subtitle">Pour ce projet</p>
      </header>
      <form id="avis-form" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="devis_id" id="avis-devis-id">
        <input type="hidden" name="artisan_id" id="avis-artisan-id">
        <input type="hidden" name="rating" id="rating-input" value="0">

        <!-- Étoiles -->
        <div class="flex justify-center gap-4 mb-10" id="stars-container">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <button type="button" onclick="setRating(<?= $i ?>)" data-rating="<?= $i ?>" class="star-btn group">
              <span class="material-symbols-outlined text-6xl text-stone-200 group-hover:scale-110 transition-transform star-icon">star</span>
            </button>
          <?php endfor; ?>
        </div>

        <div class="space-y-4">
          <label class="block font-label uppercase tracking-widest text-[10px] font-bold text-stone-500">Votre témoignage</label>
          <textarea name="comment" rows="5"
            class="w-full bg-surface-container border-0 border-b-2 border-transparent focus:ring-0 focus:border-primary p-6 font-headline italic text-xl placeholder:text-stone-400 rounded-lg transition-all"
            placeholder="Racontez-nous votre expérience..."></textarea>
        </div>

        <div class="flex items-center gap-6 pt-4">
          <button type="button" onclick="closeAvis()"
            class="flex-1 py-4 font-label uppercase tracking-widest text-xs font-bold text-stone-500 hover:bg-stone-100 transition-colors rounded-lg">
            Annuler
          </button>
          <button type="submit"
            class="flex-[2] py-5 bg-primary text-on-primary font-label uppercase tracking-widest text-xs font-bold rounded-lg shadow-lg shadow-primary/20 hover:opacity-95 transition-all">
            Publier mon avis
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function openAvis(devisId, artisanId) {
    document.getElementById('avis-devis-id').value = devisId;
    document.getElementById('avis-artisan-id').value = artisanId;
    document.getElementById('rating-input').value = 0;
    document.querySelectorAll('.star-icon').forEach(s => {
      s.style.color = '';
      s.setAttribute('style', 'font-variation-settings: "FILL" 0');
    });
    document.getElementById('avis-modal').style.display = 'flex';
  }

  function closeAvis() {
    document.getElementById('avis-modal').style.display = 'none';
  }

  function setRating(n) {
    document.getElementById('rating-input').value = n;
    document.querySelectorAll('.star-btn').forEach(btn => {
      const r = parseInt(btn.dataset.rating);
      const icon = btn.querySelector('.star-icon');
      if (r <= n) {
        icon.style.color = '#fbbf24';
        icon.setAttribute('style', 'color:#fbbf24;font-variation-settings:"FILL" 1');
      } else {
        icon.setAttribute('style', 'color:#d1d5db;font-variation-settings:"FILL" 0');
      }
    });
  }
  document.getElementById('avis-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!document.getElementById('rating-input').value || document.getElementById('rating-input').value == 0) {
      alert('Veuillez donner une note.');
      return;
    }
    const res = await fetch('<?= APP_URL ?>/dashboard/client/avis', {
      method: 'POST',
      body: new FormData(this)
    });
    const data = await res.json();
    if (data.success) {
      closeAvis();
      location.reload();
    } else alert(data.error || 'Erreur');
  });
</script>