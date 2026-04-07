<?php $isArtisan = ($type ?? 'client') === 'artisan'; ?>
<div style="min-height:100vh;background:var(--c-gray-50);padding:calc(var(--header-h) + 40px) 16px 60px;">
  <div style="max-width:600px;margin:0 auto;">
    <!-- Tabs -->
    <div style="display:flex;background:var(--c-white);border-radius:var(--radius-lg);padding:6px;box-shadow:var(--shadow-sm);border:1px solid var(--c-gray-200);margin-bottom:28px;">
      <a href="<?= APP_URL ?>/inscription?type=client"
         class="btn <?= !$isArtisan ? 'btn-primary' : '' ?>"
         style="flex:1;text-align:center;<?= !$isArtisan ? '' : 'background:transparent;color:var(--c-gray-600);box-shadow:none;' ?>">
        Particulier
      </a>
      <a href="<?= APP_URL ?>/inscription?type=artisan"
         class="btn <?= $isArtisan ? 'btn-primary' : '' ?>"
         style="flex:1;text-align:center;<?= $isArtisan ? '' : 'background:transparent;color:var(--c-gray-600);box-shadow:none;' ?>">
        Artisan / Pro
      </a>
    </div>

    <div class="card">
      <div style="background:linear-gradient(135deg,var(--c-navy),var(--c-blue));padding:28px 32px;text-align:center;">
        <h1 style="font-size:1.5rem;color:#fff;margin:0;">
          <?= $isArtisan ? 'Créer un compte Pro' : 'Créer un compte' ?>
        </h1>
        <p style="color:rgba(255,255,255,.7);margin-top:6px;font-size:.9rem">
          <?= $isArtisan ? 'Recevez des leads qualifiés dans votre zone' : 'Gérez vos demandes de devis gratuitement' ?>
        </p>
      </div>
      <div class="card-body" style="padding:36px 32px;">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul style="list-style:disc;padding-left:20px;margin:0">
            <?php foreach ($errors as $e): ?>
            <li><?= Security::e($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <form action="<?= APP_URL ?>/inscription" method="POST" data-validate enctype="multipart/form-data">
          <?= Security::csrfField() ?>
          <input type="hidden" name="role" value="<?= $isArtisan ? 'artisan' : 'client' ?>">

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Prénom <span class="req">*</span></label>
              <input type="text" name="first_name" class="form-control" required
                     value="<?= Security::e($_POST['first_name'] ?? '') ?>" placeholder="Jean">
            </div>
            <div class="form-group">
              <label class="form-label">Nom <span class="req">*</span></label>
              <input type="text" name="last_name" class="form-control" required
                     value="<?= Security::e($_POST['last_name'] ?? '') ?>" placeholder="Dupont">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Email <span class="req">*</span></label>
            <input type="email" name="email" class="form-control" required
                   value="<?= Security::e($_POST['email'] ?? '') ?>" placeholder="jean@exemple.fr">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Téléphone</label>
              <input type="tel" name="phone" class="form-control"
                     value="<?= Security::e($_POST['phone'] ?? '') ?>" placeholder="06 XX XX XX XX">
            </div>
            <div class="form-group">
              <label class="form-label">Mot de passe <span class="req">*</span></label>
              <input type="password" name="password" class="form-control" required
                     placeholder="8 caractères min." minlength="8">
            </div>
          </div>

          <?php if ($isArtisan): ?>
          <hr style="border:none;border-top:1px solid var(--c-gray-200);margin:24px 0 20px;">
          <h4 style="margin-bottom:20px;color:var(--c-navy)">Informations professionnelles</h4>

          <div class="form-group">
            <label class="form-label">Nom de l'entreprise <span class="req">*</span></label>
            <input type="text" name="company_name" class="form-control" required
                   value="<?= Security::e($_POST['company_name'] ?? '') ?>" placeholder="Mon Entreprise SARL">
          </div>
          <div class="form-group">
            <label class="form-label">Numéro SIRET <span class="req">*</span></label>
            <input type="text" name="siret" class="form-control" required
                   value="<?= Security::e($_POST['siret'] ?? '') ?>"
                   placeholder="14 chiffres" maxlength="14" pattern="\d{14}">
            <p class="form-hint">Votre SIRET sera vérifié auprès de l'annuaire officiel.</p>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Ville <span class="req">*</span></label>
              <input type="text" name="ville" class="form-control" required
                     value="<?= Security::e($_POST['ville'] ?? '') ?>" placeholder="Paris">
            </div>
            <div class="form-group">
              <label class="form-label">Code postal</label>
              <input type="text" name="code_postal" class="form-control"
                     value="<?= Security::e($_POST['code_postal'] ?? '') ?>" placeholder="75011">
            </div>
          </div>
          <?php endif; ?>

          <hr style="border:none;border-top:1px solid var(--c-gray-200);margin:24px 0 20px;">

          <div class="form-group">
            <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer;">
              <input type="checkbox" name="consent_privacy" required style="margin-top:3px;width:16px;height:16px;flex-shrink:0;">
              <span style="font-size:.875rem;color:var(--c-gray-600);">
                J'accepte la <a href="<?= APP_URL ?>/politique-confidentialite" style="color:var(--c-blue-light)" target="_blank">politique de confidentialité</a>
                et les <a href="<?= APP_URL ?>/cgv" style="color:var(--c-blue-light)" target="_blank">conditions générales</a>. <span class="req">*</span>
              </span>
            </label>
          </div>
          <div class="form-group">
            <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer;">
              <input type="checkbox" name="consent_marketing" style="margin-top:3px;width:16px;height:16px;flex-shrink:0;">
              <span style="font-size:.875rem;color:var(--c-gray-600);">
                J'accepte de recevoir des communications marketing (optionnel).
              </span>
            </label>
          </div>

          <button type="submit" class="btn btn-primary w-full btn-lg" style="margin-top:8px">
            <?= $isArtisan ? 'Créer mon compte Pro' : 'Créer mon compte' ?>
          </button>
        </form>

        <hr style="border:none;border-top:1px solid var(--c-gray-200);margin:24px 0;">
        <p style="text-align:center;font-size:.9rem;color:var(--c-gray-600)">
          Déjà un compte ?
          <a href="<?= APP_URL ?>/connexion" style="color:var(--c-blue-light);font-weight:600;">Se connecter</a>
        </p>
      </div>
    </div>
  </div>
</div>
