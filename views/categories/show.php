<div style="padding-top:var(--header-h)">

  <!-- ── Bannière catégorie ──────────────────────────────────── -->
  <div class="page-banner page-banner--metier">
    <div class="container">

      <!-- Fil d'Ariane -->
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px">
        <a href="<?= APP_URL ?>/" style="color:rgba(255,255,255,.55);font-size:.82rem">Accueil</a>
        <span style="color:rgba(255,255,255,.25)">›</span>
        <a href="<?= APP_URL ?>/categories" style="color:rgba(255,255,255,.55);font-size:.82rem">Catégories</a>
        <span style="color:rgba(255,255,255,.25)">›</span>
        <span style="color:#fff;font-size:.82rem"><?= Security::e($category['name']) ?></span>
      </div>

      <span class="page-banner__eyebrow">Nos artisans</span>
      <h1 class="page-banner__title">
        <?= Security::e($seoPage['h1'] ?? 'Devis ' . $category['name'] . ' gratuit') ?>
      </h1>
      <p class="page-banner__subtitle">
        <?= Security::e(substr($category['description'] ?? '', 0, 160)) ?>
      </p>

      <div style="display:flex;align-items:center;gap:14px;justify-content:center;margin-top:28px;flex-wrap:wrap">
        <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="btn btn-primary btn-lg">
          Demander un devis gratuit
        </a>
        <?php if (!empty($artisanCount)): ?>
          <span style="color:rgba(255,255,255,.72);font-size:.88rem">
            🛡️ <?= $artisanCount ?>+ artisans vérifiés
          </span>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <!-- ── Fourchette de prix ───────────────────────────────────── -->
  <?php if (!empty($category['prix_min']) && !empty($category['prix_max'])): ?>
    <div style="background:var(--c-gray-50);border-bottom:1px solid var(--c-gray-200);padding:20px 0">
      <div class="container" style="display:flex;align-items:center;justify-content:center;gap:32px;flex-wrap:wrap">
        <div style="text-align:center">
          <div style="font-size:.72rem;color:var(--c-gray-400);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Prix minimum</div>
          <div style="font-size:1.5rem;font-weight:800;color:var(--c-navy)"><?= number_format($category['prix_min'], 0, ',', ' ') ?> €</div>
        </div>
        <div style="width:1px;height:40px;background:var(--c-gray-200)"></div>
        <div style="text-align:center">
          <div style="font-size:.72rem;color:var(--c-gray-400);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Prix maximum</div>
          <div style="font-size:1.5rem;font-weight:800;color:var(--c-green)"><?= number_format($category['prix_max'], 0, ',', ' ') ?> €</div>
        </div>
        <div style="width:1px;height:40px;background:var(--c-gray-200)"></div>
        <div style="text-align:center">
          <div style="font-size:.72rem;color:var(--c-gray-400);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">Délai moyen</div>
          <div style="font-size:1.5rem;font-weight:800;color:var(--c-teal)">24h</div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- ── Sous-catégories ─────────────────────────────────────── -->
  <?php if (!empty($subCategories)): ?>
    <section class="section-sm">
      <div class="container">
        <h2 style="margin-bottom:20px;font-size:1.2rem">Sous-catégories</h2>
        <div style="display:flex;flex-wrap:wrap;gap:10px">
          <?php foreach ($subCategories as $sub): ?>
            <a href="<?= APP_URL ?>/categorie/<?= Security::e($sub['slug']) ?>"
              class="btn btn-outline btn-sm">
              <?= Security::e($sub['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- ── Services / Prestations ─────────────────────────────── -->
  <?php if (!empty($services)): ?>
    <section class="section">
      <div class="container">
        <div class="section-header" style="text-align:left;margin-bottom:32px">
          <span class="eyebrow">Nos prestations</span>
          <h2>Services <?= Security::e($category['name']) ?></h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:22px">
          <?php foreach ($services as $svc): ?>
            <div class="card reveal">
              <?php if (!empty($svc['image'])): ?>
                <div style="height:180px;overflow:hidden;border-radius:var(--radius-lg) var(--radius-lg) 0 0;background:var(--c-gray-100)">
                  <img src="<?= APP_URL ?>/assets/img/categories/<?= Security::e($category['slug']) ?>/<?= Security::e($svc['image']) ?>"
                    alt="<?= Security::e($svc['nom']) ?>"
                    style="width:100%;height:100%;object-fit:cover"
                    onerror="this.parentElement.style.display='none'">
                </div>
              <?php endif; ?>
              <div class="card-body">
                <h3 style="font-size:1rem;margin-bottom:8px;font-weight:700"><?= Security::e($svc['nom']) ?></h3>
                <p style="color:var(--c-gray-600);font-size:.875rem;line-height:1.6;margin-bottom:16px">
                  <?= Security::e($svc['description']) ?>
                </p>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                  <?php if (!empty($svc['prix_min']) && !empty($svc['prix_max'])): ?>
                    <div>
                      <div style="font-size:.7rem;color:var(--c-gray-400);text-transform:uppercase;letter-spacing:.05em">Prix estimé</div>
                      <div style="font-weight:700;color:var(--c-navy);font-size:.9rem">
                        <?= number_format($svc['prix_min'], 0, ',', ' ') ?> — <?= number_format($svc['prix_max'], 0, ',', ' ') ?> €
                      </div>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($svc['duree'])): ?>
                    <div style="text-align:right">
                      <div style="font-size:.7rem;color:var(--c-gray-400);text-transform:uppercase;letter-spacing:.05em">Durée</div>
                      <div style="font-weight:600;font-size:.875rem"><?= Security::e($svc['duree']) ?></div>
                    </div>
                  <?php endif; ?>
                </div>
                <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="btn btn-primary w-full btn-sm">
                  Demander un devis
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- ── Top artisans ────────────────────────────────────────── -->
  <?php if (!empty($topArtisans)): ?>
    <section class="section section--gray">
      <div class="container">
        <div class="section-header" style="text-align:left;margin-bottom:32px">
          <span class="eyebrow">Notre sélection</span>
          <h2>Artisans <?= Security::e($category['name']) ?> recommandés</h2>
        </div>
        <div class="artisans-grid">
          <?php foreach ($topArtisans as $a): ?>
            <div class="artisan-card reveal">
              <div class="artisan-header">
                <div class="artisan-avatar">
                  <?= strtoupper(substr($a['first_name'] ?? 'A', 0, 1)) ?>
                </div>
                <div>
                  <div class="artisan-name">
                    <?= Security::e($a['company_name'] ?: ($a['first_name'] . ' ' . $a['last_name'])) ?>
                  </div>
                  <div class="artisan-meta"><?= Security::e($a['ville'] ?? '') ?></div>
                </div>
              </div>
              <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px">
                <?php if (!empty($a['badge_verified'])): ?>
                  <span class="badge-verified">✓ Certifié</span>
                <?php endif; ?>
                <?php if (!empty($a['plan']) && $a['plan'] !== 'gratuit'): ?>
                  <span class="badge-pro">⭐ <?= ucfirst($a['plan']) ?></span>
                <?php endif; ?>
              </div>
              <div class="artisan-rating">
                <span class="stars"><?= str_repeat('★', (int)round($a['rating_avg'] ?? 0)) . str_repeat('☆', 5 - (int)round($a['rating_avg'] ?? 0)) ?></span>
                <span class="text-sm fw-600"><?= number_format((float)($a['rating_avg'] ?? 0), 1) ?></span>
                <span class="text-sm text-muted">(<?= $a['rating_count'] ?? 0 ?> avis)</span>
              </div>
              <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="btn btn-primary w-full btn-sm" style="margin-top:16px">
                Demander un devis
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- ── Contenu SEO ─────────────────────────────────────────── -->
  <?php if (!empty($seoPage['content'])): ?>
    <section class="section">
      <div class="container container--narrow">
        <div style="line-height:1.85;color:var(--c-gray-600);font-size:.95rem">
          <?= $seoPage['content'] ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- ── FAQ ────────────────────────────────────────────────── -->
  <?php if (!empty($faq)): ?>
    <section class="section section--gray">
      <div class="container container--narrow">
        <h2 style="margin-bottom:28px">Questions fréquentes — <?= Security::e($category['name']) ?></h2>
        <div style="display:flex;flex-direction:column;gap:10px">
          <?php foreach ($faq as $i => $item): ?>
            <div style="background:var(--c-white);border-radius:var(--radius-md);border:1.5px solid var(--c-gray-200);overflow:hidden">
              <button onclick="toggleFaq(<?= $i ?>)"
                style="width:100%;text-align:left;padding:17px 20px;background:none;border:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;font-weight:600;font-family:var(--f-body);font-size:.92rem;color:var(--c-text)">
                <?= Security::e($item['q']) ?>
                <span id="faq-icon-<?= $i ?>" style="font-size:1.2rem;transition:.3s;flex-shrink:0;color:var(--c-green)">+</span>
              </button>
              <div id="faq-<?= $i ?>" style="display:none;padding:0 20px 16px;color:var(--c-gray-600);font-size:.88rem;line-height:1.75">
                <?= Security::e($item['a']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- ── CTA final ───────────────────────────────────────────── -->
  <section class="section section--navy" style="text-align:center">
    <div class="container">
      <h2 style="color:#fff;margin-bottom:12px">
        Prêt pour vos travaux de <?= Security::e($category['name']) ?> ?
      </h2>
      <p style="color:rgba(255,255,255,.72);margin-bottom:32px;font-size:1rem;max-width:480px;margin-left:auto;margin-right:auto">
        Obtenez jusqu'à 5 devis gratuits d'artisans qualifiés sous 24h.
      </p>
      <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="btn btn-primary btn-xl">
        Demander mon devis gratuit
      </a>
    </div>
  </section>

</div>

<script>
  function toggleFaq(i) {
    var el = document.getElementById('faq-' + i);
    var icon = document.getElementById('faq-icon-' + i);
    var open = el.style.display !== 'none';
    el.style.display = open ? 'none' : 'block';
    icon.textContent = open ? '+' : '−';
    icon.style.transform = open ? '' : 'rotate(180deg)';
  }
</script>