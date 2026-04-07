<?php
$catIcons = [
    'plomberie' => '🔧', 'electricite' => '⚡', 'peinture' => '🖌️',
    'toiture' => '🏠', 'chauffage' => '🔥', 'menuiserie' => '🪚',
    'climatisation' => '❄️', 'isolation' => '🧱', 'maconnerie' => '⬛',
    'carrelage' => '◻️', 'jardinage' => '🌿', 'renovation' => '🏗️',
    'securite-domotique' => '🛡️', 'energies-renouvelables' => '☀️',
    'amenagements-exterieurs' => '🏊', 'services-b2b' => '💼',
    'demenagement-services' => '🚚', 'traitement-protection' => '🦺',
];
?>

<!-- ── Hero ──────────────────────────────────────────────────── -->
<section class="hero">
  <div class="hero-grain"></div>
  <div class="container">
    <div class="hero-inner">
      <div class="hero-content">
        <div class="hero-eyebrow">
          <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          12 000+ artisans vérifiés
        </div>
        <h1 class="hero-title">
          Trouvez l'artisan<br>
          <em>parfait</em> pour<br>vos travaux
        </h1>
        <p class="hero-subtitle">
          Obtenez jusqu'à 5 devis en moins de 24h de professionnels qualifiés et certifiés. Gratuit, rapide, sans engagement.
        </p>
        <div class="hero-actions">
          <a href="<?= APP_URL ?>/devis" class="btn btn-primary btn-xl">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Demander un devis gratuit
          </a>
          <a href="<?= APP_URL ?>/categories" class="btn btn-outline-white btn-xl">Voir les métiers</a>
        </div>
        <div class="hero-trust">
          <div class="hero-trust-item">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            100% gratuit
          </div>
          <div class="hero-trust-item">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Réponse sous 24h
          </div>
          <div class="hero-trust-item">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Artisans vérifiés
          </div>
          <div class="hero-trust-item">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            Sans engagement
          </div>
        </div>
      </div>

      <!-- Card flottante -->
      <div class="hero-visual">
        <div class="hero-card">
          <p class="hero-card-title">Quelle prestation cherchez-vous ?</p>
          <form class="hero-form" id="hero-quick-form" onsubmit="heroQuickSubmit(event)">
            <select name="category" class="form-control" required>
              <option value="">-- Choisir une catégorie --</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= Security::e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="text" name="ville" placeholder="Votre ville (ex: Paris)" required>
            <button type="submit" class="btn btn-primary w-full">
              Trouver un artisan →
            </button>
          </form>
          <p style="text-align:center;color:rgba(255,255,255,.55);font-size:.78rem;margin-top:12px;">
            🔒 Vos données sont protégées
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Stats Bar ─────────────────────────────────────────────── -->
<div class="stats-bar">
  <div class="container">
    <div class="stats-bar-inner">
      <div class="stat-item">
        <div class="stat-number"><?= $stats['artisans'] ?></div>
        <div class="stat-label">Artisans vérifiés</div>
      </div>
      <div class="stat-item">
        <div class="stat-number"><?= $stats['devis'] ?></div>
        <div class="stat-label">Devis envoyés</div>
      </div>
      <div class="stat-item">
        <div class="stat-number"><?= $stats['avis'] ?></div>
        <div class="stat-label">Avis vérifiés</div>
      </div>
      <div class="stat-item">
        <div class="stat-number"><?= $stats['cities'] ?></div>
        <div class="stat-label">Villes couvertes</div>
      </div>
    </div>
  </div>
</div>

<!-- ── Categories ────────────────────────────────────────────── -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="eyebrow">Tous les métiers</span>
      <h2>Trouvez le bon professionnel</h2>
      <p>Tous nos artisans sont vérifiés, assurés et certifiés. Comparez les devis et choisissez en toute confiance.</p>
    </div>
    <div class="categories-grid">
      <?php foreach ($categories as $cat): ?>
      <a href="<?= APP_URL ?>/categorie/<?= Security::e($cat['slug']) ?>" class="category-card reveal">
        <div class="category-icon"><?= $catIcons[$cat['slug']] ?? '🛠️' ?></div>
        <h3><?= Security::e($cat['name']) ?></h3>
        <p><?= Security::e(substr($cat['description'] ?? '', 0, 65)) ?>...</p>
        <?php if ($cat['prix_min'] && $cat['prix_max']): ?>
        <div class="category-price">
          À partir de <?= number_format($cat['prix_min'], 0, ',', ' ') ?> €
        </div>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:40px">
      <a href="<?= APP_URL ?>/categories" class="btn btn-navy">Voir toutes les catégories</a>
    </div>
  </div>
</section>

<!-- ── Comment ça marche ─────────────────────────────────────── -->
<section class="section section--gray">
  <div class="container">
    <div class="section-header">
      <span class="eyebrow">Simple & rapide</span>
      <h2>Comment ça marche ?</h2>
      <p>Obtenez vos devis en 3 étapes simples, 100% gratuit et sans engagement.</p>
    </div>
    <div class="steps-grid">
      <div class="step-card reveal">
        <div class="step-number">1</div>
        <div class="step-title">Décrivez votre projet</div>
        <p class="step-desc">Remplissez le formulaire en moins de 2 minutes. Décrivez vos travaux, votre ville et votre urgence.</p>
      </div>
      <div class="step-card reveal reveal-delay-1">
        <div class="step-number">2</div>
        <div class="step-title">Recevez des devis</div>
        <p class="step-desc">Jusqu'à 5 artisans qualifiés de votre région vous contactent sous 24h avec leurs devis.</p>
      </div>
      <div class="step-card reveal reveal-delay-2">
        <div class="step-number">3</div>
        <div class="step-title">Choisissez & validez</div>
        <p class="step-desc">Comparez les devis, vérifiez les avis clients et choisissez le professionnel qui vous convient.</p>
      </div>
      <div class="step-card reveal reveal-delay-3">
        <div class="step-number">4</div>
        <div class="step-title">Suivez votre chantier</div>
        <p class="step-desc">Utilisez votre espace client pour suivre l'avancement, échanger et payer en sécurité.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── Top Artisans ──────────────────────────────────────────── -->
<?php if (!empty($topArtisans)): ?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="eyebrow">Notre sélection</span>
      <h2>Artisans les mieux notés</h2>
      <p>Des professionnels vérifiés, certifiés et recommandés par nos clients.</p>
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
        <div class="d-flex gap-8" style="margin-bottom:12px;flex-wrap:wrap">
          <?php if ($a['badge_verified']): ?>
          <span class="badge-verified">✓ Certifié</span>
          <?php endif; ?>
          <?php if ($a['plan'] !== 'gratuit'): ?>
          <span class="badge-pro">⭐ <?= ucfirst($a['plan']) ?></span>
          <?php endif; ?>
        </div>
        <div class="artisan-rating">
          <span class="stars"><?= str_repeat('★', (int)round($a['rating_avg'] ?? 0)) . str_repeat('☆', 5 - (int)round($a['rating_avg'] ?? 0)) ?></span>
          <span class="text-sm fw-600"><?= number_format((float)($a['rating_avg'] ?? 0), 1) ?></span>
          <span class="text-sm text-muted">(<?= $a['rating_count'] ?? 0 ?> avis)</span>
        </div>
        <?php if (!empty($a['categories'])): ?>
        <p class="text-xs text-muted" style="margin-top:8px"><?= Security::e($a['categories']) ?></p>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/devis" class="btn btn-primary w-full" style="margin-top:16px">
          Demander un devis
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── CTA Banner ────────────────────────────────────────────── -->
<section class="section section--navy">
  <div class="container" style="text-align:center">
    <h2 style="color:#fff;margin-bottom:16px">Prêt à démarrer votre projet ?</h2>
    <p style="color:rgba(255,255,255,.75);max-width:520px;margin:0 auto 40px;font-size:1.1rem">
      Rejoignez les milliers de particuliers qui trouvent leur artisan idéal sur InfoDevis chaque jour.
    </p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
      <a href="<?= APP_URL ?>/devis" class="btn btn-primary btn-xl">Demander un devis gratuit</a>
      <a href="<?= APP_URL ?>/categories" class="btn btn-outline-white btn-xl">Voir les catégories</a>
    </div>
  </div>
</section>

<script>
function heroQuickSubmit(e) {
  e.preventDefault();
  const cat  = e.target.category.value;
  const ville = encodeURIComponent(e.target.ville.value);
  window.location.href = '<?= APP_URL ?>/devis?categorie=' + cat + '&ville=' + ville;
}
</script>
