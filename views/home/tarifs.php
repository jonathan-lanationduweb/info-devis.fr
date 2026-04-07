<div style="padding-top:var(--header-h)">
  <section style="background:linear-gradient(135deg,var(--c-navy),var(--c-blue));padding:80px 0 120px;text-align:center">
    <div class="container">
      <span class="eyebrow" style="display:inline-block;background:rgba(249,115,22,.15);color:#fdba74;border-radius:999px;font-size:.78rem;font-weight:700;padding:5px 16px;letter-spacing:.08em;text-transform:uppercase;margin-bottom:20px">Pour les professionnels</span>
      <h1 style="color:#fff;margin-bottom:16px">Développez votre activité</h1>
      <p style="color:rgba(255,255,255,.75);font-size:1.15rem;max-width:560px;margin:0 auto">
        Recevez des leads qualifiés, augmentez votre visibilité et gérez vos chantiers depuis un seul tableau de bord.
      </p>
    </div>
  </section>

  <section style="margin-top:-60px;padding-bottom:96px">
    <div class="container">
      <div class="plans-grid" style="max-width:1000px;margin:0 auto">

        <!-- Gratuit -->
        <div class="plan-card">
          <div class="plan-name">Gratuit</div>
          <div class="plan-price">0 <span>€/mois</span></div>
          <p style="color:var(--c-gray-400);font-size:.875rem;margin:12px 0">Pour découvrir la plateforme</p>
          <ul class="plan-features">
            <li>3 leads par mois</li>
            <li>Profil basique</li>
            <li>Chat clients</li>
            <li class="disabled">Badge vérifié</li>
            <li class="disabled">Mise en avant</li>
            <li class="disabled">Statistiques avancées</li>
          </ul>
          <a href="<?= APP_URL ?>/inscription?type=artisan" class="btn btn-outline w-full">Commencer gratuitement</a>
        </div>

        <!-- Starter -->
        <div class="plan-card">
          <div class="plan-name">Starter</div>
          <div class="plan-price">49 <span>€/mois</span></div>
          <p style="color:var(--c-gray-400);font-size:.875rem;margin:12px 0">Pour les artisans actifs</p>
          <ul class="plan-features">
            <li>20 leads par mois</li>
            <li>Profil optimisé</li>
            <li>Chat clients</li>
            <li>Badge vérifié</li>
            <li class="disabled">Mise en avant premium</li>
            <li class="disabled">Statistiques avancées</li>
          </ul>
          <a href="<?= APP_URL ?>/inscription?type=artisan&plan=starter" class="btn btn-navy w-full">Choisir Starter</a>
        </div>

        <!-- Pro (featured) -->
        <div class="plan-card featured">
          <div class="plan-badge">⭐ Populaire</div>
          <div class="plan-name">Pro</div>
          <div class="plan-price">99 <span>€/mois</span></div>
          <p style="color:var(--c-gray-400);font-size:.875rem;margin:12px 0">Pour les pros en croissance</p>
          <ul class="plan-features">
            <li>50 leads par mois</li>
            <li>Profil premium mis en avant</li>
            <li>Chat clients prioritaire</li>
            <li>Badge vérifié Pro</li>
            <li>Mise en avant premium</li>
            <li>Statistiques avancées</li>
          </ul>
          <a href="<?= APP_URL ?>/inscription?type=artisan&plan=pro" class="btn btn-primary w-full">Choisir Pro</a>
        </div>

        <!-- Illimité -->
        <div class="plan-card">
          <div class="plan-name">Illimité</div>
          <div class="plan-price">199 <span>€/mois</span></div>
          <p style="color:var(--c-gray-400);font-size:.875rem;margin:12px 0">Pour les grandes entreprises</p>
          <ul class="plan-features">
            <li>Leads illimités</li>
            <li>Top du classement garanti</li>
            <li>Gestionnaire dédié</li>
            <li>Badge Partenaire Premium</li>
            <li>Mise en avant maximale</li>
            <li>Dashboard personnalisé</li>
          </ul>
          <a href="<?= APP_URL ?>/inscription?type=artisan&plan=illimite" class="btn btn-navy w-full">Choisir Illimité</a>
        </div>
      </div>

      <!-- Lead individuel -->
      <div style="text-align:center;margin-top:48px;padding:32px;background:var(--c-gray-50);border-radius:var(--radius-xl);max-width:600px;margin-left:auto;margin-right:auto">
        <h3 style="font-family:var(--f-body);margin-bottom:8px">💡 Ou achetez des leads à l'unité</h3>
        <p style="color:var(--c-gray-600);margin-bottom:20px">Pas d'abonnement ? Achetez des leads individuellement à <strong>9,90 €</strong> par lead.</p>
        <a href="<?= APP_URL ?>/inscription?type=artisan" class="btn btn-primary">Créer un compte Pro</a>
      </div>
    </div>
  </section>

  <!-- Garanties -->
  <section class="section section--gray">
    <div class="container">
      <div class="section-header">
        <h2>Pourquoi choisir InfoDevis ?</h2>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:32px">
        <?php
        $benefits = [
          ['🎯','Leads qualifiés','Chaque lead est un particulier qui a réellement besoin de vos services dans votre zone.'],
          ['🛡️','Artisans vérifiés','Vos certifications et assurances sont vérifiées, rassurant les clients.'],
          ['📱','Dashboard mobile','Gérez vos leads, messages et chantiers depuis votre smartphone.'],
          ['💬','Chat intégré','Échangez directement avec vos clients depuis la plateforme.'],
          ['📊','Statistiques','Analysez vos performances et optimisez votre activité.'],
          ['🔒','Paiement sécurisé','Recevez des acomptes clients directement sur la plateforme.'],
        ];
        foreach($benefits as $b): ?>
        <div style="text-align:center;padding:24px">
          <div style="font-size:2rem;margin-bottom:12px"><?= $b[0] ?></div>
          <div style="font-weight:700;margin-bottom:8px"><?= $b[1] ?></div>
          <p style="font-size:.875rem;color:var(--c-gray-600);margin:0"><?= $b[2] ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</div>
