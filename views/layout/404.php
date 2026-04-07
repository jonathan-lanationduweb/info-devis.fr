<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Page introuvable | InfoDevis</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
</head>
<body>
<?php require BASE_PATH . '/includes/navbar.php'; ?>
<div style="min-height:calc(100vh - var(--header-h));display:flex;align-items:center;justify-content:center;text-align:center;padding:40px 16px">
  <div>
    <div style="font-size:8rem;line-height:1;margin-bottom:16px;font-family:var(--f-display);font-weight:800;background:linear-gradient(135deg,var(--c-navy),var(--c-orange));-webkit-background-clip:text;-webkit-text-fill-color:transparent">404</div>
    <h1 style="margin-bottom:16px">Page introuvable</h1>
    <p style="color:var(--c-gray-400);max-width:400px;margin:0 auto 32px">La page que vous cherchez n'existe pas ou a été déplacée.</p>
    <div style="display:flex;gap:12px;justify-content:center">
      <a href="<?= APP_URL ?>/" class="btn btn-primary">Retour à l'accueil</a>
      <a href="<?= APP_URL ?>/devis" class="btn btn-outline">Demander un devis</a>
    </div>
  </div>
</div>
<?php require BASE_PATH . '/includes/footer.php'; ?>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
