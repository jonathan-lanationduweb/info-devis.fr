<?php
/**
 * Template Name: Espace client — Signature de devis
 * Reproduction de views/client/signature.php : canvas de signature manuscrite,
 * carte détails du devis, envoi AJAX (idc_devis_sign) → empreinte SHA-256.
 * Devis ciblé via ?d={demande_id}.
 */

$idv_user = idv_require_role('client');

$idv_id  = (int) ($_GET['d'] ?? 0);
$idv_can = function_exists('idc_signature_can_sign') && idc_signature_can_sign($idv_id, $idv_user);
$idv_sig = ($idv_id && function_exists('idc_signature_get')) ? idc_signature_get($idv_id) : null;

get_header();
get_template_part('template-parts/sidebar', 'client', ['user' => $idv_user]);
?>

<div class="md:ml-72 min-h-screen">
<?php if (!$idv_id || get_post_type($idv_id) !== 'demande_devis'
    || ((int) get_post_meta($idv_id, '_idc_client_user_id', true) !== (int) $idv_user->ID
        && strtolower((string) get_post_meta($idv_id, '_idc_contact_email', true)) !== strtolower($idv_user->user_email))) : ?>
  <div class="pt-32 pb-24 px-8 text-center">
    <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">description</span>
    <h1 class="font-headline text-3xl mb-4">Devis introuvable</h1>
    <a href="<?php echo esc_url(home_url('/dashboard/client/devis/')); ?>" class="text-primary font-bold">Retour à mes projets</a>
  </div>

<?php elseif ($idv_sig) : ?>
  <div class="pt-32 pb-24 px-8 max-w-2xl mx-auto text-center">
    <span class="material-symbols-outlined text-6xl text-primary mb-6 block">verified</span>
    <h1 class="font-headline text-4xl mb-4">Devis déjà signé</h1>
    <p class="text-on-surface-variant mb-2">Ce devis a été signé le <?php echo esc_html(date_i18n('d/m/Y à H:i', strtotime($idv_sig->created_at))); ?>.</p>
    <p class="text-xs text-on-surface-variant/70 font-mono break-all mb-8">Empreinte : <?php echo esc_html($idv_sig->document_hash); ?></p>
    <a href="<?php echo esc_url(home_url('/dashboard/client/devis/')); ?>" class="inline-block bg-primary text-on-primary px-8 py-4 rounded-lg font-label font-bold uppercase tracking-widest text-xs">Retour à mes projets</a>
  </div>

<?php elseif (!$idv_can) : ?>
  <div class="pt-32 pb-24 px-8 max-w-2xl mx-auto text-center">
    <span class="material-symbols-outlined text-6xl text-outline-variant mb-6 block">hourglass_empty</span>
    <h1 class="font-headline text-3xl mb-4">Devis pas encore signable</h1>
    <p class="text-on-surface-variant mb-8">Vous pourrez signer ce devis une fois qu'un artisan aura accepté votre demande.</p>
    <a href="<?php echo esc_url(home_url('/dashboard/client/devis/')); ?>" class="text-primary font-bold">Retour à mes projets</a>
  </div>

<?php else :
    $idv_ref   = (string) get_post_meta($idv_id, '_idc_reference', true);
    $idv_ville = (string) get_post_meta($idv_id, '_idc_ville', true);
    $idv_nonce = wp_create_nonce('idc_devis_sign');
?>
<style>#sig-canvas { cursor: crosshair; touch-action: none; }</style>

<main class="pt-32 pb-20 px-4 md:px-12 max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12">

  <div class="lg:col-span-5 space-y-10">
    <header>
      <span class="text-sm font-label uppercase tracking-[0.2em] text-primary mb-4 block">Validation de document</span>
      <h1 class="text-5xl md:text-6xl font-headline font-medium text-on-surface leading-tight">
        Signer le <span class="italic text-primary">devis</span>
      </h1>
      <p class="mt-6 text-on-surface-variant text-lg leading-relaxed max-w-md">
        En apposant votre signature, vous validez l'acceptation des termes techniques et financiers décrits dans ce document.
      </p>
    </header>

    <div class="bg-surface-container-low p-8 rounded-xl border border-outline-variant/10 shadow-sm">
      <div class="flex justify-between items-start mb-8">
        <div>
          <h2 class="text-2xl font-headline font-semibold text-primary"><?php echo esc_html(get_the_title($idv_id)); ?></h2>
        </div>
        <span class="bg-primary/5 text-primary px-3 py-1 text-xs font-bold uppercase rounded-full">En attente</span>
      </div>
      <div class="space-y-4">
        <div class="flex justify-between items-end border-b border-outline-variant/20 pb-2">
          <span class="text-sm text-on-surface-variant">Référence</span>
          <span class="font-mono text-sm font-semibold"><?php echo esc_html($idv_ref); ?></span>
        </div>
        <div class="flex justify-between items-end border-b border-outline-variant/20 pb-2">
          <span class="text-sm text-on-surface-variant">Ville</span>
          <span class="text-sm font-medium"><?php echo esc_html($idv_ville); ?></span>
        </div>
        <div class="flex justify-between items-end border-b border-outline-variant/20 pb-2">
          <span class="text-sm text-on-surface-variant">Date de demande</span>
          <span class="text-sm font-medium"><?php echo esc_html(get_the_date('d/m/Y', $idv_id)); ?></span>
        </div>
      </div>
      <div class="mt-8 flex items-center gap-3 text-sm text-primary italic">
        <span class="material-symbols-outlined text-lg">verified_user</span>
        <span>Signature numérique sécurisée InfoDevis</span>
      </div>
    </div>
  </div>

  <div class="lg:col-span-7">
    <div class="bg-surface-container-lowest p-8 md:p-12 rounded-xl border border-outline-variant/20 shadow-2xl shadow-on-surface/5">
      <div class="mb-10">
        <h3 class="text-xl font-headline font-semibold mb-2">Espace de signature manuscrite</h3>
        <p class="text-sm text-on-surface-variant">Utilisez votre souris ou votre écran tactile pour signer ci-dessous.</p>
      </div>

      <div class="relative w-full rounded-lg overflow-hidden border-2 border-on-surface" style="aspect-ratio:16/9">
        <canvas id="sig-canvas" class="w-full h-full bg-white block"></canvas>
        <div id="sig-placeholder" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-20">
          <span class="text-sm font-label uppercase tracking-widest">Signer ici</span>
        </div>
        <div class="absolute bottom-4 left-4 flex gap-2">
          <button type="button" onclick="clearSig()" class="bg-surface-container-high hover:bg-surface-container-highest p-2 rounded-lg transition-colors text-on-surface-variant" title="Effacer">
            <span class="material-symbols-outlined">delete</span>
          </button>
        </div>
      </div>

      <form id="sig-form" class="mt-8 space-y-6">
        <input type="hidden" name="demande_id" value="<?php echo (int) $idv_id; ?>">
        <input type="hidden" name="idc_sign_nonce" value="<?php echo esc_attr($idv_nonce); ?>">
        <input type="hidden" name="signature_data" id="sig-data">

        <div class="flex items-start gap-3">
          <input type="checkbox" id="tos" required class="mt-1 rounded border-outline-variant text-primary focus:ring-primary h-4 w-4">
          <label class="text-sm text-on-surface-variant leading-relaxed" for="tos">
            Je confirme avoir lu et accepté les conditions générales de vente ainsi que le descriptif technique annexé à ce devis.
          </label>
        </div>

        <button type="submit"
                class="w-full bg-primary text-on-primary py-5 rounded-lg font-label font-bold uppercase tracking-widest text-sm hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-3 group shadow-xl shadow-primary/10">
          Signer et valider le projet
          <span class="material-symbols-outlined text-xl group-hover:translate-x-1 transition-transform">arrow_forward</span>
        </button>

        <div id="sig-msg" class="hidden text-center text-sm"></div>
      </form>
    </div>
  </div>

</main>

<script>
const canvas = document.getElementById('sig-canvas');
const ctx    = canvas.getContext('2d');
let drawing = false, lastX = 0, lastY = 0, hasSig = false;

function resizeCanvas() { const r = canvas.getBoundingClientRect(); canvas.width = r.width; canvas.height = r.height; }
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

function getPos(e) { const r = canvas.getBoundingClientRect(); const s = e.touches ? e.touches[0] : e; return [s.clientX - r.left, s.clientY - r.top]; }
function stroke(x, y) { ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(x, y); ctx.strokeStyle = '#0f2460'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.stroke(); [lastX, lastY] = [x, y]; hasSig = true; document.getElementById('sig-placeholder').style.display = 'none'; }
canvas.addEventListener('mousedown', e => { drawing = true; [lastX, lastY] = getPos(e); });
canvas.addEventListener('mousemove', e => { if (drawing) { const [x, y] = getPos(e); stroke(x, y); } });
canvas.addEventListener('mouseup', () => drawing = false);
canvas.addEventListener('mouseleave', () => drawing = false);
canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; [lastX, lastY] = getPos(e); }, { passive: false });
canvas.addEventListener('touchmove', e => { e.preventDefault(); if (drawing) { const [x, y] = getPos(e); stroke(x, y); } }, { passive: false });
canvas.addEventListener('touchend', () => drawing = false);

function clearSig() { ctx.clearRect(0, 0, canvas.width, canvas.height); hasSig = false; document.getElementById('sig-placeholder').style.display = 'flex'; }

document.getElementById('sig-form').addEventListener('submit', async function (e) {
  e.preventDefault();
  if (!hasSig) { alert('Veuillez signer le document.'); return; }
  document.getElementById('sig-data').value = canvas.toDataURL('image/png');
  const btn = this.querySelector('button[type=submit]');
  btn.disabled = true; btn.textContent = 'Envoi…';
  const fd = new FormData(this);
  fd.append('action', 'idc_devis_sign');
  try {
    const res = await fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', { method: 'POST', body: fd, credentials: 'same-origin' });
    const data = await res.json();
    const msg = document.getElementById('sig-msg');
    if (data.success) {
      msg.className = 'text-center text-sm text-primary font-semibold';
      msg.textContent = '✅ Devis signé avec succès !';
      msg.classList.remove('hidden');
      setTimeout(() => window.location.href = '<?php echo esc_js(home_url('/dashboard/client/devis/')); ?>', 1800);
    } else {
      msg.className = 'text-center text-sm text-red-700';
      msg.textContent = data.error || 'Erreur';
      msg.classList.remove('hidden');
      btn.disabled = false; btn.textContent = 'Signer et valider le projet';
    }
  } catch (err) {
    btn.disabled = false; btn.textContent = 'Signer et valider le projet';
    alert('Erreur réseau.');
  }
});
</script>
<?php endif; ?>
</div>

<?php get_footer(); ?>
