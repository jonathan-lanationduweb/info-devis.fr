<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--c-gray-50);padding-top:var(--header-h);">
  <div style="width:100%;max-width:460px;margin:40px auto;padding:0 16px;">
    <div class="card">
      <div style="background:linear-gradient(135deg,var(--c-navy),var(--c-blue));padding:36px 32px;text-align:center;">
        <a href="<?= APP_URL ?>" style="font-family:var(--f-display);font-size:1.8rem;color:#fff;font-weight:800;">
          Info<span style="color:var(--c-orange)">Devis</span>
        </a>
        <p style="color:rgba(255,255,255,.7);margin-top:8px;font-size:.95rem">Connectez-vous à votre espace</p>
      </div>
      <div class="card-body" style="padding:36px 32px;">
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= Security::e($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['reset'])): ?>
        <div class="alert alert-success">Mot de passe mis à jour. Connectez-vous.</div>
        <?php endif; ?>

        <form action="<?= APP_URL ?>/connexion" method="POST" data-validate>
          <?= Security::csrfField() ?>
          <div class="form-group">
            <label class="form-label" for="email">Email <span class="req">*</span></label>
            <input type="email" id="email" name="email" class="form-control" required
                   value="<?= Security::e($_POST['email'] ?? '') ?>" placeholder="votre@email.fr">
          </div>
          <div class="form-group">
            <label class="form-label" for="password">Mot de passe <span class="req">*</span></label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
          </div>
          <div style="display:flex;justify-content:flex-end;margin-bottom:24px;">
            <a href="<?= APP_URL ?>/mot-de-passe-oublie" style="font-size:.85rem;color:var(--c-blue-light);">Mot de passe oublié ?</a>
          </div>
          <button type="submit" class="btn btn-primary w-full btn-lg">Se connecter</button>
        </form>

        <hr style="border:none;border-top:1px solid var(--c-gray-200);margin:28px 0;">
        <p style="text-align:center;font-size:.9rem;color:var(--c-gray-600)">
          Pas encore de compte ?
          <a href="<?= APP_URL ?>/inscription" style="color:var(--c-blue-light);font-weight:600;">S'inscrire gratuitement</a>
        </p>
        <p style="text-align:center;font-size:.875rem;margin-top:10px;">
          <a href="<?= APP_URL ?>/inscription?type=artisan" style="color:var(--c-orange);font-weight:600;">Vous êtes artisan ? Créer un compte Pro →</a>
        </p>
      </div>
    </div>
  </div>
</div>
