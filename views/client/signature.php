<?php /* views/client/signature.php */ ?>
<style>
.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 300,'GRAD' 0,'opsz' 24;vertical-align:middle}
#sig-canvas { cursor: crosshair; touch-action: none; }
</style>

<main class="pt-32 pb-20 px-4 md:px-12 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12">

  <!-- Colonne gauche -->
  <div class="lg:col-span-5 space-y-12">
    <header>
      <span class="text-sm font-label uppercase tracking-[0.2em] text-tertiary mb-4 block">Validation de document</span>
      <h1 class="text-5xl md:text-6xl font-headline font-medium text-on-surface leading-tight">
        Signer le <span class="italic text-primary">devis</span>
      </h1>
      <p class="mt-6 text-on-surface-variant text-lg leading-relaxed max-w-md">
        En apposant votre signature, vous validez l'acceptation des termes techniques et financiers décrits dans ce document.
      </p>
    </header>

    <!-- Carte détails devis -->
    <div class="bg-surface-container-low p-8 rounded-xl border border-outline-variant/10 shadow-sm">
      <div class="flex justify-between items-start mb-8">
        <div>
          <h2 class="text-2xl font-headline font-semibold text-primary"><?= Security::e($devis['title'] ?? 'Devis') ?></h2>
          <p class="text-sm font-label text-on-surface-variant">
            Artisan : <?= Security::e($lead['company_name'] ?? 'Artisan') ?>
          </p>
        </div>
        <span class="bg-primary/5 text-primary px-3 py-1 text-xs font-bold tracking-tighter uppercase rounded-full">En attente</span>
      </div>
      <div class="space-y-4">
        <div class="flex justify-between items-end border-b border-outline-variant/20 pb-2">
          <span class="text-sm text-on-surface-variant">Référence</span>
          <span class="font-mono text-sm font-semibold"><?= Security::e($devis['reference'] ?? '') ?></span>
        </div>
        <div class="flex justify-between items-end border-b border-outline-variant/20 pb-2">
          <span class="text-sm text-on-surface-variant">Ville</span>
          <span class="text-sm font-medium"><?= Security::e($devis['ville'] ?? '') ?></span>
        </div>
        <div class="flex justify-between items-end border-b border-outline-variant/20 pb-2">
          <span class="text-sm text-on-surface-variant">Date de demande</span>
          <span class="text-sm font-medium"><?= !empty($devis['created_at']) ? date('d/m/Y', strtotime($devis['created_at'])) : '—' ?></span>
        </div>
      </div>
      <div class="mt-8 flex items-center gap-3 text-sm text-on-tertiary-container italic">
        <span class="material-symbols-outlined text-lg">verified_user</span>
        <span>Signature numérique sécurisée InfoDevis</span>
      </div>
    </div>

    <!-- Citation -->
    <div class="relative py-10 px-8 bg-surface-container-highest rounded-xl overflow-hidden">
      <span class="absolute -top-4 -left-2 text-9xl font-headline opacity-5 text-primary select-none">"</span>
      <p class="relative z-10 text-on-tertiary-fixed font-headline italic text-xl leading-relaxed">
        La signature numérique de votre devis déclenche immédiatement la réservation des matériaux et le planning de nos artisans partenaires.
      </p>
      <p class="mt-4 text-xs font-label uppercase tracking-widest text-on-surface-variant">— L'Équipe InfoDevis</p>
    </div>
  </div>

  <!-- Colonne droite : canvas signature -->
  <div class="lg:col-span-7">
    <div class="bg-surface-container-lowest p-8 md:p-12 rounded-xl border border-outline-variant/20 shadow-2xl shadow-on-surface/5">
      <div class="mb-10">
        <h3 class="text-xl font-headline font-semibold mb-2">Espace de signature manuscrite</h3>
        <p class="text-sm text-on-surface-variant">Utilisez votre souris ou votre écran tactile pour signer ci-dessous.</p>
      </div>

      <!-- Canvas -->
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
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="devis_id" value="<?= $devis['id'] ?? '' ?>">
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
// Canvas signature
const canvas = document.getElementById('sig-canvas');
const ctx    = canvas.getContext('2d');
let drawing  = false;
let lastX = 0, lastY = 0;
let hasSig   = false;

function resizeCanvas() {
  const rect = canvas.getBoundingClientRect();
  canvas.width  = rect.width;
  canvas.height = rect.height;
}
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

function getPos(e) {
  const r = canvas.getBoundingClientRect();
  const src = e.touches ? e.touches[0] : e;
  return [src.clientX - r.left, src.clientY - r.top];
}
canvas.addEventListener('mousedown',  e => { drawing=true; [lastX,lastY]=getPos(e); });
canvas.addEventListener('mousemove',  e => {
  if (!drawing) return;
  const [x,y] = getPos(e);
  ctx.beginPath(); ctx.moveTo(lastX,lastY); ctx.lineTo(x,y);
  ctx.strokeStyle='#0f2460'; ctx.lineWidth=2; ctx.lineCap='round'; ctx.stroke();
  [lastX,lastY]=[x,y]; hasSig=true;
  document.getElementById('sig-placeholder').style.display='none';
});
canvas.addEventListener('mouseup',    ()=>drawing=false);
canvas.addEventListener('mouseleave', ()=>drawing=false);
canvas.addEventListener('touchstart', e=>{ e.preventDefault(); drawing=true; [lastX,lastY]=getPos(e); },{passive:false});
canvas.addEventListener('touchmove',  e=>{ e.preventDefault(); if(!drawing)return; const[x,y]=getPos(e); ctx.beginPath();ctx.moveTo(lastX,lastY);ctx.lineTo(x,y);ctx.strokeStyle='#0f2460';ctx.lineWidth=2;ctx.lineCap='round';ctx.stroke();[lastX,lastY]=[x,y];hasSig=true; },{passive:false});
canvas.addEventListener('touchend',   ()=>drawing=false);

function clearSig() {
  ctx.clearRect(0,0,canvas.width,canvas.height);
  hasSig=false;
  document.getElementById('sig-placeholder').style.display='flex';
}

document.getElementById('sig-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  if (!hasSig) { alert('Veuillez signer le document.'); return; }
  document.getElementById('sig-data').value = canvas.toDataURL();
  const btn = this.querySelector('button[type=submit]');
  btn.disabled=true; btn.textContent='Envoi...';
  try {
    const res  = await fetch('<?= APP_URL ?>/dashboard/client/signature', { method:'POST', body:new FormData(this) });
    const data = await res.json();
    const msg  = document.getElementById('sig-msg');
    if (data.success) {
      msg.className='text-center text-sm text-green-700 font-semibold';
      msg.textContent='✅ Devis signé avec succès ! Hash: '+data.hash;
      msg.classList.remove('hidden');
      setTimeout(()=>window.location.href='<?= APP_URL ?>/dashboard/client/devis', 2000);
    } else {
      msg.className='text-center text-sm text-red-700';
      msg.textContent=data.error||'Erreur';
      msg.classList.remove('hidden');
      btn.disabled=false; btn.textContent='Signer et valider le projet';
    }
  } catch(err) {
    btn.disabled=false; btn.textContent='Signer et valider le projet';
    alert('Erreur réseau.');
  }
});
</script>
