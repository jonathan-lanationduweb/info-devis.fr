<?php
/**
 * Template Name: Espace artisan — Abonnement
 * Plan actuel + montée en gamme via Stripe Checkout (action idc_stripe_checkout).
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_plan  = get_user_meta($idv_user->ID, '_idc_plan', true) ?: ($idv_fiche ? (get_post_meta($idv_fiche->ID, '_idc_plan', true) ?: 'gratuit') : 'gratuit');
// Offres et prix identiques à la page Tarifs Pro originale (views/home/tarifs.php).
$idv_plans = [
    'gratuit' => ['Gratuit', '0€ /mois', ['Profil professionnel basique', '1 réalisation maximum (1 photo)', 'Badge « Référencé » (SIRET vérifié)', 'Système de prise de RDV en ligne']],
    'silver'  => ['Silver', '10€ /mois', ['5 réalisations (3 photos chacune)', 'Badge « Vérifié » (après upload documents)', 'Visibilité renforcée (zone milieu)', 'Articles blog (3/mois)', 'Gestion des disponibilités']],
    'gold'    => ['Gold', '14€ /mois', ['Réalisations illimitées (10 photos chacune)', 'Badge « Vérifié Pro » (qualifs + Décennale)', 'Visibilité PRIORITAIRE (Top 5 garanti)', 'Présence dans « Artisans recommandés »', 'Articles blog illimités', 'Thème personnalisé (7 ambiances)']],
];
$idv_stripe_ok = function_exists('idc_stripe_ready') && idc_stripe_ready();
?>

<div class="md:ml-72 pt-28 px-8 pb-24 md:pb-12">

  <div class="mb-12 max-w-5xl">
    <h1 class="text-4xl md:text-5xl font-headline italic text-on-surface mb-4">Mon abonnement</h1>
    <p class="text-on-surface-variant max-w-2xl font-body leading-relaxed">
      Votre plan actuel : <strong class="text-primary"><?php echo esc_html(ucfirst($idv_plan)); ?></strong>.
      Montez en gamme pour recevoir plus de demandes et renforcer votre badge de confiance.
    </p>
  </div>

  <?php if (isset($_GET['abo'])) : ?>
    <div class="mb-8 p-5 border-l-4 <?php echo $_GET['abo'] === 'ok' ? 'border-primary bg-primary/5' : 'border-red-400 bg-red-50 text-red-700'; ?> rounded-r-xl text-sm">
      <?php echo $_GET['abo'] === 'ok' ? '✅ Merci ! Votre abonnement est en cours d’activation (quelques secondes) — vous recevrez un email de confirmation.' : 'Le paiement a été annulé. Vous pouvez réessayer quand vous le souhaitez.'; ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl">
    <?php foreach ($idv_plans as $idv_key => [$idv_label, $idv_prix, $idv_points]) :
        $idv_current = ($idv_key === $idv_plan);
    ?>
      <div class="bg-surface-container-lowest p-8 rounded-2xl border <?php echo $idv_current ? 'border-primary shadow-lg shadow-primary/10' : 'border-outline-variant/10'; ?> flex flex-col">
        <div class="flex items-center justify-between mb-2">
          <h3 class="font-headline text-2xl font-bold"><?php echo esc_html($idv_label); ?></h3>
          <?php if ($idv_current) : ?>
            <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">Actuel</span>
          <?php endif; ?>
        </div>
        <p class="text-3xl font-headline font-bold text-primary mb-6"><?php echo esc_html($idv_prix); ?></p>
        <ul class="space-y-3 mb-8 flex-1">
          <?php foreach ($idv_points as $idv_pt) : ?>
            <li class="flex items-start gap-2 text-sm text-on-surface-variant">
              <span class="material-symbols-outlined text-primary text-base flex-shrink-0">check_circle</span>
              <?php echo esc_html($idv_pt); ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php if ($idv_current) : ?>
          <span class="text-center border border-outline-variant/30 text-on-surface-variant py-3.5 rounded-xl font-bold tracking-widest uppercase text-xs">Votre plan</span>
        <?php elseif ($idv_key !== 'gratuit' && $idv_stripe_ok) : ?>
          <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="idc_stripe_checkout">
            <input type="hidden" name="idc_plan" value="<?php echo esc_attr($idv_key); ?>">
            <?php wp_nonce_field('idc_stripe_checkout', 'idc_stripe_nonce'); ?>
            <button type="submit" class="w-full bg-primary text-on-primary py-3.5 rounded-xl font-bold tracking-widest uppercase text-xs hover:opacity-90 transition-all">
              Passer en <?php echo esc_html($idv_label); ?>
            </button>
          </form>
        <?php elseif ($idv_key === 'gratuit') : ?>
          <span class="text-center text-[11px] text-on-surface-variant italic py-3.5">Pour revenir au plan gratuit, résiliez votre abonnement en cours.</span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <p class="mt-8 text-xs text-on-surface-variant max-w-5xl">
    Paiement sécurisé par Stripe. Abonnement mensuel sans engagement — pour toute résiliation ou question de facturation,
    contactez-nous à <a class="text-primary font-bold" href="mailto:<?php echo esc_attr(idv_contact('email')); ?>"><?php echo esc_html(idv_contact('email')); ?></a>.
  </p>
</div>

<?php get_footer(); ?>
