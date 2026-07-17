<?php
/**
 * Nos niveaux de confiance — reproduction fidèle de
 * views/pages/nos_niveaux_de_confiance.php : 3 cartes badges (alignées sur
 * les plans Gratuit/Silver/Gold), tableau comparatif des critères, CTA.
 */

get_header();

$idv_niveaux = [
    'referenced'   => ['title' => 'Référencé', 'plan' => 'Gratuit', 'price' => '0 €', 'icon' => 'fa-solid fa-clipboard-check', 'class' => 'idv-badge--referenced'],
    'verified'     => ['title' => 'Vérifié', 'plan' => 'Silver', 'price' => '10 €/mois', 'icon' => 'fa-solid fa-circle-check', 'class' => 'idv-badge--verified'],
    'verified_pro' => ['title' => 'Vérifié Pro', 'plan' => 'Gold', 'price' => '14 €/mois', 'icon' => 'fa-solid fa-medal', 'class' => 'idv-badge--verified-pro'],
];

$idv_criteres = [
    ['SIRET vérifié', [1, 1, 1]],
    ['Compte validé par notre équipe', [1, 1, 1]],
    ['KBIS récent (- 3 mois)', [0, 1, 1]],
    ['Assurance RC Pro', [0, 1, 1]],
    ['Pièce d\'identité du dirigeant', [0, 1, 1]],
    ['Assurance Décennale', [0, 0, 1]],
    ['Qualifications professionnelles', [0, 0, 1]],
    ['Certifications RGE / Qualibat', [0, 0, 1]],
];

$idv_badge = static function (array $n, string $size): string {
    return '<span class="idv-badge ' . esc_attr($n['class']) . ' idv-badge--' . esc_attr($size) . '">'
        . '<i class="' . esc_attr($n['icon']) . ' idv-badge__icon" aria-hidden="true"></i>'
        . '<span class="idv-badge__label">' . esc_html($n['title']) . '</span></span>';
};
?>
<style>
  .niveaux-table { border-collapse: separate; border-spacing: 0; width: 100%; }
  .niveaux-table th, .niveaux-table td {
    padding: 14px 12px; border-bottom: 1px solid #f3f4f6;
    text-align: center; vertical-align: middle;
  }
  .niveaux-table th { background: #f9fafb; }
  .niveaux-table td:first-child, .niveaux-table th:first-child {
    text-align: left; font-weight: 600;
  }
  .niveaux-table tbody tr:hover td { background: #fafafa; }
  .niveau-check {
    display: inline-flex; width: 24px; height: 24px;
    align-items: center; justify-content: center;
    border-radius: 50%; background: #d1fae5; color: #047857;
  }
  .niveau-cross { color: #d1d5db; font-size: 18px; }
</style>

<div class="bg-background pt-32 pb-20">

  <section class="max-w-4xl mx-auto px-8 mb-16 text-center">
    <span class="inline-block px-3 py-1 bg-primary/10 text-primary text-xs font-bold uppercase tracking-widest rounded-full mb-6">
      Niveaux de confiance
    </span>
    <h1 class="text-5xl md:text-6xl mb-6 leading-tight" style="font-family:'Newsreader',serif;font-style:italic;color:#207752;">
      Nos niveaux de confiance
    </h1>
    <p class="text-xl text-on-surface-variant leading-relaxed">
      Comprenez comment nous garantissons la qualité de chaque artisan recommandé sur InfoDevis.
    </p>
  </section>

  <section class="max-w-6xl mx-auto px-8 mb-20">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach ($idv_niveaux as $idv_n) : ?>
        <div class="bg-white p-8 rounded-3xl border border-outline-variant/10 text-center hover:shadow-xl transition-all">
          <div class="mb-6 flex justify-center"><?php echo $idv_badge($idv_n, 'lg'); ?></div>
          <h3 class="text-2xl mb-2" style="font-family:'Newsreader',serif;"><?php echo esc_html($idv_n['title']); ?></h3>
          <p class="text-xs uppercase tracking-widest text-on-surface-variant font-bold mb-3"><?php echo esc_html($idv_n['plan']); ?></p>
          <p class="text-3xl font-bold text-primary" style="font-family:'Newsreader',serif;font-style:italic;">
            <?php echo esc_html($idv_n['price']); ?>
          </p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="max-w-6xl mx-auto px-8 mb-20">
    <h2 class="text-3xl md:text-4xl mb-8 text-center" style="font-family:'Newsreader',serif;">
      Tableau <span style="font-style:italic;color:#207752;">comparatif</span>
    </h2>

    <div class="bg-white rounded-2xl border border-outline-variant/10 overflow-x-auto">
      <table class="niveaux-table">
        <thead>
          <tr>
            <th class="font-bold text-sm uppercase tracking-widest text-on-surface-variant">Critère</th>
            <?php foreach ($idv_niveaux as $idv_n) : ?>
              <th>
                <div class="flex flex-col items-center gap-2">
                  <?php echo $idv_badge($idv_n, 'md'); ?>
                  <span class="text-[10px] uppercase tracking-wider text-on-surface-variant"><?php echo esc_html($idv_n['plan']); ?></span>
                </div>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($idv_criteres as [$idv_label, $idv_checks]) : ?>
            <tr>
              <td><?php echo esc_html($idv_label); ?></td>
              <?php foreach ($idv_checks as $idv_ok) : ?>
                <td>
                  <?php if ($idv_ok) : ?>
                    <span class="niveau-check"><i class="fa-solid fa-check" style="font-size:11px;" aria-hidden="true"></i></span>
                  <?php else : ?>
                    <span class="niveau-cross">—</span>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <p class="text-xs text-on-surface-variant italic text-center mt-6 max-w-2xl mx-auto">
      ⚠ Si un artisan souscrit à un plan supérieur mais n'a pas fourni les documents requis,
      il garde son badge actuel jusqu'à validation complète de son dossier.
    </p>
  </section>

  <section class="max-w-3xl mx-auto px-8">
    <div class="bg-primary text-on-primary rounded-3xl p-12 text-center">
      <h2 class="text-3xl md:text-4xl mb-4" style="font-family:'Newsreader',serif;font-style:italic;">
        Vous êtes artisan ?
      </h2>
      <p class="text-on-primary/80 mb-8 max-w-xl mx-auto">
        Inscrivez-vous gratuitement, faites valider votre dossier et obtenez votre badge dès aujourd'hui.
      </p>
      <div class="flex flex-wrap gap-3 justify-center">
        <a href="<?php echo esc_url(home_url('/inscription/?type=artisan')); ?>"
           class="bg-white text-primary px-10 py-4 rounded-xl font-bold text-sm tracking-widest uppercase hover:bg-on-primary/10 transition-all no-underline">
          Devenir artisan InfoDevis
        </a>
        <a href="<?php echo esc_url(home_url('/tarifs-pro/')); ?>"
           class="border-2 border-white text-white px-10 py-4 rounded-xl font-bold text-sm tracking-widest uppercase hover:bg-white hover:text-primary transition-all no-underline">
          Voir les tarifs
        </a>
      </div>
    </div>
  </section>

</div>

<?php get_footer(); ?>
