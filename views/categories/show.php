<div style="padding-top:var(--header-h)">

  <!-- Hero catégorie -->
  <section style="background:linear-gradient(135deg,var(--c-navy) 0%,var(--c-blue) 100%);padding:64px 0">
    <div class="container">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
        <a href="<?= APP_URL ?>/" style="color:rgba(255,255,255,.6);font-size:.85rem">Accueil</a>
        <span style="color:rgba(255,255,255,.3)">›</span>
        <a href="<?= APP_URL ?>/categories" style="color:rgba(255,255,255,.6);font-size:.85rem">Catégories</a>
        <span style="color:rgba(255,255,255,.3)">›</span>
        <span style="color:#fff;font-size:.85rem"><?= Security::e($category['name']) ?></span>
      </div>
      <div style="display:grid;grid-template-columns:1fr auto;gap:40px;align-items:center">
        <div>
          <h1 style="color:#fff;margin-bottom:16px"><?= Security::e($seoPage['h1'] ?? 'Devis ' . $category['name'] . ' gratuit') ?></h1>
          <p style="color:rgba(255,255,255,.8);font-size:1.1rem;margin-bottom:28px;max-width:600px">
            <?= Security::e($category['description'] ?? '') ?>
          </p>
          <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
            <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="btn btn-primary btn-lg">
              Demander un devis gratuit
            </a>
            <?php if ($artisanCount): ?>
            <span style="color:rgba(255,255,255,.75);font-size:.9rem">
              🛡️ <?= $artisanCount ?>+ artisans vérifiés
            </span>
            <?php endif; ?>
          </div>
        </div>
        <?php if ($category['prix_min'] && $category['prix_max']): ?>
        <div style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);backdrop-filter:blur(12px);border-radius:var(--radius-lg);padding:28px 32px;text-align:center;min-width:200px">
          <div style="font-size:.8rem;color:rgba(255,255,255,.6);margin-bottom:8px;text-transform:uppercase;letter-spacing:.06em">Fourchette de prix</div>
          <div style="font-family:var(--f-display);font-size:1.5rem;color:#fff;font-weight:800">
            <?= number_format($category['prix_min'],0,',',' ') ?> €
          </div>
          <div style="color:rgba(255,255,255,.5);margin:4px 0">à</div>
          <div style="font-family:var(--f-display);font-size:1.5rem;color:var(--c-orange);font-weight:800">
            <?= number_format($category['prix_max'],0,',',' ') ?> €
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Sous-catégories -->
  <?php if (!empty($subCategories)): ?>
  <section class="section-sm section--gray">
    <div class="container">
      <h2 style="margin-bottom:24px;font-size:1.3rem">Sous-catégories</h2>
      <div style="display:flex;flex-wrap:wrap;gap:12px">
        <?php foreach($subCategories as $sub): ?>
        <a href="<?= APP_URL ?>/categorie/<?= Security::e($sub['slug']) ?>"
           style="background:var(--c-white);border:1.5px solid var(--c-gray-200);border-radius:var(--radius-full);padding:8px 20px;font-size:.875rem;font-weight:500;transition:var(--transition);"
           onmouseover="this.style.borderColor='var(--c-orange)';this.style.color='var(--c-orange)'"
           onmouseout="this.style.borderColor='var(--c-gray-200)';this.style.color=''">
          <?= Security::e($sub['name']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Services / Catalogue -->
  <?php if (!empty($services)): ?>
  <section class="section">
    <div class="container">
      <div class="section-header" style="text-align:left;margin-bottom:32px">
        <h2>Nos prestations <?= Security::e($category['name']) ?></h2>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px">
        <?php foreach($services as $svc): ?>
        <div class="card reveal" style="overflow:visible">
          <?php if ($svc['image']): ?>
          <div style="height:180px;background:var(--c-gray-100);overflow:hidden;border-radius:var(--radius-lg) var(--radius-lg) 0 0">
            <img src="<?= APP_URL ?>/assets/img/categories/<?= Security::e($category['slug']) ?>/<?= Security::e($svc['image']) ?>"
                 alt="<?= Security::e($svc['nom']) ?>" style="width:100%;height:100%;object-fit:cover">
          </div>
          <?php endif; ?>
          <div class="card-body">
            <h3 style="font-size:1rem;margin-bottom:8px;font-family:var(--f-body);font-weight:700"><?= Security::e($svc['nom']) ?></h3>
            <p style="color:var(--c-gray-600);font-size:.875rem;line-height:1.6;margin-bottom:16px"><?= Security::e($svc['description']) ?></p>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
              <?php if ($svc['prix_min'] && $svc['prix_max']): ?>
              <div>
                <div style="font-size:.72rem;color:var(--c-gray-400);text-transform:uppercase;letter-spacing:.05em">Prix estimé</div>
                <div style="font-weight:700;color:var(--c-navy)"><?= number_format($svc['prix_min'],0,',',' ') ?> € — <?= number_format($svc['prix_max'],0,',',' ') ?> €</div>
              </div>
              <?php endif; ?>
              <?php if ($svc['duree']): ?>
              <div style="text-align:right">
                <div style="font-size:.72rem;color:var(--c-gray-400);text-transform:uppercase;letter-spacing:.05em">Durée</div>
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

  <!-- Top artisans -->
  <?php if (!empty($topArtisans)): ?>
  <section class="section section--gray">
    <div class="container">
      <div class="section-header" style="text-align:left;margin-bottom:32px">
        <h2>Artisans <?= Security::e($category['name']) ?> recommandés</h2>
      </div>
      <div class="artisans-grid">
        <?php foreach($topArtisans as $a): ?>
        <div class="artisan-card">
          <div class="artisan-header">
            <div class="artisan-avatar"><?= strtoupper(substr($a['first_name']??'A',0,1)) ?></div>
            <div>
              <div class="artisan-name"><?= Security::e($a['company_name'] ?: $a['first_name'].' '.$a['last_name']) ?></div>
              <div class="artisan-meta"><?= Security::e($a['ville'] ?? '') ?></div>
            </div>
          </div>
          <?php if ($a['badge_verified']): ?><span class="badge-verified">✓ Certifié</span><?php endif; ?>
          <div class="artisan-rating" style="margin-top:10px">
            <span class="stars"><?= str_repeat('★',(int)round($a['rating_avg']??0)) . str_repeat('☆',5-(int)round($a['rating_avg']??0)) ?></span>
            <span class="text-sm fw-600"><?= number_format((float)($a['rating_avg']??0),1) ?></span>
          </div>
          <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="btn btn-primary w-full btn-sm" style="margin-top:16px">Demander un devis</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Contenu SEO -->
  <?php if (!empty($seoPage['content'])): ?>
  <section class="section">
    <div class="container container--narrow">
      <div class="prose" style="line-height:1.8;color:var(--c-gray-600)">
        <?= $seoPage['content'] /* already safe, generated internally */ ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- FAQ -->
  <?php if (!empty($faq)): ?>
  <section class="section section--gray">
    <div class="container container--narrow">
      <h2 style="margin-bottom:32px">Questions fréquentes — <?= Security::e($category['name']) ?></h2>
      <div style="display:flex;flex-direction:column;gap:12px">
        <?php foreach($faq as $i=>$item): ?>
        <div style="background:var(--c-white);border-radius:var(--radius-md);border:1.5px solid var(--c-gray-200);overflow:hidden">
          <button onclick="toggleFaq(<?= $i ?>)"
                  style="width:100%;text-align:left;padding:18px 20px;background:none;border:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;font-weight:600;font-family:var(--f-body);font-size:.95rem">
            <?= Security::e($item['q']) ?>
            <span id="faq-icon-<?= $i ?>" style="font-size:1.2rem;transition:.3s;flex-shrink:0">+</span>
          </button>
          <div id="faq-<?= $i ?>" style="display:none;padding:0 20px 18px;color:var(--c-gray-600);font-size:.9rem;line-height:1.7">
            <?= Security::e($item['a']) ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- CTA final -->
  <section style="background:var(--c-orange);padding:56px 0;text-align:center">
    <div class="container">
      <h2 style="color:#fff;margin-bottom:12px">Prêt pour vos travaux de <?= Security::e($category['name']) ?> ?</h2>
      <p style="color:rgba(255,255,255,.85);margin-bottom:32px;font-size:1.05rem">Obtenez jusqu'à 5 devis gratuits d'artisans qualifiés sous 24h</p>
      <a href="<?= APP_URL ?>/devis?categorie=<?= $category['id'] ?>" class="btn btn-navy btn-xl">
        Demander mon devis gratuit
      </a>
    </div>
  </section>
</div>

<script>
function toggleFaq(i) {
  const el   = document.getElementById('faq-'+i);
  const icon = document.getElementById('faq-icon-'+i);
  const open = el.style.display !== 'none';
  el.style.display   = open ? 'none'  : 'block';
  icon.textContent   = open ? '+'     : '−';
  icon.style.transform = open ? '' : 'rotate(180deg)';
}
</script>
