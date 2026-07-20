<?php
/**
 * Template Name: Espace artisan — Mes articles
 * Reproduction de views/artisan/blog.php : liste des articles de l'artisan avec
 * statut (publié / en attente / brouillon / refusé), quota Silver, lock Gratuit.
 * Articles = posts WP natifs de l'auteur ; création via /dashboard/artisan/blog/nouveau.
 */

$idv_user  = idv_require_role('artisan');
$idv_fiche = idv_artisan_fiche($idv_user);

get_header();
get_template_part('template-parts/sidebar', 'artisan', ['user' => $idv_user, 'fiche' => $idv_fiche]);

$idv_allowed  = function_exists('idc_blog_allowed') && idc_blog_allowed($idv_fiche);
$idv_plan     = function_exists('idc_blog_plan') ? idc_blog_plan($idv_fiche) : 'gratuit';
$idv_quota_max = defined('IDC_BLOG_SILVER_QUOTA') ? IDC_BLOG_SILVER_QUOTA : 3;
$idv_quota_used = ($idv_plan === 'silver') ? idc_blog_quota_used($idv_user->ID) : 0;
$idv_quota_reached = ($idv_plan === 'silver') && $idv_quota_used >= $idv_quota_max;
$idv_articles = $idv_allowed ? idc_blog_articles($idv_user->ID) : [];
$idv_new_url  = home_url('/dashboard/artisan/blog/nouveau/');
?>
<main class="md:ml-72 min-h-screen p-4 sm:p-6 md:p-8 pt-24 md:pt-28 bg-background">
  <div class="max-w-5xl mx-auto">

    <header class="flex items-end justify-between gap-4 mb-8 flex-wrap">
      <div>
        <h1 class="text-4xl md:text-5xl font-bold tracking-tight mb-2" style="font-family:'Newsreader',serif;">
          <i class="fa-solid fa-pen-nib text-primary mr-2"></i> Mes <span class="italic">articles</span>
        </h1>
        <p class="text-on-surface-variant max-w-2xl">Partagez votre expertise et conseils pratiques. Vos articles sont validés par notre équipe avant publication.</p>
      </div>
      <?php if ($idv_allowed) : ?>
        <?php if ($idv_quota_reached) : ?>
          <span class="bg-amber-100 text-amber-800 px-5 py-2.5 rounded-xl text-sm font-bold uppercase tracking-widest inline-flex items-center gap-2 cursor-not-allowed" title="Limite atteinte — passez en Gold pour publier sans limite"><i class="fa-solid fa-lock"></i> Quota atteint</span>
        <?php else : ?>
          <a href="<?php echo esc_url($idv_new_url); ?>" class="bg-primary text-on-primary px-5 py-2.5 rounded-xl text-sm font-bold uppercase tracking-widest hover:opacity-90 inline-flex items-center gap-2"><i class="fa-solid fa-plus"></i> Nouvel article</a>
        <?php endif; ?>
      <?php endif; ?>
    </header>

    <?php if (isset($_GET['saved'])) : ?>
      <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm font-semibold"><i class="fa-solid fa-circle-check mr-1"></i> Article enregistré. Il est en attente de validation par notre équipe.</div>
    <?php endif; ?>
    <?php if (($_GET['err'] ?? '') === 'limit') : ?>
      <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-xl text-sm font-semibold"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Vous avez atteint la limite de <?php echo (int) ($_GET['max'] ?? 3); ?> articles publiés sur les 30 derniers jours (plan Silver). <a href="<?php echo esc_url(home_url('/dashboard/artisan/abonnement/')); ?>" class="underline font-bold ml-1">Passer en Gold</a> pour publier sans limite.</div>
    <?php endif; ?>

    <?php if ($idv_allowed && $idv_plan === 'silver') :
        $pct = $idv_quota_max > 0 ? min(100, (int) round($idv_quota_used * 100 / $idv_quota_max)) : 0;
        $bar = $idv_quota_used >= $idv_quota_max ? 'bg-red-500' : ($idv_quota_used >= $idv_quota_max - 1 ? 'bg-amber-500' : 'bg-primary');
    ?>
      <div class="mb-6 p-4 bg-white border border-outline-variant/10 rounded-xl">
        <div class="flex items-center justify-between mb-2">
          <p class="text-sm font-semibold text-on-surface"><i class="fa-solid fa-chart-column text-primary mr-1"></i> Quota d'articles (plan Silver)</p>
          <p class="text-sm font-bold text-on-surface"><?php echo (int) $idv_quota_used; ?> / <?php echo (int) $idv_quota_max; ?> <span class="text-xs text-on-surface-variant font-normal">sur 30 jours</span></p>
        </div>
        <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden"><div class="<?php echo $bar; ?> h-2 rounded-full transition-all" style="width:<?php echo $pct; ?>%"></div></div>
      </div>
    <?php endif; ?>

    <?php if (!$idv_allowed) : ?>
      <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-8 text-center">
        <i class="fa-solid fa-lock text-amber-600 mb-3" style="font-size:48px"></i>
        <h2 class="font-headline text-2xl mb-3">La création d'articles est réservée aux plans payants</h2>
        <p class="text-on-surface-variant text-sm max-w-md mx-auto mb-6">Avec un abonnement <strong>Silver (10€/mois)</strong> ou <strong>Gold (14€/mois)</strong>, vous pouvez publier vos propres articles sur le blog InfoDevis pour partager votre expertise et gagner en visibilité.</p>
        <ul class="text-sm text-on-surface-variant max-w-md mx-auto mb-6 space-y-2 text-left">
          <li class="flex items-start gap-2"><i class="fa-solid fa-circle-check text-primary mt-1 flex-shrink-0" style="font-size:12px;"></i><span>Améliorez votre référencement (SEO) avec du contenu d'expert</span></li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-circle-check text-primary mt-1 flex-shrink-0" style="font-size:12px;"></i><span>Démontrez votre savoir-faire auprès des futurs clients</span></li>
          <li class="flex items-start gap-2"><i class="fa-solid fa-circle-check text-primary mt-1 flex-shrink-0" style="font-size:12px;"></i><span>Vos articles apparaissent sur votre profil public et le blog InfoDevis</span></li>
        </ul>
        <a href="<?php echo esc_url(home_url('/dashboard/artisan/abonnement/')); ?>" class="inline-flex items-center gap-2 bg-primary text-on-primary px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-widest hover:opacity-90 transition-all"><i class="fa-solid fa-arrow-up"></i> Choisir mon plan</a>
      </div>
    <?php elseif (!$idv_articles) : ?>
      <div class="bg-white p-12 text-center rounded-2xl border border-outline-variant/10">
        <i class="fa-solid fa-pen-nib text-stone-300 mb-4" style="font-size:48px"></i>
        <p class="text-on-surface-variant">Vous n'avez pas encore d'article.</p>
        <a href="<?php echo esc_url($idv_new_url); ?>" class="inline-flex items-center gap-2 bg-primary text-on-primary px-5 py-2.5 rounded-xl text-sm font-bold uppercase tracking-widest hover:opacity-90 mt-4"><i class="fa-solid fa-plus"></i> Créer mon premier article</a>
      </div>
    <?php else : ?>
      <div class="space-y-3">
        <?php foreach ($idv_articles as $a) :
            $note = (string) get_post_meta($a->ID, '_idc_admin_note', true);
            $st = $a->post_status;
            if ($st === 'draft' && $note) { $st = 'rejected'; }
            $map = [
                'publish'  => ['bg-green-100 text-green-800', 'fa-circle-check', 'Publié'],
                'draft'    => ['bg-stone-100 text-stone-700', 'fa-pen-to-square', 'Brouillon'],
                'pending'  => ['bg-amber-100 text-amber-800', 'fa-clock', 'En attente'],
                'rejected' => ['bg-red-100 text-red-800', 'fa-circle-xmark', 'Refusé'],
            ];
            [$cls, $ic, $lbl] = $map[$st] ?? ['bg-stone-100 text-stone-700', 'fa-circle', 'Inconnu'];
        ?>
          <article class="bg-white p-5 rounded-xl border border-outline-variant/10 flex items-center justify-between gap-4 flex-wrap" data-row-post="<?php echo (int) $a->ID; ?>">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-1 flex-wrap">
                <h3 class="font-headline text-lg font-bold line-clamp-1"><?php echo esc_html(get_the_title($a)); ?></h3>
                <span class="text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full inline-flex items-center gap-1 <?php echo $cls; ?>"><i class="fa-solid <?php echo $ic; ?>"></i> <?php echo $lbl; ?></span>
              </div>
              <?php if ($a->post_excerpt) : ?><p class="text-sm text-on-surface-variant line-clamp-1"><?php echo esc_html($a->post_excerpt); ?></p><?php endif; ?>
              <p class="text-xs text-on-surface-variant/70 mt-1">Créé le <?php echo esc_html(wp_date('d/m/Y', strtotime($a->post_date))); ?>
                <?php if ($note) : ?> · <span class="text-red-700 italic">Note admin : <?php echo esc_html($note); ?></span><?php endif; ?>
              </p>
            </div>
            <div class="flex items-center gap-2">
              <?php if ($a->post_status === 'publish') : ?>
                <a href="<?php echo esc_url(get_permalink($a)); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high font-bold" title="Voir l'article publié"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
              <?php endif; ?>
              <a href="<?php echo esc_url(add_query_arg('id', $a->ID, $idv_new_url)); ?>" class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 font-bold" title="Éditer"><i class="fa-solid fa-pen-to-square"></i></a>
              <button type="button" onclick="idvDeletePost(<?php echo (int) $a->ID; ?>)" class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 font-bold" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</main>

<script>
  const IDV_AJAX = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
  const IDV_DEL_NONCE = '<?php echo esc_js(wp_create_nonce('idc_blog_delete')); ?>';
  async function idvDeletePost(id) {
    if (!confirm('Supprimer cet article ?')) return;
    const fd = new FormData();
    fd.append('action', 'idc_blog_delete');
    fd.append('id', id);
    fd.append('idc_blog_nonce', IDV_DEL_NONCE);
    try {
      const res = await fetch(IDV_AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
      const data = await res.json();
      if (data.success) {
        const row = document.querySelector('[data-row-post="' + id + '"]');
        if (row) row.style.opacity = '0.3';
        setTimeout(function () { window.location.reload(); }, 400);
      } else alert(data.error || 'Erreur');
    } catch (e) { alert('Erreur réseau'); }
  }
</script>

<?php get_footer(); ?>
