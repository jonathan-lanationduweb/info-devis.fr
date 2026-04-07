<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= Security::e($pageTitle ?? 'InfoDevis - Trouvez le bon artisan') ?></title>
  <meta name="description" content="<?= Security::e($metaDesc ?? 'InfoDevis : obtenez jusqu\'à 5 devis gratuits d\'artisans qualifiés sous 24h. Plombier, électricien, peintre, maçon et plus.') ?>">
  <meta name="robots" content="<?= $noIndex ?? 'index, follow' ?>">
  <meta property="og:title" content="<?= Security::e($pageTitle ?? 'InfoDevis') ?>">
  <meta property="og:description" content="<?= Security::e($metaDesc ?? '') ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= APP_URL . $_SERVER['REQUEST_URI'] ?>">
  <meta property="og:image" content="<?= APP_URL ?>/assets/img/og-default.jpg">
  <link rel="canonical" href="<?= APP_URL . ($_SERVER['REQUEST_URI'] ?? '/') ?>">
  <link rel="icon" href="<?= APP_URL ?>/assets/img/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
  <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/<?= Security::e($css) ?>">
  <?php endforeach; endif; ?>
  <!-- Schema.org -->
  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"LocalBusiness","name":"InfoDevis","url":"<?= APP_URL ?>","telephone":"<?= CONTACT_PHONE ?>","email":"<?= CONTACT_EMAIL ?>","address":{"@type":"PostalAddress","streetAddress":"45 Rue des Boulets","addressLocality":"Paris","postalCode":"75011","addressCountry":"FR"}}
  </script>
</head>
<body>

<?php require BASE_PATH . '/includes/navbar.php'; ?>

<main id="main-content">
  <?= $content ?>
</main>

<?php require BASE_PATH . '/includes/footer.php'; ?>
<?php require BASE_PATH . '/chatbot/chatbot.php'; ?>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script src="<?= APP_URL ?>/chatbot/chatbot.js"></script>
<?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= APP_URL ?>/assets/js/<?= Security::e($js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
