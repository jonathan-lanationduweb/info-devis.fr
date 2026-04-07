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
  <?php require BASE_PATH . '/views/artisan/_sidebar.php'; ?>
  <main class="dashboard-main">
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Mes Leads</h1>
        <p style="color:var(--c-gray-400);margin:0;font-size:.9rem">Gérez vos demandes de devis</p>
      </div>
    </div>

    <!-- Filters -->
    <div style="display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap">
      <?php foreach(['all'=>'Tous','pending'=>'En attente','accepted'=>'Acceptés','refused'=>'Refusés'] as $v=>$l): ?>
      <a href="?status=<?= $v ?>" class="btn btn-sm <?= ($_GET['status']??'all')===$v ? 'btn-navy' : 'btn-outline' ?>"><?= $l ?></a>
      <?php endforeach; ?>
    </div>

    <div class="card">
      <div class="card-body" style="padding:0">
        <div class="table-wrapper" style="border:none;border-radius:0">
          <?php if (empty($leads)): ?>
          <div style="padding:60px;text-align:center;color:var(--c-gray-400)">
            <div style="font-size:3rem;margin-bottom:16px">🎯</div>
            <h3 style="font-family:var(--f-body)">Aucun lead</h3>
            <p>Vous n'avez pas encore de leads. Complétez votre profil et activez vos zones d'intervention.</p>
            <a href="<?= APP_URL ?>/dashboard/artisan/profile" class="btn btn-primary" style="margin-top:16px">Compléter mon profil</a>
          </div>
          <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Réf.</th>
                <th>Titre & Description</th>
                <th>Contact</th>
                <th>Catégorie</th>
                <th>Urgence</th>
                <th>Ville</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($leads as $l): ?>
              <tr id="row-<?= $l['id'] ?>">
                <td><span style="font-size:.78rem;font-weight:700;color:var(--c-gray-400)"><?= Security::e($l['reference']) ?></span></td>
                <td style="max-width:240px">
                  <div style="font-weight:600;font-size:.9rem"><?= Security::e(substr($l['title'],0,45)) ?>...</div>
                  <div style="font-size:.78rem;color:var(--c-gray-400);margin-top:3px"><?= Security::e(substr($l['description'],0,80)) ?>...</div>
                </td>
                <td>
                  <?php if ($l['status'] === 'accepted'): ?>
                    <div style="font-weight:600;font-size:.88rem"><?= Security::e($l['first_name'].' '.$l['last_name']) ?></div>
                  <?php else: ?>
                    <span style="color:var(--c-gray-400);font-size:.82rem">Visible après acceptation</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge badge-active" style="font-size:.72rem"><?= Security::e($l['cat']) ?></span></td>
                <td>
                  <?php $urg = ['normal'=>['badge-active','Normal'],'urgent'=>['badge-pending','Urgent'],'tres_urgent'=>['badge-refused','Très urgent']]; ?>
                  <span class="badge <?= $urg[$l['urgency']][0] ?? 'badge-active' ?>"><?= $urg[$l['urgency']][1] ?? $l['urgency'] ?></span>
                </td>
                <td style="font-weight:500"><?= Security::e($l['ville']) ?></td>
                <td>
                  <span class="badge badge-<?= $l['status'] ?>"><?= ['pending'=>'En attente','accepted'=>'Accepté','refused'=>'Refusé','expired'=>'Expiré'][$l['status']] ?? $l['status'] ?></span>
                </td>
                <td>
                  <?php if ($l['status'] === 'pending'): ?>
                  <div style="display:flex;flex-direction:column;gap:5px">
                    <button onclick="respondLead(<?= $l['id'] ?>,'accepted')" class="btn btn-success btn-sm">✓ Accepter</button>
                    <button onclick="respondLead(<?= $l['id'] ?>,'refused')"  class="btn btn-danger btn-sm">✗ Refuser</button>
                  </div>
                  <?php elseif ($l['status'] === 'accepted'): ?>
                    <a href="<?= APP_URL ?>/dashboard/artisan/messages?lead=<?= $l['lead_id'] ?? $l['id'] ?>" class="btn btn-navy btn-sm">💬 Contacter</a>
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

    <!-- Pagination -->
    <?php if ($page > 1 || count($leads) === 20): ?>
    <div style="display:flex;justify-content:center;gap:8px;margin-top:24px">
      <?php if ($page > 1): ?>
      <a href="?page=<?= $page-1 ?>" class="btn btn-outline btn-sm">← Précédent</a>
      <?php endif; ?>
      <?php if (count($leads) === 20): ?>
      <a href="?page=<?= $page+1 ?>" class="btn btn-outline btn-sm">Suivant →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </main>
</div>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
const CSRF = '<?= Security::generateCsrf() ?>';
async function respondLead(leadId, status, note='', price=null) {
  const row = document.getElementById('row-'+leadId);
  const body = new URLSearchParams({csrf_token:CSRF, lead_id:leadId, status, note});
  if (price) body.append('price', price);

  const res = await fetch('<?= APP_URL ?>/dashboard/artisan/lead/respond', {method:'POST', body});
  const d   = await res.json();
  if (d.success) {
    if(row) row.style.opacity = '.4';
    setTimeout(() => location.reload(), 600);
  } else {
    alert(d.error || 'Erreur');
  }
}
</script>
</body>
</html>
