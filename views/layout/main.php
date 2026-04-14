<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= Security::e($pageTitle ?? 'InfoDevis - Trouvez le bon artisan') ?></title>
  <meta name="description" content="<?= Security::e($metaDesc ?? 'InfoDevis : obtenez jusqu\'à 5 devis gratuits d\'artisans qualifiés sous 24h.') ?>">
  <meta name="robots" content="<?= $noIndex ?? 'index, follow' ?>">
  <meta property="og:title" content="<?= Security::e($pageTitle ?? 'InfoDevis') ?>">
  <meta property="og:description" content="<?= Security::e($metaDesc ?? '') ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= APP_URL . $_SERVER['REQUEST_URI'] ?>">
  <meta property="og:image" content="<?= APP_URL ?>/assets/img/og-default.jpg">
  <meta name="base-url" content="<?= APP_URL ?>">
  <link rel="canonical" href="<?= APP_URL . ($_SERVER['REQUEST_URI'] ?? '/') ?>">
  <link rel="icon" href="<?= APP_URL ?>/assets/img/favicon.ico" type="image/x-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            'primary': '#207752',
            'primary-dk': '#165a3c',
            'primary-lt': '#28936a',
            'primary-container': '#a0f4c6',
            'on-primary': '#e1ffeb',
            'on-primary-container': '#005e3d',
            'secondary': '#57615c',
            'secondary-container': '#dae5de',
            'on-secondary-container': '#4a544f',
            'teal': '#157e90',
            'teal-dk': '#0f6474',
            'surface': '#faf9f8',
            'surface-container-low': '#f3f4f3',
            'surface-container': '#edeeed',
            'surface-container-high': '#e6e9e8',
            'surface-dim': '#d6dbda',
            'on-surface': '#2f3333',
            'on-surface-variant': '#5b605f',
            'outline': '#777c7b',
            'outline-variant': '#aeb3b2',
            'background': '#faf9f8',
            'on-background': '#2f3333',
            'error': '#9f403d',
            'gold': '#f0b429',
          },
          fontFamily: {
            headline: ['Newsreader', 'serif'],
            body: ['Manrope', 'sans-serif'],
            label: ['Manrope', 'sans-serif'],
          },
          borderRadius: {
            DEFAULT: '0.125rem',
            lg: '0.5rem',
            xl: '1rem',
            '2xl': '1.5rem',
            full: '9999px',
          },
        },
      },
    };
  </script>

  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
    }

    .editorial-shadow {
      box-shadow: 0 30px 60px -12px rgba(47, 51, 51, .08);
    }

    .glass-nav {
      background: rgba(7, 30, 36, .92);
      backdrop-filter: blur(20px);
    }

    /* Reveal animation */
    .reveal {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity .55s ease, transform .55s ease;
    }

    .reveal.visible {
      opacity: 1;
      transform: none;
    }

    .reveal-delay-1 {
      transition-delay: .1s;
    }

    .reveal-delay-2 {
      transition-delay: .2s;
    }

    .reveal-delay-3 {
      transition-delay: .3s;
    }

    /* Chatbot classes conservées */
    .chatbot-btn {
      position: fixed;
      bottom: 28px;
      right: 28px;
      z-index: 9000;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: #207752;
      color: #fff;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 24px rgba(32, 119, 82, .35);
      transition: .2s;
    }

    .chatbot-btn:hover {
      transform: scale(1.08);
      background: #165a3c;
    }

    .chatbot-window {
      position: fixed;
      bottom: 96px;
      right: 28px;
      z-index: 9001;
      width: 360px;
      max-height: 560px;
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 32px 80px rgba(0, 0, 0, .18);
      border: 1px solid #e2e8ea;
      display: none;
      flex-direction: column;
      overflow: hidden;
    }

    .chatbot-window.open {
      display: flex;
      animation: cbOpen .28s cubic-bezier(.34, 1.56, .64, 1);
    }

    @keyframes cbOpen {
      from {
        transform: scale(.88);
        opacity: 0
      }

      to {
        transform: scale(1);
        opacity: 1
      }
    }

    .chatbot-header {
      background: linear-gradient(135deg, #0d2b33, #157e90);
      padding: 16px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .chatbot-avatar {
      width: 38px;
      height: 38px;
      background: #207752;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .chatbot-name {
      font-weight: 700;
      color: #fff;
      font-size: .9rem;
      font-family: Manrope, sans-serif;
    }

    .chatbot-status {
      font-size: .72rem;
      color: rgba(255, 255, 255, .65);
      font-family: Manrope, sans-serif;
    }

    .chatbot-close {
      margin-left: auto;
      background: none;
      border: none;
      cursor: pointer;
      color: rgba(255, 255, 255, .65);
      font-size: 1.1rem;
      padding: 4px;
      line-height: 1;
    }

    .chatbot-messages {
      flex: 1;
      overflow-y: auto;
      padding: 12px;
      display: flex;
      flex-direction: column;
      gap: 9px;
    }

    .chat-msg {
      max-width: 86%;
      padding: 10px 14px;
      border-radius: 14px;
      font-size: .86rem;
      line-height: 1.5;
      font-family: Manrope, sans-serif;
      animation: fadeUp .22s ease;
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(6px)
      }

      to {
        opacity: 1;
        transform: none
      }
    }

    .chat-msg.bot {
      background: #f3f4f3;
      align-self: flex-start;
      border-bottom-left-radius: 4px;
    }

    .chat-msg.user {
      background: #157e90;
      color: #fff;
      align-self: flex-end;
      border-bottom-right-radius: 4px;
    }

    .chatbot-quick-replies {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      padding: 0 12px 8px;
    }

    .quick-reply-btn {
      background: #f3f4f3;
      border: 1px solid #e2e8ea;
      border-radius: 9999px;
      padding: 6px 12px;
      font-size: .78rem;
      font-weight: 500;
      cursor: pointer;
      transition: .18s;
      color: #2f3333;
      font-family: Manrope, sans-serif;
    }

    .quick-reply-btn:hover {
      background: #207752;
      color: #fff;
      border-color: #207752;
    }

    .chatbot-input-bar {
      display: flex;
      gap: 8px;
      padding: 10px 12px;
      border-top: 1px solid #e2e8ea;
    }

    .chatbot-input {
      flex: 1;
      border: 1.5px solid #e2e8ea;
      border-radius: 9999px;
      padding: 8px 14px;
      font-family: Manrope, sans-serif;
      font-size: .86rem;
      outline: none;
      transition: .18s;
      color: #2f3333;
    }

    .chatbot-input:focus {
      border-color: #157e90;
    }

    .chatbot-send {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #207752;
      border: none;
      cursor: pointer;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: .2s;
      flex-shrink: 0;
    }

    .chatbot-send:hover {
      background: #165a3c;
    }

    .category-checkboxes {
      display: flex;
      flex-direction: column;
      gap: 6px;
      padding: 4px 0;
    }

    .cat-check-label {
      display: flex;
      align-items: center;
      gap: 8px;
      background: #f3f4f3;
      border: 1px solid #e2e8ea;
      border-radius: 8px;
      padding: 8px 12px;
      cursor: pointer;
      transition: .18s;
      font-size: .84rem;
      font-family: Manrope, sans-serif;
    }

    .cat-check-label:hover {
      border-color: #207752;
      background: #f0faf5;
    }

    .cat-check-label input[type=checkbox] {
      accent-color: #207752;
      width: 15px;
      height: 15px;
      cursor: pointer;
    }

    /* Page banners */
    .page-banner {
      position: relative;
      overflow: hidden;
      background: #071e24;
      padding: 120px 0 64px;
      text-align: center;
    }

    .page-banner::before {
      content: '';
      position: absolute;
      inset: 0;
      background-size: cover;
      background-position: center;
      opacity: .55;
      z-index: 0;
    }

    .page-banner::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(7, 30, 36, .55) 0%, rgba(7, 30, 36, .45) 100%);
      z-index: 1;
    }

    .page-banner--blog::before {
      background-image: url('../img/blog.png');
    }

    .page-banner--metier::before {
      background-image: url('../img/metier.png');
    }

    .page-banner--guides::before {
      background-image: url('../img/metier.png');
    }

    .page-banner .container {
      position: relative;
      z-index: 2;
    }

    .page-banner__eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(32, 119, 82, .18);
      border: 1px solid rgba(32, 119, 82, .35);
      color: #6ee7b7;
      border-radius: 9999px;
      font-size: .74rem;
      font-weight: 600;
      padding: 5px 14px;
      margin-bottom: 16px;
      letter-spacing: .05em;
      text-transform: uppercase;
      font-family: Manrope, sans-serif;
    }

    .page-banner__title {
      color: #fff;
      font-family: Newsreader, serif;
      font-size: clamp(2rem, 5vw, 3.2rem);
      font-weight: 700;
      margin-bottom: 12px;
    }

    .page-banner__subtitle {
      color: rgba(255, 255, 255, .78);
      font-size: 1.05rem;
      max-width: 540px;
      margin: 0 auto;
      font-family: Manrope, sans-serif;
    }

    /* Guides */
    .guide-card {
      background: #fff;
      border-radius: .75rem;
      border: 1px solid #e2e8ea;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
      display: flex;
      flex-direction: column;
    }

    .guide-card__header {
      padding: 16px 20px 12px;
      border-bottom: 1px solid #f3f4f3;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .guide-card__icon {
      font-size: 1.6rem;
    }

    .guide-card__title {
      margin: 0;
      font-size: 1rem;
      font-weight: 700;
      color: #0d2b33;
      font-family: Manrope, sans-serif;
    }

    .guide-table {
      width: 100%;
      border-collapse: collapse;
    }

    .guide-table__th {
      text-align: left;
      padding: 8px 20px;
      font-size: .7rem;
      color: #5b605f;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .05em;
      background: #faf9f8;
    }

    .guide-table__th--right {
      text-align: right;
    }

    .guide-table__tr {
      border-top: 1px solid #f3f4f3;
    }

    .guide-table__tr--alt {
      background: #fafafa;
    }

    .guide-table__td {
      padding: 10px 20px;
      font-size: .86rem;
      color: #334155;
    }

    .guide-table__td--price {
      text-align: right;
      font-weight: 600;
      color: #157e90;
      white-space: nowrap;
    }

    .guide-card__footer {
      padding: 12px 20px;
      background: #faf9f8;
      border-top: 1px solid #f3f4f3;
      margin-top: auto;
    }

    .guide-card__cta {
      font-size: .82rem;
      font-weight: 600;
      color: #157e90;
      text-decoration: none;
    }

    .guide-card__cta:hover {
      color: #0f6474;
    }

    .guides-notice {
      background: #fef9c3;
      border: 1px solid #fde047;
      border-radius: .5rem;
      padding: 14px 20px;
      display: flex;
      gap: 10px;
      align-items: flex-start;
      max-width: 760px;
      margin: 0 auto;
    }

    .guides-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 26px;
    }

    /* Blog */
    .blog-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 24px;
    }

    .blog-card {
      display: flex;
      flex-direction: column;
      background: #fff;
      border-radius: .75rem;
      border: 1px solid #e2e8ea;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
      text-decoration: none;
      color: inherit;
      transition: .3s;
    }

    .blog-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 16px 40px rgba(13, 43, 51, .14);
    }

    .blog-card__visual {
      height: 100px;
      background: linear-gradient(135deg, #0f6474, #157e90);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.6rem;
    }

    .blog-card__body {
      padding: 16px 18px;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .blog-card__meta {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-wrap: wrap;
    }

    .blog-card__cat {
      background: #ecfeff;
      color: #157e90;
      font-size: .68rem;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 9999px;
      text-transform: uppercase;
      letter-spacing: .05em;
    }

    .blog-card__time {
      color: #777c7b;
      font-size: .74rem;
    }

    .blog-card__title {
      margin: 0;
      font-size: .92rem;
      font-weight: 700;
      color: #2f3333;
      line-height: 1.4;
    }

    .blog-card__excerpt {
      margin: 0;
      font-size: .82rem;
      color: #5b605f;
      line-height: 1.55;
      flex: 1;
    }

    .blog-card__footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 10px;
      padding-top: 10px;
      border-top: 1px solid #f3f4f3;
    }

    .blog-card__date {
      font-size: .74rem;
      color: #777c7b;
    }

    .blog-card__lire {
      font-size: .78rem;
      font-weight: 600;
      color: #157e90;
    }

    .blog-empty {
      text-align: center;
      padding: 64px 20px;
      color: #5b605f;
    }

    .blog-empty__icon {
      font-size: 2.6rem;
      margin-bottom: 12px;
    }

    /* Mobile responsive */
    @media (max-width:768px) {

      .guides-grid,
      .blog-grid {
        grid-template-columns: 1fr;
      }

      .chatbot-window {
        width: calc(100vw - 24px);
        right: 12px;
        bottom: 86px;
      }
    }
  </style>

  <!-- Schema.org -->
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "InfoDevis",
      "url": "<?= APP_URL ?>",
      "telephone": "<?= CONTACT_PHONE ?>",
      "email": "<?= CONTACT_EMAIL ?>",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "45 Rue des Boulets",
        "addressLocality": "Paris",
        "postalCode": "75011",
        "addressCountry": "FR"
      }
    }
  </script>
</head>

<body class="bg-background text-on-background font-body">

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
  <?php endforeach;
  endif; ?>
</body>

</html>