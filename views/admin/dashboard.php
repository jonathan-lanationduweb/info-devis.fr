<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= Security::e($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
  <meta name="robots" content="noindex,nofollow">
</head>
<body>
<div class="dashboard-layout">

  <!-- Admin Sidebar -->
  <aside class="dashboard-sidebar">
    <div class="sidebar-logo"><a href="<?= APP_URL ?>">Info<span>Devis</span></a></div>
    <div style="padding:0 20px 20px;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:16px">
      <span style="display:inline-block;background:rgba(249,115,22,.25);color:var(--c-orange);border-radius:999px;padding:3px 12px;font-size:.75rem;font-weight:700">ADMIN</span>
    </div>
    <ul class="sidebar-menu">
      <li class="sidebar-section-title">Vue d'ensemble</li>
      <li><a href="<?= APP_URL ?>/admin">📊 Dashboard</a></li>
      <li><a href="<?= APP_URL ?>/admin/metrics">📈 Métriques business</a></li>
      <li class="sidebar-section-title">Gestion</li>
      <li><a href="<?= APP_URL ?>/admin/artisans">
        👷 Artisans
        <?php if (($metrics['pending_artisans']??0) > 0): ?>
        <span style="margin-left:auto;background:var(--c-orange);color:#fff;border-radius:999px;padding:2px 8px;font-size:.7rem;font-weight:700"><?= $metrics['pending_artisans'] ?></span>
        <?php endif; ?>
      </a></li>
      <li><a href="<?= APP_URL ?>/admin/users">👥 Utilisateurs</a></li>
      <li><a href="<?= APP_URL ?>/admin/devis">📋 Devis</a></li>
      <li><a href="<?= APP_URL ?>/admin/paiements">💳 Paiements</a></li>
      <li><a href="<?= APP_URL ?>/admin/abonnements">📦 Abonnements</a></li>
      <li><a href="<?= APP_URL ?>/admin/categories">🏷️ Catégories</a></li>
      <li class="sidebar-section-title">Contenu</li>
      <li><a href="<?= APP_URL ?>/admin/blog">
        📝 Blog
        <?php if (($metrics['pending_blog']??0) > 0): ?>
        <span style="margin-left:auto;background:var(--c-danger);color:#fff;border-radius:999px;padding:2px 8px;font-size:.7rem;font-weight:700"><?= $metrics['pending_blog'] ?></span>
        <?php endif; ?>
      </a></li>
      <li><a href="<?= APP_URL ?>/admin/chatbot">🤖 Chatbot</a></li>
      <li class="sidebar-section-title">Autre</li>
      <li><a href="<?= APP_URL ?>">🌐 Site public</a></li>
      <li><a href="<?= APP_URL ?>/deconnexion" style="color:rgba(255,100,100,.7)">🚪 Déconnexion</a></li>
    </ul>
  </aside>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Administration</h1>
        <p style="color:var(--c-gray-400);margin:0;font-size:.9rem"><?= date('l d F Y') ?></p>
      </div>
      <div style="font-size:.85rem;color:var(--c-gray-400)">InfoDevis v1.0</div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid" style="margin-bottom:32px">
      <div class="kpi-card"><div class="kpi-icon green">👷</div><div><div class="kpi-value"><?= number_format($metrics['total_artisans'],0,',',' ') ?></div><div class="kpi-label">Artisans vérifiés</div></div></div>
      <div class="kpi-card"><div class="kpi-icon blue">👥</div><div><div class="kpi-value"><?= number_format($metrics['total_clients'],0,',',' ') ?></div><div class="kpi-label">Clients</div></div></div>
      <div class="kpi-card"><div class="kpi-icon orange">📋</div><div><div class="kpi-value"><?= number_format($metrics['total_devis'],0,',',' ') ?></div><div class="kpi-label">Devis totaux</div></div></div>
      <div class="kpi-card"><div class="kpi-icon purple">💳</div><div><div class="kpi-value"><?= number_format($metrics['revenue_month'],0,',',' ') ?> €</div><div class="kpi-label">CA ce mois</div></div></div>
    </div>

    <!-- Row 2 -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
      <!-- Pending artisans -->
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">⏳ Artisans à valider</h4>
          <a href="<?= APP_URL ?>/admin/artisans" style="font-size:.85rem;color:var(--c-blue-light)">Voir tous →</a>
        </div>
        <div class="card-body" style="padding:0">
          <?php
          $pendingArtisans = Database::fetchAll(
            'SELECT a.*,u.first_name,u.last_name,u.email FROM artisans a JOIN users u ON u.id=a.user_id
             WHERE a.verification_status="pending" ORDER BY a.created_at DESC LIMIT 5'
          );
          ?>
          <?php if (empty($pendingArtisans)): ?>
          <div style="padding:32px;text-align:center;color:var(--c-gray-400)">✅ Aucun artisan en attente</div>
          <?php else: ?>
          <?php foreach($pendingArtisans as $a): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--c-gray-100)">
            <div>
              <div style="font-weight:600;font-size:.9rem"><?= Security::e($a['company_name'] ?: $a['first_name'].' '.$a['last_name']) ?></div>
              <div style="font-size:.78rem;color:var(--c-gray-400)"><?= Security::e($a['email']) ?> · <?= Security::e($a['ville']) ?></div>
            </div>
            <div style="display:flex;gap:6px">
              <button onclick="validateArtisan(<?= $a['id'] ?>,'validate')" class="btn btn-success btn-sm">✓</button>
              <button onclick="validateArtisan(<?= $a['id'] ?>,'refuse')"   class="btn btn-danger  btn-sm">✗</button>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Recent devis -->
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">📋 Derniers devis</h4>
          <a href="<?= APP_URL ?>/admin/devis" style="font-size:.85rem;color:var(--c-blue-light)">Voir tous →</a>
        </div>
        <div class="card-body" style="padding:0">
          <?php
          $recentDevis = Database::fetchAll(
            'SELECT d.*,c.name as cat,u.first_name,u.last_name FROM devis d JOIN categories c ON c.id=d.category_id JOIN users u ON u.id=d.client_id ORDER BY d.created_at DESC LIMIT 6'
          );
          ?>
          <?php foreach($recentDevis as $d): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--c-gray-100)">
            <div>
              <div style="font-weight:600;font-size:.88rem"><?= Security::e($d['reference']) ?> — <?= Security::e($d['first_name'].' '.$d['last_name']) ?></div>
              <div style="font-size:.75rem;color:var(--c-gray-400)"><?= Security::e($d['cat']) ?> · <?= Security::e($d['ville']) ?></div>
            </div>
            <span class="badge badge-<?= $d['status'] ?>"><?= $d['status'] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Stats row -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
      <div class="card" style="text-align:center;padding:20px">
        <div style="font-size:1.8rem;font-weight:800;color:var(--c-orange)"><?= $metrics['devis_today'] ?></div>
        <div style="font-size:.82rem;color:var(--c-gray-400)">Devis aujourd'hui</div>
      </div>
      <div class="card" style="text-align:center;padding:20px">
        <div style="font-size:1.8rem;font-weight:800;color:var(--c-navy)"><?= $metrics['active_subs'] ?></div>
        <div style="font-size:.82rem;color:var(--c-gray-400)">Abonnements actifs</div>
      </div>
      <div class="card" style="text-align:center;padding:20px">
        <div style="font-size:1.8rem;font-weight:800;color:var(--c-success)"><?= $metrics['pending_artisans'] ?></div>
        <div style="font-size:.82rem;color:var(--c-gray-400)">Artisans en attente</div>
      </div>
      <div class="card" style="text-align:center;padding:20px">
        <div style="font-size:1.8rem;font-weight:800;color:var(--c-danger)"><?= $metrics['pending_blog'] ?></div>
        <div style="font-size:.82rem;color:var(--c-gray-400)">Articles à valider</div>
      </div>
    </div>
  </main>
</div>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
const CSRF = '<?= Security::generateCsrf() ?>';
async function validateArtisan(id, action) {
  const msg = action==='validate' ? 'Valider cet artisan ?' : 'Refuser cet artisan ?';
  if (!confirm(msg)) return;
  const res = await fetch('<?= APP_URL ?>/admin/artisan/validate', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: `csrf_token=${CSRF}&artisan_id=${id}&action=${action}`
  });
  const d = await res.json();
  if (d.success) location.reload();
  else alert(d.error||'Erreur');
}
</script>
</body>
</html>
