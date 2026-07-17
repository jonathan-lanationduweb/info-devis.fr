<?php
/**
 * Template Name: Prendre rendez-vous
 * Reproduction fidèle de views/pages/rdv_prendre.php + partials/creneaux_picker.php :
 * breadcrumb, sidebar « Artisan choisi » (+ projet lié), sélecteur de créneaux
 * 4 jours avec navigation semaine (AJAX idc_creneaux), formulaire détails
 * (adresse, précisions, photos max 5), bandeau récapitulatif vert, envoi AJAX
 * (idc_rdv_creer, recalcul serveur du créneau + anti-spam 3/7j + photos).
 * NB : paramètre `?pro=` (et non `?artisan=`, query var réservé WordPress).
 */

if (!is_user_logged_in()) {
    wp_safe_redirect(add_query_arg('redirect_to', rawurlencode($_SERVER['REQUEST_URI'] ?? '/'), home_url('/connexion/')));
    exit;
}

$idv_fiche_id = (int) ($_GET['pro'] ?? 0);
$idv_ok       = $idv_fiche_id && get_post_type($idv_fiche_id) === 'artisan' && get_post_status($idv_fiche_id) === 'publish';

if ($idv_ok) {
    $idv_user       = wp_get_current_user();
    $idv_owner_id   = (int) get_post_meta($idv_fiche_id, '_idc_user_id', true);
    $idv_first      = $idv_owner_id ? get_user_meta($idv_owner_id, 'first_name', true) : '';
    $idv_last       = $idv_owner_id ? get_user_meta($idv_owner_id, 'last_name', true) : '';
    $idv_display    = get_the_title($idv_fiche_id) ?: trim($idv_first . ' ' . $idv_last) ?: 'Artisan';
    $idv_initials   = mb_strtoupper(mb_substr($idv_first ?: 'A', 0, 1) . mb_substr($idv_last, 0, 1));
    if ('' === trim($idv_initials)) {
        $idv_initials = mb_strtoupper(mb_substr($idv_display, 0, 2));
    }
    $idv_rating = (float) get_post_meta($idv_fiche_id, '_idc_rating_avg', true);
    $idv_types  = (string) get_post_meta($idv_fiche_id, '_idc_types_projets', true);
    $idv_spec   = '' !== trim($idv_types) ? trim(explode(',', $idv_types)[0]) : 'Artisan qualifié';

    // Anti-spam : max 3 demandes / 7j / artisan (avertissement, comme l'original).
    $idv_recent = idc_rdv_demandes_recentes((int) $idv_user->ID, $idv_fiche_id);

    // Devis lié (optionnel).
    $idv_devis_lie = null;
    $idv_devis_id  = (int) ($_GET['devis_id'] ?? 0);
    if ($idv_devis_id && get_post_type($idv_devis_id) === 'demande_devis'
        && (int) get_post_field('post_author', $idv_devis_id) === (int) $idv_user->ID) {
        $idv_devis_lie = get_post($idv_devis_id);
    }

    // Créneaux de la semaine demandée (lundi de la semaine, ?week= optionnel).
    $idv_tz = wp_timezone();
    try {
        $idv_week_raw   = sanitize_text_field(wp_unslash($_GET['week'] ?? ''));
        $idv_week_start = preg_match('/^\d{4}-\d{2}-\d{2}$/', $idv_week_raw)
            ? (new DateTimeImmutable($idv_week_raw, $idv_tz))->modify('monday this week')
            : (new DateTimeImmutable('today', $idv_tz))->modify('monday this week');
    } catch (Exception $e) {
        $idv_week_start = (new DateTimeImmutable('today', $idv_tz))->modify('monday this week');
    }
    $idv_slots = idc_rdv_slots_semaine($idv_fiche_id, $idv_week_start->format('Y-m-d'));
    $idv_today = new DateTimeImmutable('today', $idv_tz);
}

get_header();
?>

<?php if (!$idv_ok) : ?>
  <div class="pt-32 pb-24 px-8 max-w-3xl mx-auto min-h-screen text-center">
    <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">event_busy</span>
    <h1 class="font-headline text-3xl mb-4">Artisan introuvable</h1>
    <a href="<?php echo esc_url(home_url('/professionnels/')); ?>" class="text-primary font-bold">Retour à l'annuaire</a>
  </div>
<?php else : ?>

<main class="rdv-prendre">

  <!-- Breadcrumb -->
  <nav class="rdv-prendre__breadcrumb">
    <a href="<?php echo esc_url(home_url('/dashboard/client/')); ?>">Tableau de bord</a>
    <span class="rdv-prendre__breadcrumb-sep">›</span>
    <a href="<?php echo esc_url(home_url('/dashboard/client/devis/')); ?>">Mes devis</a>
    <span class="rdv-prendre__breadcrumb-sep">›</span>
    <span>Prendre RDV</span>
  </nav>

  <h1 class="rdv-prendre__title">Prendre rendez-vous</h1>
  <p class="rdv-prendre__subtitle">Visite technique gratuite · ~1h30 sur place</p>

  <?php if ($idv_recent >= 3) : ?>
    <div class="rdv-msg rdv-msg--err">
      ⚠ Vous avez déjà soumis <?php echo (int) $idv_recent; ?> demandes à <strong><?php echo esc_html($idv_display); ?></strong> ces 7 derniers jours.
      Patientez ou contactez-le directement.
    </div>
  <?php endif; ?>

  <form id="rdv-prendre-form" class="rdv-prendre__grid" enctype="multipart/form-data">
    <input type="hidden" name="action" value="idc_rdv_creer">
    <input type="hidden" name="idc_rdv_nonce" value="<?php echo esc_attr(wp_create_nonce('idc_rdv_create')); ?>">
    <input type="hidden" name="artisan" value="<?php echo (int) $idv_fiche_id; ?>">
    <input type="hidden" name="date_rdv" id="rdv-date-input" value="">
    <?php if ($idv_devis_lie) : ?>
      <input type="hidden" name="devis_id" value="<?php echo (int) $idv_devis_lie->ID; ?>">
    <?php endif; ?>

    <!-- Sidebar gauche : Artisan + Projet -->
    <aside class="rdv-prendre__sidebar">

      <div class="rdv-card">
        <div class="rdv-card__label">Artisan choisi</div>
        <div class="rdv-card__artisan">
          <div class="rdv-card__avatar"><?php echo esc_html($idv_initials); ?></div>
          <div>
            <div class="rdv-card__name"><?php echo esc_html($idv_display); ?></div>
            <?php if ($idv_rating > 0) : ?>
              <div class="rdv-card__rating">★ <?php echo number_format($idv_rating, 1); ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="rdv-card__speciality"><?php echo esc_html($idv_spec); ?></div>
      </div>

      <?php if ($idv_devis_lie) : ?>
        <div class="rdv-card">
          <div class="rdv-card__label">Projet lié</div>
          <div class="rdv-card__projet-title"><?php echo esc_html(get_the_title($idv_devis_lie) ?: 'Devis #' . $idv_devis_lie->ID); ?></div>
          <div class="rdv-card__projet-ref">Réf. <?php echo esc_html(get_post_meta($idv_devis_lie->ID, '_idc_reference', true) ?: '—'); ?></div>
        </div>
      <?php endif; ?>

    </aside>

    <!-- Colonne centrale -->
    <div>

      <!-- Section 1 : Créneau -->
      <section class="rdv-section">
        <h2 class="rdv-section__title">1 · Choisissez un créneau</h2>
        <div id="creneaux-container">
          <?php
          get_template_part('template-parts/creneaux-picker', null, [
              'fiche_id'   => $idv_fiche_id,
              'week_start' => $idv_week_start->format('Y-m-d'),
              'slots'      => $idv_slots,
              'today'      => $idv_today,
          ]);
          ?>
        </div>
        <p class="rdv-rassurante" style="text-align:left;margin-top:12px;">
          Créneaux affichés : visites de <strong>~1h30</strong>. Si rien ne convient, naviguez vers la semaine suivante.
        </p>
      </section>

      <!-- Section 2 : Détails -->
      <section class="rdv-section">
        <h2 class="rdv-section__title">2 · Confirmez les détails</h2>

        <div class="rdv-field">
          <label class="rdv-field__label" for="rdv-adresse">Adresse du chantier *</label>
          <input type="text" id="rdv-adresse" name="adresse" required maxlength="255"
                 class="rdv-field__input"
                 placeholder="N° et rue" value="">
        </div>

        <div style="display:grid;grid-template-columns:120px 1fr;gap:12px;">
          <div class="rdv-field">
            <label class="rdv-field__label" for="rdv-cp">Code postal</label>
            <input type="text" id="rdv-cp" name="code_postal" maxlength="10" class="rdv-field__input">
          </div>
          <div class="rdv-field">
            <label class="rdv-field__label" for="rdv-ville">Ville</label>
            <input type="text" id="rdv-ville" name="ville" maxlength="100" class="rdv-field__input">
          </div>
        </div>

        <div class="rdv-field">
          <label class="rdv-field__label" for="rdv-desc">Précisions techniques</label>
          <textarea id="rdv-desc" name="description" rows="3" maxlength="2000"
                    class="rdv-field__textarea"
                    placeholder="Étage, code d'accès, contraintes particulières…"></textarea>
        </div>

        <div class="rdv-field">
          <label class="rdv-field__label">Photos du chantier (optionnel)</label>
          <label class="rdv-upload" for="rdv-photos">
            <div class="rdv-upload__icon">📷</div>
            <div class="rdv-upload__text"><strong>+ Cliquez ou glissez vos photos</strong> (max 5)</div>
            <div class="rdv-upload__hint">JPG, PNG, WEBP — max 10 Mo / fichier</div>
            <input type="file" id="rdv-photos" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" style="display:none;">
          </label>
          <div id="rdv-photos-preview" class="rdv-photos-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;"></div>
          <p style="font-size:11px;color:var(--rdv-text-muted);margin-top:6px;">
            Vous pourrez ajouter d'autres documents après création (PDF, plans).
          </p>
        </div>

        <label class="rdv-checkbox">
          <input type="checkbox" name="consent_engagement" value="1" required>
          <span>
            Je m'engage à être présent à l'horaire choisi et à prévenir au moins 24h à l'avance en cas d'imprévu.
          </span>
        </label>
      </section>

      <!-- Bandeau récap (vert plein) -->
      <div class="rdv-recap">
        <div>
          <div class="rdv-recap__label">Récapitulatif</div>
          <div class="rdv-recap__date rdv-recap__date--empty" id="rdv-recap-date">
            Sélectionnez un créneau ci-dessus
          </div>
        </div>
        <button type="submit" class="rdv-recap__btn" id="rdv-submit-btn" disabled>
          Confirmer le RDV →
        </button>
      </div>

      <div id="rdv-msg" class="rdv-msg rdv-msg--hidden"></div>

      <p class="rdv-rassurante">
        L'artisan recevra votre demande par email. Vous recevrez une confirmation dans les 24h.
        Aucun paiement n'est demandé à ce stade — la visite technique est <strong>gratuite</strong>.
      </p>

    </div>
  </form>

</main>

<script>
(function() {
  const AJAX_URL = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
  const FICHE_ID = <?php echo (int) $idv_fiche_id; ?>;

  const moisFr = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
  const moisCourt = ['', 'janv.', 'févr.', 'mars', 'avril', 'mai', 'juin', 'juill.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
  const jourFr = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];

  const dateInput   = document.getElementById('rdv-date-input');
  const recapDate   = document.getElementById('rdv-recap-date');
  const submitBtn   = document.getElementById('rdv-submit-btn');
  const msg         = document.getElementById('rdv-msg');
  const container   = document.getElementById('creneaux-container');

  // ── Sélection d'un créneau ──
  function bindSlotClicks() {
    container.querySelectorAll('.creneaux-picker__slot').forEach(btn => {
      btn.addEventListener('click', () => {
        container.querySelectorAll('.creneaux-picker__slot--selected')
          .forEach(b => b.classList.remove('creneaux-picker__slot--selected'));
        btn.classList.add('creneaux-picker__slot--selected');

        const date = btn.dataset.slotDate;
        const time = btn.dataset.slotTime;
        dateInput.value = date + ' ' + time;

        // Formatte "Vendredi 12 juin · 14:30"
        const d = new Date(date + 'T' + time);
        const label = jourFr[d.getDay()].charAt(0).toUpperCase() + jourFr[d.getDay()].slice(1)
                    + ' ' + d.getDate() + ' ' + moisFr[d.getMonth() + 1]
                    + ' · ' + time;
        recapDate.textContent = label;
        recapDate.classList.remove('rdv-recap__date--empty');
        submitBtn.disabled = false;
      });
    });
  }
  bindSlotClicks();

  // ── Navigation semaine (AJAX) ──
  function bindNavWeek() {
    container.querySelectorAll('[data-week-prev]').forEach(btn => {
      btn.addEventListener('click', (e) => { e.preventDefault(); loadWeek(btn.dataset.weekPrev); });
    });
    container.querySelectorAll('[data-week-next]').forEach(btn => {
      btn.addEventListener('click', (e) => { e.preventDefault(); loadWeek(btn.dataset.weekNext); });
    });
  }
  bindNavWeek();

  async function loadWeek(weekIso) {
    const url = `${AJAX_URL}?action=idc_creneaux&fiche=${FICHE_ID}&week=${encodeURIComponent(weekIso)}`;
    try {
      const res = await fetch(url, { credentials: 'same-origin' });
      const data = await res.json();
      if (!data.success) { console.error(data.error); return; }
      const newGrid = buildGridHtml(weekIso, data.slots);
      const picker = container.querySelector('.creneaux-picker');
      const start = new Date(weekIso + 'T00:00');
      const end   = new Date(start); end.setDate(end.getDate() + 6);
      const lbl   = `Semaine du ${start.getDate()} ${moisCourt[start.getMonth()+1]} au ${end.getDate()} ${moisCourt[end.getMonth()+1]}`;
      picker.querySelector('.creneaux-picker__week-label').textContent = lbl;
      const prev = new Date(start); prev.setDate(prev.getDate() - 7);
      const next = new Date(start); next.setDate(next.getDate() + 7);
      const prevBtn = picker.querySelector('[data-week-prev]');
      const nextBtn = picker.querySelector('[data-week-next]');
      prevBtn.dataset.weekPrev = iso(prev);
      nextBtn.dataset.weekNext = iso(next);
      const today = new Date();
      const monThis = new Date(today);
      monThis.setDate(today.getDate() - ((today.getDay() + 6) % 7));
      prevBtn.disabled = (start <= monThis);

      picker.querySelector('.creneaux-picker__grid').outerHTML = newGrid;
      picker.dataset.week = weekIso;
      bindSlotClicks();
      dateInput.value = '';
      recapDate.textContent = 'Sélectionnez un créneau ci-dessus';
      recapDate.classList.add('rdv-recap__date--empty');
      submitBtn.disabled = true;
    } catch (e) {
      console.error(e);
    }
  }

  function iso(d) {
    return d.getFullYear() + '-'
      + String(d.getMonth() + 1).padStart(2, '0') + '-'
      + String(d.getDate()).padStart(2, '0');
  }

  function buildGridHtml(weekIso, slots) {
    const days = [];
    const start = new Date(weekIso + 'T00:00');
    for (let i = 0; i < 4; i++) {
      const d = new Date(start); d.setDate(start.getDate() + i);
      const dIso = iso(d);
      const daySlots = slots[dIso] || [];
      const dayName = ['lun','mar','mer','jeu','ven','sam','dim'][(d.getDay() + 6) % 7];
      const today = new Date(); today.setHours(0,0,0,0);
      const isToday = (d.getTime() === today.getTime());
      const slotsHtml = daySlots.length
        ? daySlots.map(s =>
            `<button type="button" class="creneaux-picker__slot" data-slot-date="${dIso}" data-slot-time="${s}">${s}</button>`
          ).join('')
        : '<div class="creneaux-picker__day-empty">Aucun créneau</div>';
      days.push(`
        <div class="creneaux-picker__day-col">
          <div class="creneaux-picker__day-head ${isToday ? 'creneaux-picker__day-head--today' : ''}">
            <div class="creneaux-picker__day-name">${dayName}</div>
            <div class="creneaux-picker__day-date">${d.getDate()} ${moisCourt[d.getMonth()+1]}</div>
          </div>
          ${slotsHtml}
        </div>`);
    }
    return `<div class="creneaux-picker__grid" id="creneaux-grid">${days.join('')}</div>`;
  }

  // ── Aperçu des photos sélectionnées + limite à 5 ──
  const photosInput   = document.getElementById('rdv-photos');
  const photosPreview = document.getElementById('rdv-photos-preview');
  if (photosInput && photosPreview) {
    photosInput.addEventListener('change', () => {
      photosPreview.innerHTML = '';
      const files = Array.from(photosInput.files || []).slice(0, 5);
      if (files.length < (photosInput.files || []).length) {
        showMsg('err', 'Maximum 5 photos. Les premières ont été conservées.');
      }
      const dt = new DataTransfer();
      files.forEach(f => dt.items.add(f));
      photosInput.files = dt.files;
      files.forEach((f) => {
        if (f.size > 10 * 1024 * 1024) {
          showMsg('err', `"${f.name}" dépasse 10 Mo et sera ignoré.`);
          return;
        }
        const thumb = document.createElement('div');
        thumb.style.cssText = 'position:relative;width:80px;height:80px;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;';
        const img = document.createElement('img');
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; };
        reader.readAsDataURL(f);
        thumb.appendChild(img);
        photosPreview.appendChild(thumb);
      });
    });
  }

  // ── Submit ──
  document.getElementById('rdv-prendre-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!dateInput.value) {
      showMsg('err', 'Sélectionnez un créneau avant de confirmer.');
      return;
    }
    const form = e.target;
    const fd = new FormData(form);
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="rdv-spinner"></span>Envoi…';
    try {
      const res = await fetch(AJAX_URL, {
        method: 'POST', body: fd, credentials: 'same-origin'
      });
      const data = await res.json();
      if (data.success) {
        showMsg('ok', '✓ Demande envoyée ! Redirection…');
        setTimeout(() => { window.location.href = data.redirect; }, 800);
      } else {
        showMsg('err', data.error || 'Erreur');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Confirmer le RDV →';
      }
    } catch (err) {
      showMsg('err', 'Erreur réseau');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Confirmer le RDV →';
    }
  });

  function showMsg(kind, text) {
    msg.textContent = text;
    msg.className = 'rdv-msg rdv-msg--' + kind;
  }
})();
</script>

<?php endif; ?>

<?php get_footer(); ?>
