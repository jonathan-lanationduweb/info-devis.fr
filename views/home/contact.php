<div style="padding-top:var(--header-h)">
  <!-- Hero -->
  <section style="background:linear-gradient(135deg,var(--c-navy),var(--c-blue));padding:64px 0">
    <div class="container" style="text-align:center">
      <h1 style="color:#fff;margin-bottom:12px">Contactez-nous</h1>
      <p style="color:rgba(255,255,255,.8);font-size:1.1rem">Notre équipe est disponible du lundi au vendredi de 9h à 18h</p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:start">

        <!-- Infos contact -->
        <div>
          <h2 style="margin-bottom:32px">Nos coordonnées</h2>
          <div style="display:flex;flex-direction:column;gap:24px">
            <div style="display:flex;align-items:flex-start;gap:16px">
              <div style="width:48px;height:48px;background:rgba(249,115,22,.1);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">📞</div>
              <div>
                <div style="font-weight:700;margin-bottom:4px">Téléphone</div>
                <a href="tel:<?= str_replace(' ','',$_CONTACT_PHONE ?? CONTACT_PHONE) ?>" style="color:var(--c-blue-light);font-size:1.1rem;font-weight:600"><?= CONTACT_PHONE ?></a>
                <div style="font-size:.85rem;color:var(--c-gray-400);margin-top:2px"><?= CONTACT_HOURS ?></div>
              </div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:16px">
              <div style="width:48px;height:48px;background:rgba(249,115,22,.1);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">✉️</div>
              <div>
                <div style="font-weight:700;margin-bottom:4px">Email</div>
                <a href="mailto:<?= CONTACT_EMAIL ?>" style="color:var(--c-blue-light);font-size:1.05rem;font-weight:600"><?= CONTACT_EMAIL ?></a>
                <div style="font-size:.85rem;color:var(--c-gray-400);margin-top:2px">Réponse sous 24h</div>
              </div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:16px">
              <div style="width:48px;height:48px;background:rgba(249,115,22,.1);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">📍</div>
              <div>
                <div style="font-weight:700;margin-bottom:4px">Adresse</div>
                <div style="color:var(--c-gray-600)"><?= CONTACT_ADDRESS ?></div>
              </div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:16px">
              <div style="width:48px;height:48px;background:rgba(249,115,22,.1);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0">🕐</div>
              <div>
                <div style="font-weight:700;margin-bottom:4px">Horaires</div>
                <div style="color:var(--c-gray-600)"><?= CONTACT_HOURS ?></div>
              </div>
            </div>
          </div>

          <!-- Google Maps -->
          <div style="margin-top:32px;border-radius:var(--radius-lg);overflow:hidden;border:1px solid var(--c-gray-200)">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2625.5!2d2.3722!3d48.8535!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2s45+Rue+des+Boulets+75011+Paris!5e0!3m2!1sfr!2sfr!4v1"
              width="100%" height="280" style="border:0;display:block" allowfullscreen loading="lazy">
            </iframe>
          </div>
        </div>

        <!-- Formulaire -->
        <div>
          <h2 style="margin-bottom:32px">Envoyez-nous un message</h2>
          <?php if (!empty($success)): ?>
          <div class="alert alert-success">✅ Votre message a été envoyé. Nous vous répondrons sous 24h.</div>
          <?php endif; ?>
          <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= Security::e($error) ?></div>
          <?php endif; ?>
          <form action="<?= APP_URL ?>/contact" method="POST" data-validate>
            <?= Security::csrfField() ?>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Prénom <span class="req">*</span></label>
                <input type="text" name="first_name" class="form-control" required placeholder="Jean">
              </div>
              <div class="form-group">
                <label class="form-label">Nom <span class="req">*</span></label>
                <input type="text" name="last_name" class="form-control" required placeholder="Dupont">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Email <span class="req">*</span></label>
              <input type="email" name="email" class="form-control" required placeholder="jean@exemple.fr">
            </div>
            <div class="form-group">
              <label class="form-label">Sujet</label>
              <select name="subject" class="form-control">
                <option>Demande d'information</option>
                <option>Problème technique</option>
                <option>Partenariat artisan</option>
                <option>Signalement</option>
                <option>Autre</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Message <span class="req">*</span></label>
              <textarea name="message" class="form-control" rows="6" required placeholder="Votre message..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-full btn-lg">Envoyer mon message</button>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>
