<?php
$initials = strtoupper(substr($artisan['first_name'] ?? 'A', 0, 1) . substr($artisan['last_name'] ?? '', 0, 1));
$planColors = ['gratuit' => '#9ca3af', 'starter' => '#3b82f6', 'pro' => '#8b5cf6', 'illimite' => '#f97316'];
$planColor  = $planColors[$artisan['plan'] ?? 'gratuit'] ?? '#9ca3af';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= Security::e($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
  <meta name="robots" content="noindex">
</head>
<body>
<div class="dashboard-layout">

  <!-- Sidebar -->
  <aside class="dashboard-sidebar">
    <div class="sidebar-logo">
      <a href="<?= APP_URL ?>">Info<span>Devis</span></a>
    </div>

    <!-- Profile mini -->
    <div style="padding:0 20px 24px;border-bottom:1px solid rgba(255,255,255,.08);margin-bottom:16px">
      <div style="display:flex;align-items:center;gap:12px">
        <div style="width:44px;height:44px;border-radius:50%;background:var(--c-orange);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1rem;flex-shrink:0">
          <?= $initials ?>
        </div>
        <div>
          <div style="color:#fff;font-weight:600;font-size:.9rem"><?= Security::e($artisan['company_name'] ?? $artisan['first_name']) ?></div>
          <div style="font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:999px;display:inline-block;margin-top:3px;background:<?= $planColor ?>22;color:<?= $planColor ?>;border:1px solid <?= $planColor ?>44">
            Plan <?= ucfirst($artisan['plan'] ?? 'gratuit') ?>
          </div>
        </div>
      </div>
      <?php if (!$artisan['is_verified']): ?>
      <div style="margin-top:12px;background:rgba(249,115,22,.15);border:1px solid rgba(249,115,22,.3);border-radius:8px;padding:8px 10px;font-size:.78rem;color:#fdba74">
        ⚠️ Compte en attente de validation
      </div>
      <?php endif; ?>
    </div>

    <ul class="sidebar-menu">
      <li class="sidebar-section-title">Principal</li>
      <li><a href="<?= APP_URL ?>/dashboard/artisan" class="<?= str_ends_with($_SERVER['REQUEST_URI'],'/artisan') ? 'active' : '' ?>">
        📊 Tableau de bord
      </a></li>
      <li><a href="<?= APP_URL ?>/dashboard/artisan/leads" class="<?= str_contains($_SERVER['REQUEST_URI'],'/leads') ? 'active' : '' ?>">
        🎯 Mes leads
        <?php if (($stats['pending_leads'] ?? 0) > 0): ?>
        <span style="margin-left:auto;background:var(--c-orange);color:#fff;border-radius:999px;padding:2px 8px;font-size:.72rem;font-weight:700"><?= $stats['pending_leads'] ?></span>
        <?php endif; ?>
      </a></li>
      <li><a href="<?= APP_URL ?>/dashboard/artisan/messages" class="<?= str_contains($_SERVER['REQUEST_URI'],'/messages') ? 'active' : '' ?>">
        💬 Messages
        <?php if (($stats['unread_messages'] ?? 0) > 0): ?>
        <span style="margin-left:auto;background:var(--c-danger);color:#fff;border-radius:999px;padding:2px 8px;font-size:.72rem;font-weight:700"><?= $stats['unread_messages'] ?></span>
        <?php endif; ?>
      </a></li>

      <li class="sidebar-section-title">Mon compte</li>
      <li><a href="<?= APP_URL ?>/dashboard/artisan/profile">👤 Mon profil</a></li>
      <li><a href="<?= APP_URL ?>/dashboard/artisan/calendar">📅 Disponibilités</a></li>
      <li><a href="<?= APP_URL ?>/dashboard/artisan/documents">📄 Mes documents</a></li>
      <li><a href="<?= APP_URL ?>/dashboard/artisan/stats">📈 Statistiques</a></li>
      <li><a href="<?= APP_URL ?>/dashboard/artisan/abonnement">💳 Abonnement</a></li>
      <li class="sidebar-section-title">Autre</li>
      <li><a href="<?= APP_URL ?>">🏠 Site principal</a></li>
      <li><a href="<?= APP_URL ?>/deconnexion" style="color:rgba(255,100,100,.7)">🚪 Déconnexion</a></li>
    </ul>
  </aside>

  <!-- Main content -->
  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Bonjour, <?= Security::e($artisan['first_name'] ?? 'Artisan') ?> 👋</h1>
        <p style="color:var(--c-gray-400);margin:0;font-size:.9rem"><?= date('l d F Y') ?></p>
      </div>
      <a href="<?= APP_URL ?>/dashboard/artisan/leads" class="btn btn-primary">Voir mes leads</a>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-icon blue">🎯</div>
        <div>
          <div class="kpi-value"><?= $stats['total_leads'] ?></div>
          <div class="kpi-label">Leads reçus</div>
        </div>
      </div>
      <div class="kpi-card">
        <div class="kpi-icon green">✅</div>
        <div>
          <div class="kpi-value"><?= $stats['accepted_leads'] ?></div>
          <div class="kpi-label">Leads acceptés</div>
        </div>
      </div>
      <div class="kpi-card">
        <div class="kpi-icon orange">⏳</div>
        <div>
          <div class="kpi-value"><?= $stats['pending_leads'] ?></div>
          <div class="kpi-label">En attente</div>
        </div>
      </div>
      <div class="kpi-card">
        <div class="kpi-icon purple">⭐</div>
        <div>
          <div class="kpi-value"><?= $stats['avg_rating'] ?>/5</div>
          <div class="kpi-label">Note moyenne (<?= $stats['total_avis'] ?> avis)</div>
        </div>
      </div>
    </div>

    <!-- Verification alert -->
    <?php if (!$artisan['is_verified']): ?>
    <div class="alert alert-warning" style="margin-bottom:24px">
      <strong>⚠️ Votre compte est en attente de validation.</strong>
      Déposez vos documents pour accélérer le processus.
      <a href="<?= APP_URL ?>/dashboard/artisan/documents" style="margin-left:8px;font-weight:700">Déposer mes documents →</a>
    </div>
    <?php endif; ?>

    <!-- Upgrade banner -->
    <?php if (($artisan['plan'] ?? 'gratuit') === 'gratuit'): ?>
    <div style="background:linear-gradient(135deg,var(--c-navy),var(--c-blue));border-radius:var(--radius-lg);padding:28px 32px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
      <div>
        <h3 style="color:#fff;margin-bottom:6px;font-family:var(--f-body)">🚀 Passez au plan Pro</h3>
        <p style="color:rgba(255,255,255,.7);font-size:.9rem;margin:0">Recevez plus de leads, badge vérifié, statistiques avancées</p>
      </div>
      <a href="<?= APP_URL ?>/dashboard/artisan/abonnement" class="btn btn-primary">Voir les plans →</a>
    </div>
    <?php endif; ?>

    <!-- Quick leads -->
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Derniers leads reçus</h4>
        <a href="<?= APP_URL ?>/dashboard/artisan/leads" style="font-size:.85rem;color:var(--c-blue-light)">Voir tous →</a>
      </div>
      <div class="card-body" style="padding:0">
        <div class="table-wrapper" style="border:none;border-radius:0">
          <?php
          $recentLeads = Database::fetchAll(
            'SELECT l.*, d.reference, d.title, d.ville, d.urgency, c.name as cat, u.first_name, u.last_name
             FROM leads l JOIN devis d ON d.id=l.devis_id JOIN categories c ON c.id=l.category_id JOIN users u ON u.id=d.client_id
             WHERE l.artisan_id=? ORDER BY l.created_at DESC LIMIT 5',
            [$artisan['id']]
          );
          ?>
          <?php if (empty($recentLeads)): ?>
          <div style="padding:40px;text-align:center;color:var(--c-gray-400)">
            <div style="font-size:2.5rem;margin-bottom:12px">🎯</div>
            <p>Aucun lead pour l'instant. Complétez votre profil pour en recevoir !</p>
            <a href="<?= APP_URL ?>/dashboard/artisan/profile" class="btn btn-primary btn-sm" style="margin-top:12px">Compléter mon profil</a>
          </div>
          <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Réf.</th>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Ville</th>
                <th>Urgence</th>
                <th>Statut</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentLeads as $l): ?>
              <tr>
                <td><span style="font-weight:600;font-size:.8rem;color:var(--c-gray-400)"><?= Security::e($l['reference']) ?></span></td>
                <td><?= Security::e(substr($l['title'],0,40)) ?>...</td>
                <td><span class="badge badge-active"><?= Security::e($l['cat']) ?></span></td>
                <td><?= Security::e($l['ville']) ?></td>
                <td>
                  <?php $urgColors = ['normal'=>'badge-active','urgent'=>'badge-pending','tres_urgent'=>'badge-refused']; ?>
                  <span class="badge <?= $urgColors[$l['urgency']] ?? 'badge-active' ?>"><?= ucfirst($l['urgency']) ?></span>
                </td>
                <td>
                  <span class="badge badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span>
                </td>
                <td>
                  <?php if ($l['status'] === 'pending'): ?>
                  <div style="display:flex;gap:6px">
                    <button onclick="respondLead(<?= $l['id'] ?>,'accepted')" class="btn btn-success btn-sm">Accepter</button>
                    <button onclick="respondLead(<?= $l['id'] ?>,'refused')"  class="btn btn-danger btn-sm">Refuser</button>
                  </div>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
const CSRF = '<?= Security::generateCsrf() ?>';
async function respondLead(leadId, status) {
  if (!confirm(status === 'accepted' ? 'Accepter ce lead ?' : 'Refuser ce lead ?')) return;
  const res = await fetch('<?= APP_URL ?>/dashboard/artisan/lead/respond', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `csrf_token=${CSRF}&lead_id=${leadId}&status=${status}`
  });
  const d = await res.json();
  if (d.success) location.reload();
  else alert(d.error || 'Erreur');
}
</script>
</body>
</html>
