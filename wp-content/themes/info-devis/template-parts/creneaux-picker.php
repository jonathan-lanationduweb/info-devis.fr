<?php
/**
 * Sélecteur de créneaux — port 1:1 de views/partials/creneaux_picker.php :
 * grille 4 jours × N créneaux + navigation semaine (AJAX idc_creneaux).
 * $args : fiche_id, week_start (Y-m-d lundi), slots ['Y-m-d' => ['H:i']], today.
 */

$idv_fiche  = (int) $args['fiche_id'];
$idv_slots  = $args['slots'];
$idv_today  = $args['today'];

$idv_start = new DateTimeImmutable($args['week_start'], wp_timezone());
$idv_end   = $idv_start->modify('+6 days');

$idv_jour_fr = ['lun', 'mar', 'mer', 'jeu', 'ven', 'sam', 'dim'];
$idv_mois_fr = ['', 'janv.', 'févr.', 'mars', 'avril', 'mai', 'juin', 'juill.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

$idv_prev = $idv_start->modify('-7 days')->format('Y-m-d');
$idv_next = $idv_start->modify('+7 days')->format('Y-m-d');
$idv_lbl  = 'Semaine du ' . $idv_start->format('j') . ' ' . $idv_mois_fr[(int) $idv_start->format('n')]
          . ' au ' . $idv_end->format('j') . ' ' . $idv_mois_fr[(int) $idv_end->format('n')];

$idv_today_str = $idv_today->format('Y-m-d');
?>

<div class="creneaux-picker" data-artisan="<?php echo (int) $idv_fiche; ?>" data-week="<?php echo esc_attr($idv_start->format('Y-m-d')); ?>">

  <!-- Navigation semaine -->
  <div class="creneaux-picker__nav">
    <button type="button" class="creneaux-picker__nav-btn" data-week-prev="<?php echo esc_attr($idv_prev); ?>"
            <?php echo $idv_start <= $idv_today ? 'disabled' : ''; ?>>
      ‹ Semaine précédente
    </button>
    <span class="creneaux-picker__week-label"><?php echo esc_html($idv_lbl); ?></span>
    <button type="button" class="creneaux-picker__nav-btn" data-week-next="<?php echo esc_attr($idv_next); ?>">
      Semaine suivante ›
    </button>
  </div>

  <!-- Grille créneaux : 4 jours × créneaux -->
  <div class="creneaux-picker__grid" id="creneaux-grid">
    <?php
    for ($idv_i = 0; $idv_i < 4; $idv_i++) :
        $idv_day       = $idv_start->modify('+' . $idv_i . ' days');
        $idv_day_iso   = $idv_day->format('Y-m-d');
        $idv_day_slots = $idv_slots[$idv_day_iso] ?? [];
        $idv_is_today  = ($idv_day_iso === $idv_today_str);
    ?>
      <div class="creneaux-picker__day-col">
        <div class="creneaux-picker__day-head <?php echo $idv_is_today ? 'creneaux-picker__day-head--today' : ''; ?>">
          <div class="creneaux-picker__day-name"><?php echo esc_html($idv_jour_fr[$idv_i]); ?></div>
          <div class="creneaux-picker__day-date"><?php echo esc_html($idv_day->format('j') . ' ' . $idv_mois_fr[(int) $idv_day->format('n')]); ?></div>
        </div>

        <?php if (empty($idv_day_slots)) : ?>
          <div class="creneaux-picker__day-empty">Aucun créneau</div>
        <?php else : ?>
          <?php foreach ($idv_day_slots as $idv_slot) : ?>
            <button type="button"
                    class="creneaux-picker__slot"
                    data-slot-date="<?php echo esc_attr($idv_day_iso); ?>"
                    data-slot-time="<?php echo esc_attr($idv_slot); ?>">
              <?php echo esc_html($idv_slot); ?>
            </button>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endfor; ?>
  </div>
</div>
