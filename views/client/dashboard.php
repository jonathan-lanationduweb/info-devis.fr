<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= Security::e($pageTitle) ?></title>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
  <meta name="robots" content="noindex">
</head>
<body>
<div class="dashboard-layout">

  <!-- Sidebar -->
  <aside class="dashboard-sidebar">
    <div class="sidebar-logo"><a href="<?= APP_URL ?>">Info<span>Devis</span></a></div>
    <ul class="sidebar-menu">
      <li class="sidebar-section-title">Mon espace</li>
      <li><a href="<?= APP_URL ?>/dashboard/client">🏠 Tableau de bord</a></li>
      <li><a href="<?= APP_URL ?>/dashboard/client/devis">📋 Mes demandes</a></li>
      <li><a href="<?= APP_URL ?>/dashboard/client/messages">💬 Messages</a></li>
      <li><a href="<?= APP_URL ?>/dashboard/client/avis">⭐ Mes avis</a></li>
      <li class="sidebar-section-title">Autre</li>
      <li><a href="<?= APP_URL ?>/devis" style="color:var(--c-orange);font-weight:600">➕ Nouvelle demande</a></li>
      <li><a href="<?= APP_URL ?>">🏠 Site principal</a></li>
      <li><a href="<?= APP_URL ?>/deconnexion" style="color:rgba(255,100,100,.7)">🚪 Déconnexion</a></li>
    </ul>
  </aside>

  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Mon espace client</h1>
        <p style="color:var(--c-gray-400);margin:0;font-size:.9rem">Gérez vos demandes de devis</p>
      </div>
      <a href="<?= APP_URL ?>/devis" class="btn btn-primary">
        + Nouvelle demande
      </a>
    </div>

    <!-- Notifications -->
    <?php foreach ($notifs as $n): ?>
    <div class="alert alert-<?= str_contains($n['type'],'accepted') ? 'success' : (str_contains($n['type'],'refused') ? 'warning' : 'info') ?>" data-auto-dismiss>
      <strong><?= Security::e($n['title']) ?></strong> — <?= Security::e($n['body']) ?>
    </div>
    <?php endforeach; ?>

    <!-- Summary -->
    <div class="kpi-grid" style="margin-bottom:32px">
      <?php
      $total     = count($devis);
      $accepted  = count(array_filter($devis, fn($d) => $d['status'] === 'accepted'));
      $pending   = count(array_filter($devis, fn($d) => $d['status'] === 'sent' || $d['status'] === 'pending'));
      $completed = count(array_filter($devis, fn($d) => $d['status'] === 'completed'));
      ?>
      <div class="kpi-card"><div class="kpi-icon blue">📋</div><div><div class="kpi-value"><?= $total ?></div><div class="kpi-label">Demandes totales</div></div></div>
      <div class="kpi-card"><div class="kpi-icon orange">⏳</div><div><div class="kpi-value"><?= $pending ?></div><div class="kpi-label">En attente</div></div></div>
      <div class="kpi-card"><div class="kpi-icon green">✅</div><div><div class="kpi-value"><?= $accepted ?></div><div class="kpi-label">Acceptées</div></div></div>
      <div class="kpi-card"><div class="kpi-icon purple">🏆</div><div><div class="kpi-value"><?= $completed ?></div><div class="kpi-label">Terminées</div></div></div>
    </div>

    <!-- Devis list -->
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Mes demandes de devis</h4>
        <a href="<?= APP_URL ?>/dashboard/client/devis" style="font-size:.85rem;color:var(--c-blue-light)">Voir tout →</a>
      </div>
      <div class="card-body" style="padding:0">
        <?php if (empty($devis)): ?>
        <div style="padding:60px;text-align:center;color:var(--c-gray-400)">
          <div style="font-size:3rem;margin-bottom:16px">📋</div>
          <h3 style="font-family:var(--f-body)">Aucune demande</h3>
          <p>Vous n'avez pas encore fait de demande de devis.</p>
          <a href="<?= APP_URL ?>/devis" class="btn btn-primary" style="margin-top:16px">Faire ma première demande</a>
        </div>
        <?php else: ?>
        <div class="table-wrapper" style="border:none;border-radius:0">
          <table>
            <thead>
              <tr><th>Référence</th><th>Projet</th><th>Catégorie</th><th>Ville</th><th>Devis reçus</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($devis,0,8) as $d): ?>
              <tr>
                <td><strong style="font-size:.8rem;color:var(--c-gray-400)"><?= Security::e($d['reference']) ?></strong></td>
                <td>
                  <div style="font-weight:600;font-size:.9rem"><?= Security::e(substr($d['title'],0,40)) ?>...</div>
                  <div style="font-size:.75rem;color:var(--c-gray-400)"><?= date('d/m/Y', strtotime($d['created_at'])) ?></div>
                </td>
                <td><span class="badge badge-active" style="font-size:.72rem"><?= Security::e($d['category_name']) ?></span></td>
                <td><?= Security::e($d['ville']) ?></td>
                <td>
                  <span style="font-weight:700;color:var(--c-navy)"><?= $d['leads_count'] ?></span>
                  <?php if ($d['accepted_count']): ?>
                  <span style="color:var(--c-success);font-size:.78rem"> (<?= $d['accepted_count'] ?> accepté<?= $d['accepted_count']>1?'s':'' ?>)</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php $labels = ['pending'=>'En attente','sent'=>'Envoyé','accepted'=>'Accepté','in_progress'=>'En cours','completed'=>'Terminé','cancelled'=>'Annulé','refused'=>'Refusé']; ?>
                  <span class="badge badge-<?= $d['status'] ?>"><?= $labels[$d['status']] ?? $d['status'] ?></span>
                </td>
                <td style="display:flex;flex-direction:column;gap:4px">
                  <?php if ($d['status'] === 'accepted'): ?>
                    <a href="<?= APP_URL ?>/dashboard/client/messages" class="btn btn-navy btn-sm">💬 Messages</a>
                    <a href="<?= APP_URL ?>/dashboard/client/signature/<?= $d['id'] ?>" class="btn btn-outline btn-sm">✍️ Signer</a>
                  <?php endif; ?>
                  <?php if ($d['status'] === 'completed'): ?>
                    <a href="<?= APP_URL ?>/dashboard/client/avis?devis=<?= $d['id'] ?>" class="btn btn-outline btn-sm">⭐ Avis</a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
