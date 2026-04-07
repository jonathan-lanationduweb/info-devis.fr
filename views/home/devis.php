<div style="background:var(--c-gray-50);min-height:100vh;padding:calc(var(--header-h) + 48px) 0 80px;">
  <div class="container container--narrow">
    <!-- Header -->
    <div style="text-align:center;margin-bottom:48px;">
      <span class="eyebrow" style="display:inline-block;background:rgba(249,115,22,.1);color:var(--c-orange);border-radius:var(--radius-full);font-size:.78rem;font-weight:700;padding:5px 16px;letter-spacing:.08em;text-transform:uppercase;margin-bottom:16px">
        100% gratuit — Sans engagement
      </span>
      <h1 style="margin-bottom:16px">Demandez votre devis gratuit</h1>
      <p class="lead">Décrivez votre projet en moins de 2 minutes et recevez jusqu'à 5 devis d'artisans qualifiés sous 24h.</p>
    </div>

    <!-- Steps indicator -->
    <div style="display:flex;justify-content:center;gap:8px;margin-bottom:40px;">
      <?php for ($i=1;$i<=3;$i++): ?>
      <div style="display:flex;align-items:center;gap:8px;">
        <div style="width:32px;height:32px;border-radius:50%;background:<?= $i===1 ? 'var(--c-orange)' : 'var(--c-gray-200)' ?>;display:flex;align-items:center;justify-content:center;color:<?= $i===1 ? '#fff' : 'var(--c-gray-400)' ?>;font-weight:700;font-size:.875rem;">
          <?= $i ?>
        </div>
        <span style="font-size:.85rem;color:<?= $i===1 ? 'var(--c-navy)' : 'var(--c-gray-400)' ?>;font-weight:<?= $i===1 ? '600' : '400' ?>">
          <?= ['Votre projet','Vos coordonnées','Confirmation'][$i-1] ?>
        </span>
        <?php if ($i<3): ?><span style="color:var(--c-gray-300)">›</span><?php endif; ?>
      </div>
      <?php endfor; ?>
    </div>

    <div class="card">
      <div class="card-body" style="padding:40px;">
        <div id="devis-result"></div>

        <form id="devis-form" action="<?= APP_URL ?>/devis" method="POST" enctype="multipart/form-data">
          <?= Security::csrfField() ?>

          <!-- Catégorie(s) -->
          <div class="form-group">
            <label class="form-label">Type de travaux <span class="req">*</span></label>
            <select name="category_id" id="cat-select" class="form-control" required>
              <option value="">-- Choisir une catégorie --</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($catId == $cat['id']) ? 'selected' : '' ?>>
                <?= Security::e($cat['name']) ?>
                <?php if ($cat['prix_min']): ?>
                  (à partir de <?= number_format($cat['prix_min'],0,',',' ') ?> €)
                <?php endif; ?>
              </option>
              <?php endforeach; ?>
            </select>
            <p class="form-hint">Vous pouvez sélectionner plusieurs catégories ci-dessous si besoin.</p>
          </div>

          <!-- Multi-catégories (optionnel) -->
          <div class="form-group" id="multi-cat-section" style="display:none;">
            <label class="form-label">Ajouter d'autres catégories (optionnel)</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;max-height:220px;overflow-y:auto;padding:4px;">
              <?php foreach ($categories as $cat): ?>
              <label style="display:flex;align-items:center;gap:8px;background:var(--c-gray-50);border:1px solid var(--c-gray-200);border-radius:var(--radius-md);padding:10px 12px;cursor:pointer;font-size:.88rem;">
                <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>"
                       id="cat_<?= $cat['id'] ?>" style="accent-color:var(--c-orange);">
                <?= Security::e($cat['name']) ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Description -->
          <div class="form-group">
            <label class="form-label">Décrivez votre projet <span class="req">*</span></label>
            <textarea name="description" class="form-control" rows="5" required
              placeholder="Exemple : J'ai une fuite d'eau sous mon évier, l'eau coule abondamment. Appartement Paris 75011, besoin d'intervention rapide..."><?= Security::e($_POST['description'] ?? '') ?></textarea>
            <p class="form-hint">Plus vous êtes précis, plus les devis seront adaptés à votre besoin.</p>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Urgence</label>
              <select name="urgency" class="form-control">
                <option value="normal">Normal (sous une semaine)</option>
                <option value="urgent">Urgent (sous 48h)</option>
                <option value="tres_urgent">Très urgent (aujourd'hui)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Budget approximatif</label>
              <select name="budget_min" class="form-control">
                <option value="">Pas de budget défini</option>
                <option value="0">Moins de 500 €</option>
                <option value="500">500 € - 1 000 €</option>
                <option value="1000">1 000 € - 5 000 €</option>
                <option value="5000">5 000 € - 10 000 €</option>
                <option value="10000">Plus de 10 000 €</option>
              </select>
            </div>
          </div>

          <hr style="border:none;border-top:1px solid var(--c-gray-200);margin:28px 0;">
          <h3 style="margin-bottom:24px;font-size:1.1rem;font-family:var(--f-body);font-weight:700;">Vos coordonnées</h3>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Prénom <span class="req">*</span></label>
              <input type="text" name="first_name" class="form-control" required
                     value="<?= Security::e($_POST['first_name'] ?? '') ?>" placeholder="Jean">
            </div>
            <div class="form-group">
              <label class="form-label">Nom</label>
              <input type="text" name="last_name" class="form-control"
                     value="<?= Security::e($_POST['last_name'] ?? '') ?>" placeholder="Dupont">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Email <span class="req">*</span></label>
              <input type="email" name="email" class="form-control" required
                     value="<?= Security::e($_POST['email'] ?? '') ?>" placeholder="jean@exemple.fr">
            </div>
            <div class="form-group">
              <label class="form-label">Téléphone</label>
              <input type="tel" name="phone" class="form-control"
                     value="<?= Security::e($_POST['phone'] ?? '') ?>" placeholder="06 XX XX XX XX">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Ville <span class="req">*</span></label>
              <input type="text" name="ville" class="form-control" required
                     value="<?= Security::e($_GET['ville'] ?? $_POST['ville'] ?? '') ?>" placeholder="Paris">
            </div>
            <div class="form-group">
              <label class="form-label">Code postal</label>
              <input type="text" name="code_postal" class="form-control"
                     value="<?= Security::e($_POST['code_postal'] ?? '') ?>" placeholder="75011">
            </div>
          </div>

          <!-- RGPD -->
          <div class="form-group" style="background:var(--c-gray-50);border-radius:var(--radius-md);padding:20px;margin-top:8px;">
            <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer;">
              <input type="checkbox" name="consent_privacy" required style="margin-top:3px;width:16px;height:16px;flex-shrink:0;">
              <span style="font-size:.85rem;color:var(--c-gray-600);">
                En soumettant ce formulaire, j'accepte que mes données soient transmises à des artisans qualifiés pour recevoir des devis.
                <a href="<?= APP_URL ?>/politique-confidentialite" style="color:var(--c-blue-light)">Politique de confidentialité</a>. <span class="req">*</span>
              </span>
            </label>
          </div>

          <button type="submit" class="btn btn-primary w-full btn-xl" style="margin-top:24px">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
            Envoyer ma demande gratuite
          </button>

          <p style="text-align:center;font-size:.8rem;color:var(--c-gray-400);margin-top:14px;">
            🔒 Données sécurisées · Service 100% gratuit · Sans engagement
          </p>
        </form>
      </div>
    </div>

    <!-- Trust -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:32px;text-align:center;">
      <div><div style="font-size:1.5rem;margin-bottom:4px">🛡️</div><div style="font-size:.85rem;font-weight:600">Artisans vérifiés</div></div>
      <div><div style="font-size:1.5rem;margin-bottom:4px">⏱️</div><div style="font-size:.85rem;font-weight:600">Réponse sous 24h</div></div>
      <div><div style="font-size:1.5rem;margin-bottom:4px">💚</div><div style="font-size:.85rem;font-weight:600">100% gratuit</div></div>
    </div>
  </div>
</div>

<script>
// Sync la catégorie principale dans les checkboxes
document.getElementById('cat-select')?.addEventListener('change', function() {
  const val = this.value;
  const multi = document.getElementById('multi-cat-section');
  if (val) {
    multi.style.display = 'block';
    const cb = document.getElementById('cat_' + val);
    if (cb) cb.checked = true;
  } else {
    multi.style.display = 'none';
  }
});

// Init si catégorie pré-sélectionnée
if (document.getElementById('cat-select')?.value) {
  document.getElementById('cat-select').dispatchEvent(new Event('change'));
}
</script>
