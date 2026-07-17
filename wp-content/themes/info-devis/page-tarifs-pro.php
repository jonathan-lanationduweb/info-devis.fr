<?php
/**
 * Tarifs Pro — reproduction fidèle de views/home/tarifs.php :
 * hero avec témoignage, 3 plans (Gratuit 0€ / Silver 10€ / Gold 14€ ⭐),
 * encart « tarifs attractifs », bento features, FAQ, checkout Stripe.
 */

get_header();

$idv_logged  = is_user_logged_in();
$idv_role    = $idv_logged ? ((array) wp_get_current_user()->roles)[0] ?? '' : '';
?>

<div class="pt-32 pb-24">

  <!-- Hero -->
  <section class="max-w-7xl mx-auto px-8 mb-24">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
      <div>
        <h1 class="text-6xl md:text-7xl font-headline leading-tight mb-8 text-on-background">
          Propulsez votre <span class="italic text-primary">activité d'artisan</span>
        </h1>
        <p class="text-xl font-body text-on-surface-variant leading-relaxed max-w-xl mb-12">
          Accédez à des leads qualifiés, renforcez votre crédibilité avec nos badges vérifiés et gérez vos demandes depuis un seul tableau de bord.
        </p>
        <div class="flex flex-wrap gap-4">
          <div class="flex items-center gap-3 py-3 px-5 bg-surface-container rounded-full border border-outline-variant/15">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-primary">
              <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
              <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            <span class="font-label text-sm font-semibold uppercase tracking-wider">Badge Vérifié</span>
          </div>
          <div class="flex items-center gap-3 py-3 px-5 bg-surface-container rounded-full border border-outline-variant/15">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-primary">
              <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
            </svg>
            <span class="font-label text-sm font-semibold uppercase tracking-wider">Leads Prioritaires</span>
          </div>
        </div>
      </div>
      <div class="relative h-[500px] rounded-xl overflow-hidden shadow-2xl">
        <img src="<?php echo esc_url(IDV_THEME_URI . '/assets/images/artisan.png'); ?>" alt="Artisan professionnel" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-primary/40 to-transparent"></div>
        <div class="absolute bottom-8 left-8 right-8 p-8 bg-white/80 backdrop-blur-xl rounded-lg">
          <p class="font-headline italic text-xl text-on-background mb-3">"InfoDevis a transformé mon entreprise. La qualité des contacts est incomparable."</p>
          <p class="font-label text-sm font-bold uppercase tracking-widest text-primary">— Marc Lefebvre, Ébéniste</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Plans tarifaires -->
  <section class="max-w-7xl mx-auto px-8 mb-32">
    <div class="text-center mb-20">
      <h2 class="text-4xl font-headline text-on-background mb-4">Choisissez la formule qui vous correspond</h2>
      <p class="font-body text-on-surface-variant">Des solutions flexibles pour les artisans en quête d'excellence.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch max-w-5xl mx-auto">

      <!-- ── PLAN GRATUIT ─────────────────────────────────────── -->
      <div class="bg-surface-container-low p-8 flex flex-col border border-outline-variant/10 rounded-xl hover:border-primary/20 transition-all">
        <div class="mb-8">
          <span class="font-label text-xs font-bold uppercase tracking-widest text-on-surface-variant block mb-3">Découverte</span>
          <h3 class="text-3xl font-headline mb-2">Gratuit</h3>
          <div class="flex items-baseline gap-1 mb-2"><span class="text-4xl font-headline font-bold">0€</span><span class="text-on-surface-variant font-body">/mois</span></div>
          <p class="text-on-surface-variant font-body text-sm">Pour démarrer et tester la plateforme</p>
        </div>
        <div class="flex-grow space-y-3 mb-8">
          <?php foreach ([
              'Profil professionnel basique',
              '1 réalisation maximum (1 photo)',
              '1 réalisation (1 photo)',
              'Badge "Référencé" (SIRET vérifié)',
              'Visibilité standard',
              'Système de prise de RDV en ligne',
              'Profil professionnel',
              'Notifications email + cloche',
          ] as $idv_f) : ?>
            <div class="flex items-start gap-3">
              <i class="fa-solid fa-circle-check text-primary flex-shrink-0" style="font-size:16px;margin-top:3px;" aria-hidden="true"></i>
              <span class="font-body text-sm"><?php echo esc_html($idv_f); ?></span>
            </div>
          <?php endforeach; ?>
          <?php foreach (['Statistiques détaillées', 'Mise en avant Top 5', 'Publication d\'articles blog', 'Thème personnalisé'] as $idv_f) : ?>
            <div class="flex items-start gap-3 opacity-35">
              <i class="fa-solid fa-circle-xmark flex-shrink-0" style="font-size:16px;margin-top:3px;" aria-hidden="true"></i>
              <span class="font-body text-sm"><?php echo esc_html($idv_f); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <a href="<?php echo esc_url(home_url('/inscription/?type=artisan')); ?>" class="w-full py-4 border border-primary text-primary font-bold text-center rounded-lg hover:bg-primary hover:text-white transition-all block">S'inscrire gratuitement</a>
      </div>

      <!-- ── PLAN SILVER (10€) ────────────────────────────────── -->
      <div class="bg-surface-container-low p-8 flex flex-col border border-outline-variant/10 rounded-xl hover:border-primary/20 transition-all">
        <div class="mb-8">
          <span class="font-label text-xs font-bold uppercase tracking-widest text-on-surface-variant block mb-3">Essentiel</span>
          <h3 class="text-3xl font-headline mb-2">Silver</h3>
          <div class="flex items-baseline gap-1 mb-2"><span class="text-4xl font-headline font-bold text-primary">10€</span><span class="text-on-surface-variant font-body">/mois</span></div>
          <p class="text-on-surface-variant font-body text-sm">La solution idéale pour valoriser votre portfolio</p>
        </div>
        <div class="flex-grow space-y-3 mb-8">
          <?php foreach ([
              '5 réalisations (3 photos chacune)',
              'Badge "Vérifié" (après upload documents)',
              'Visibilité renforcée (zone milieu)',
              'Statistiques de profil basiques',
              'Système de prise de RDV en ligne',
              'Gestion des disponibilités',
              'Publication d\'articles blog (3/mois)',
              'Notifications email + cloche',
          ] as $idv_f) : ?>
            <div class="flex items-start gap-3">
              <i class="fa-solid fa-circle-check text-primary flex-shrink-0" style="font-size:16px;margin-top:3px;" aria-hidden="true"></i>
              <span class="font-body text-sm"><?php echo esc_html($idv_f); ?></span>
            </div>
          <?php endforeach; ?>
          <?php foreach (['Statistiques détaillées (graphiques)', 'Top 5 dans les recherches', 'Articles illimités', 'Thème personnalisé'] as $idv_f) : ?>
            <div class="flex items-start gap-3 opacity-35">
              <i class="fa-solid fa-circle-xmark flex-shrink-0" style="font-size:16px;margin-top:3px;" aria-hidden="true"></i>
              <span class="font-body text-sm"><?php echo esc_html($idv_f); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" data-plan-checkout="silver"
                class="w-full py-4 border border-primary text-primary font-bold text-center rounded-lg hover:bg-primary hover:text-white transition-all block cursor-pointer">
          Choisir Silver
        </button>
      </div>

      <!-- ── PLAN GOLD (14€) ⭐ Meilleure offre ──────────────── -->
      <div class="bg-white p-8 flex flex-col relative shadow-xl border-t-4 border-primary rounded-xl scale-105 z-10">
        <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary text-white px-4 py-1 text-xs font-bold uppercase tracking-widest rounded-full whitespace-nowrap">⭐ Meilleure offre</div>
        <div class="mb-8">
          <span class="font-label text-xs font-bold uppercase tracking-widest text-primary block mb-3">Pro</span>
          <h3 class="text-3xl font-headline mb-2">Gold</h3>
          <div class="flex items-baseline gap-1 mb-2"><span class="text-4xl font-headline font-bold text-primary">14€</span><span class="text-on-surface-variant font-body">/mois</span></div>
          <p class="text-on-surface-variant font-body text-sm">L'outil de croissance ultime</p>
        </div>
        <div class="flex-grow space-y-3 mb-8">
          <?php foreach ([
              'Réalisations illimitées (10 photos chacune)',
              'Badge "Vérifié Pro" (qualifs + Décennale)',
              'Visibilité PRIORITAIRE (Top 5 garanti)',
              'Présence dans "Artisans recommandés"',
              'Statistiques détaillées (graphiques + tableaux)',
              'Articles blog illimités',
              'Thème personnalisé (7 ambiances + couleur custom)',
              'Système de RDV avancé (créneaux multiples)',
              'Support prioritaire par email',
              'Notifications email + cloche',
          ] as $idv_f) : ?>
            <div class="flex items-start gap-3">
              <i class="fa-solid fa-circle-check text-primary flex-shrink-0" style="font-size:16px;margin-top:3px;font-weight:900;" aria-hidden="true"></i>
              <span class="font-body text-sm font-semibold"><?php echo esc_html($idv_f); ?></span>
            </div>
          <?php endforeach; ?>
          <?php foreach ([
              'Synchronisation Google Calendar (bientôt)',
              'Notifications SMS (bientôt)',
          ] as $idv_f) : ?>
            <div class="flex items-start gap-3 opacity-40">
              <i class="fa-solid fa-clock flex-shrink-0" style="font-size:16px;margin-top:3px;" aria-hidden="true"></i>
              <span class="font-body text-sm italic"><?php echo esc_html($idv_f); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
        <button type="button" data-plan-checkout="gold"
                class="w-full py-4 bg-primary text-white font-bold text-center rounded-lg hover:opacity-90 transition-all block cursor-pointer">
          Choisir Gold
        </button>
      </div>
    </div>

    <!-- Lien niveaux de confiance -->
    <div class="text-center mt-12">
      <a href="<?php echo esc_url(home_url('/nos-niveaux-de-confiance/')); ?>" class="inline-flex items-center gap-2 text-primary font-bold text-sm hover:underline">
        <span class="material-symbols-outlined" style="font-size:18px;">verified</span>
        En savoir plus sur nos 3 niveaux de confiance →
      </a>
    </div>

    <!-- ── Encart tarifs attractifs ── -->
    <div class="mt-16 max-w-3xl mx-auto bg-gradient-to-br from-primary/5 to-emerald-50 p-10 rounded-3xl border border-primary/15">
      <h3 class="font-headline text-2xl italic mb-4 text-primary">Pourquoi nos tarifs sont-ils si attractifs ?</h3>
      <p class="text-on-surface-variant mb-6 leading-relaxed">
        Contrairement à d'autres plateformes qui facturent 50€, 100€ ou même 200€/mois,
        nous avons choisi des tarifs accessibles pour permettre à <strong>tous les artisans</strong>
        de développer leur activité.
      </p>
      <ul class="space-y-2 text-sm">
        <?php foreach ([
            'Pas de système de leads coûteux',
            'Pas d\'achat de contacts à l\'unité',
            'Un abonnement simple et clair',
            'Toutes les fonctionnalités incluses',
            'Sans engagement, annulable à tout moment',
        ] as $idv_arg) : ?>
          <li class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary" style="font-size:18px;">check</span>
            <span><?php echo esc_html($idv_arg); ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <!-- Bento features -->
  <section class="max-w-7xl mx-auto px-8 mb-32">
    <style>
      @media (max-width: 767px) {
        #idv-tarifs-grid { display: block !important; width: 100% !important; max-width: 100% !important; }
        #idv-tarifs-grid > * { display: block !important; width: 100% !important; max-width: 100% !important; box-sizing: border-box; margin-bottom: 1rem; min-width: 0 !important; }
      }
    </style>
    <div id="idv-tarifs-grid" class="grid grid-cols-1 md:grid-cols-4 gap-4" style="min-height:400px">
      <div class="md:col-span-2 bg-surface-container p-12 flex flex-col justify-end rounded-xl relative overflow-hidden">
        <div class="relative z-10">
          <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="text-primary mb-5">
            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <h3 class="text-2xl font-headline mb-3">Analytique de Précision</h3>
          <p class="font-body text-on-surface-variant text-sm">Visualisez vos performances avec des rapports détaillés sur la conversion de vos leads.</p>
        </div>
      </div>
      <div class="md:col-span-2 bg-primary text-white p-8 flex flex-col justify-center rounded-xl">
        <h3 class="text-2xl font-headline mb-4 italic">Le Badge de Confiance</h3>
        <p class="font-body text-white/80 mb-5 text-sm">Les artisans arborant notre badge "Vérifié" voient leur taux de conversion augmenter de 42% en moyenne.</p>
        <a href="<?php echo esc_url(home_url('/inscription/?type=artisan')); ?>" class="inline-flex items-center gap-2 font-label text-sm font-bold uppercase tracking-widest hover:gap-4 transition-all">En savoir plus →</a>
      </div>
      <div class="bg-surface-container-highest p-8 flex flex-col justify-center rounded-xl">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" class="text-primary mb-4">
          <path d="M18 20V10M12 20V4M6 20v-6" />
        </svg>
        <h4 class="font-headline text-lg mb-2">Conciergerie Pro</h4>
        <p class="font-body text-xs text-on-surface-variant">Un support humain, réactif et expert pour vous accompagner.</p>
      </div>
      <div class="bg-secondary-container p-8 flex flex-col justify-center rounded-xl">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" class="text-primary mb-4">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
          <path d="M7 11V7a5 5 0 0110 0v4" />
        </svg>
        <h4 class="font-headline text-lg mb-2">Paiements Sécurisés</h4>
        <p class="font-body text-xs text-on-surface-variant">Une infrastructure de paiement robuste pour votre sérénité.</p>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="max-w-3xl mx-auto px-8 mb-24">
    <h2 class="text-3xl font-headline text-center mb-12">Questions fréquentes</h2>
    <div class="space-y-8">
      <?php foreach ([
          ['Comment sont qualifiés les leads ?', 'Chaque demande passe par un processus de vérification en trois étapes : identité, nature du projet et délai de réalisation.'],
          ['Puis-je changer de forfait à tout moment ?', 'Absolument. Votre abonnement est flexible et peut être ajusté selon la saisonnalité de votre activité.'],
          ["Qu'est-ce que la visibilité prioritaire ?", "C'est la garantie que votre profil sera affiché dans les trois premiers résultats de recherche pour votre zone géographique."],
      ] as $idv_faq) : ?>
        <div class="border-b border-outline-variant/20 pb-6">
          <h4 class="font-headline text-xl mb-2"><?php echo esc_html($idv_faq[0]); ?></h4>
          <p class="font-body text-on-surface-variant text-sm"><?php echo esc_html($idv_faq[1]); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

</div>

<!-- Checkout Stripe via les boutons Choisir Silver/Gold -->
<script>
  (function() {
    const IS_LOGGED = <?php echo $idv_logged ? 'true' : 'false'; ?>;
    const USER_ROLE = '<?php echo esc_js($idv_role); ?>';
    const INSCRIPTION_URL = '<?php echo esc_url(home_url('/inscription/')); ?>';
    const CHECKOUT_URL = '<?php echo esc_url(admin_url('admin-post.php')); ?>';
    const NONCE = '<?php echo esc_js(wp_create_nonce('idc_stripe_checkout')); ?>';

    document.querySelectorAll('[data-plan-checkout]').forEach(btn => {
      btn.addEventListener('click', () => {
        const plan = btn.getAttribute('data-plan-checkout');

        // 1. Pas connecté → inscription artisan avec le plan en mémoire.
        if (!IS_LOGGED) {
          window.location.href = INSCRIPTION_URL + '?type=artisan&plan=' + encodeURIComponent(plan);
          return;
        }
        // 2. Connecté mais pas artisan → proposer la création d'un compte Pro.
        if (USER_ROLE !== 'artisan') {
          if (confirm('Vous êtes connecté en tant que client. Voulez-vous créer un compte Pro pour souscrire ?')) {
            window.location.href = INSCRIPTION_URL + '?type=artisan&plan=' + encodeURIComponent(plan);
          }
          return;
        }
        // 3. Artisan connecté → POST vers le checkout (redirige vers Stripe).
        btn.disabled = true;
        btn.innerHTML = 'Redirection vers Stripe…';
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = CHECKOUT_URL;
        form.innerHTML = '<input type="hidden" name="action" value="idc_stripe_checkout">'
          + '<input type="hidden" name="idc_plan" value="' + plan + '">'
          + '<input type="hidden" name="idc_stripe_nonce" value="' + NONCE + '">';
        document.body.appendChild(form);
        form.submit();
      });
    });
  })();
</script>

<?php get_footer(); ?>
