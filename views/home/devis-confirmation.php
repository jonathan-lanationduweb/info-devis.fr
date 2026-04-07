<div style="min-height:100vh;display:flex;align-items:center;background:var(--c-gray-50);padding:var(--header-h) 16px 60px;">
  <div class="container container--narrow">
    <div class="confirmation-box">
      <div class="confirmation-icon">🎉</div>
      <h2>Demande envoyée !</h2>
      <p style="margin-bottom:24px">
        Vous recevrez vos premiers devis d'artisans qualifiés sous <strong>24 heures</strong>.<br>
        Vérifiez votre boîte email.
      </p>
      <?php if ($reference): ?>
      <div style="background:white;border-radius:var(--radius-md);padding:16px 24px;margin-bottom:28px;display:inline-block;">
        <p style="margin:0;font-size:.85rem;color:var(--c-gray-400)">Référence de votre demande</p>
        <p style="margin:0;font-size:1.3rem;font-weight:800;color:var(--c-navy);font-family:var(--f-display)"><?= Security::e($reference) ?></p>
      </div>
      <?php endif; ?>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
        <a href="<?= APP_URL ?>/dashboard/client" class="btn btn-primary">Suivre ma demande</a>
        <a href="<?= APP_URL ?>/" class="btn btn-outline">Retour à l'accueil</a>
      </div>
    </div>
  </div>
</div>
