<?php /* views/artisan/abonnement.php */ ?>
<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24
  }
</style>

<?php include __DIR__ . '/_sidebar.php'; ?>

<main class="ml-64 pt-24 pb-16 min-h-screen bg-surface-container-low">
  <div class="max-w-5xl mx-auto px-6">

    <div class="mb-10">
      <h1 class="font-headline text-4xl text-on-surface mb-2">Mon abonnement</h1>
      <p class="text-on-surface-variant text-sm">Gérez votre plan et accédez à plus de leads qualifiés</p>
    </div>

    <!-- Plan actuel -->
    <?php if (!empty($current)): ?>
      <div class="bg-primary text-on-primary rounded-2xl p-8 mb-10 flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
          <p class="font-label text-xs uppercase tracking-widest opacity-70 mb-1">Plan actuel</p>
          <h2 class="font-headline text-3xl italic font-bold mb-2"><?= ucfirst($current['plan'] ?? 'Gratuit') ?></h2>
          <p class="text-on-primary/80 text-sm">
            Actif depuis le <?= !empty($current['started_at']) ? date('d/m/Y', strtotime($current['started_at'])) : '—' ?>
            <?php if (!empty($current['ends_at'])): ?>
              · Renouvellement le <?= date('d/m/Y', strtotime($current['ends_at'])) ?>
            <?php endif; ?>
          </p>
        </div>
        <div class="flex gap-3">
          <span class="bg-white/20 text-white px-4 py-2 rounded-full font-label text-xs uppercase tracking-widest font-bold">✅ Actif</span>
        </div>
      </div>
    <?php else: ?>
      <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 mb-10 flex items-center gap-4">
        <span class="text-2xl">⚠️</span>
        <div>
          <p class="font-semibold text-yellow-800">Vous êtes sur le plan Gratuit</p>
          <p class="text-sm text-yellow-700">Passez à un plan payant pour recevoir plus de leads qualifiés.</p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Plans -->
    <h2 class="font-headline text-2xl text-on-surface mb-6">Choisir un plan</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-12">
      <?php
      $plans = [
        ['gratuit',  '0 €',   '/mois', 'Essentiel',  '3 leads/mois',  false],
        ['starter',  '49 €',  '/mois', 'Croissance', '20 leads/mois', false],
        ['pro',      '99 €',  '/mois', 'Excellence', '50 leads/mois', true],
        ['illimite', '199 €', '/mois', 'Prestige',   'Leads illimités', false],
      ];
      foreach ($plans as [$slug, $price, $period, $label, $leads, $featured]):
        $isCurrent = ($current['plan'] ?? $artisan['plan'] ?? 'gratuit') === $slug;
      ?>
        <div class="bg-white rounded-2xl border-2 <?= $featured ? 'border-primary shadow-lg shadow-primary/10' : 'border-outline-variant/20' ?> p-6 flex flex-col relative <?= $featured ? 'scale-105 z-10' : '' ?>">
          <?php if ($featured): ?>
            <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary text-on-primary px-3 py-0.5 text-[10px] font-bold uppercase tracking-widest rounded-full whitespace-nowrap">⭐ Populaire</div>
          <?php endif; ?>
          <div class="mb-4">
            <p class="font-label text-[10px] uppercase tracking-widest text-on-surface-variant mb-1"><?= $label ?></p>
            <h3 class="font-headline text-2xl font-bold"><?= ucfirst($slug) ?></h3>
            <div class="flex items-baseline gap-1 mt-1">
              <span class="text-3xl font-headline font-bold text-primary"><?= $price ?></span>
              <span class="text-on-surface-variant text-sm"><?= $period ?></span>
            </div>
            <p class="text-xs text-on-surface-variant mt-1"><?= $leads ?></p>
          </div>
          <?php if ($isCurrent): ?>
            <button disabled class="mt-auto w-full py-3 bg-surface-container text-on-surface-variant rounded-xl font-label text-xs uppercase tracking-widest font-bold cursor-not-allowed">
              Plan actuel
            </button>
          <?php elseif ($slug === 'gratuit'): ?>
            <button disabled class="mt-auto w-full py-3 border border-outline-variant/30 text-on-surface-variant rounded-xl font-label text-xs uppercase tracking-widest cursor-not-allowed">
              Plan de base
            </button>
          <?php else: ?>
            <button onclick="choosePlan('<?= $slug ?>')"
              class="mt-auto w-full py-3 <?= $featured ? 'bg-primary text-on-primary' : 'border border-primary text-primary hover:bg-primary hover:text-on-primary' ?> rounded-xl font-label text-xs uppercase tracking-widest font-bold transition-all hover:opacity-90">
              Choisir <?= ucfirst($slug) ?>
            </button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Achat leads à l'unité -->
    <div class="bg-white rounded-2xl border border-outline-variant/20 p-8 mb-10">
      <div class="flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
          <h3 class="font-headline text-xl mb-2">💡 Acheter des leads à l'unité</h3>
          <p class="text-on-surface-variant text-sm">Sans abonnement, achetez des leads individuellement à <strong class="text-primary">9,90 €</strong> par lead.</p>
        </div>
        <button onclick="buyLead()"
          class="flex-shrink-0 bg-primary text-on-primary px-8 py-3 rounded-xl font-label text-xs uppercase tracking-widest font-bold hover:opacity-90 transition-all">
          Acheter des leads
        </button>
      </div>
    </div>

    <!-- Historique paiements -->
    <h2 class="font-headline text-2xl text-on-surface mb-4">Historique des paiements</h2>
    <div class="bg-white rounded-2xl border border-outline-variant/20 overflow-hidden">
      <?php
      $paiements = Database::fetchAll(
        'SELECT * FROM paiements WHERE user_id = ? ORDER BY created_at DESC LIMIT 10',
        [$_SESSION['user_id']]
      );
      ?>
      <?php if (empty($paiements)): ?>
        <div class="text-center py-12">
          <span class="text-4xl block mb-3">💳</span>
          <p class="text-on-surface-variant">Aucun paiement pour le moment</p>
        </div>
      <?php else: ?>
        <table class="w-full text-sm">
          <thead class="bg-surface-container-low border-b border-outline-variant/10">
            <tr>
              <th class="text-left px-5 py-3 font-label text-xs uppercase tracking-widest text-on-surface-variant">Date</th>
              <th class="text-left px-5 py-3 font-label text-xs uppercase tracking-widest text-on-surface-variant">Type</th>
              <th class="text-left px-5 py-3 font-label text-xs uppercase tracking-widest text-on-surface-variant">Montant</th>
              <th class="text-left px-5 py-3 font-label text-xs uppercase tracking-widest text-on-surface-variant">Statut</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <?php foreach ($paiements as $p):
              $cls = ['paid' => 'bg-green-100 text-green-700', 'pending' => 'bg-yellow-100 text-yellow-700', 'failed' => 'bg-red-100 text-red-700'][$p['status']] ?? 'bg-gray-100 text-gray-600';
            ?>
              <tr class="hover:bg-surface-container-low transition-colors">
                <td class="px-5 py-3 text-on-surface-variant"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                <td class="px-5 py-3 font-semibold text-on-surface"><?= Security::e($p['type'] ?? 'Abonnement') ?></td>
                <td class="px-5 py-3 font-bold text-primary"><?= number_format($p['amount'] ?? 0, 2, ',', ' ') ?> €</td>
                <td class="px-5 py-3"><span class="text-xs font-semibold px-2 py-1 rounded-full <?= $cls ?>"><?= ucfirst($p['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  </div>
</main>

<script>
  const STRIPE_KEY = '<?= defined("STRIPE_PUBLIC_KEY") ? STRIPE_PUBLIC_KEY : "" ?>';
  const APP_URL = '<?= APP_URL ?>';

  async function choosePlan(plan) {
    if (!STRIPE_KEY) {
      alert('Stripe non configuré. Contactez l\'administrateur.');
      return;
    }
    try {
      const form = new FormData();
      form.append('plan', plan);
      form.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
      const res = await fetch(APP_URL + '/api/payments/create-intent', {
        method: 'POST',
        body: form
      });
      const data = await res.json();
      if (data.client_secret) {
        // Redirection vers page paiement Stripe
        window.location.href = APP_URL + '/dashboard/artisan/paiement?plan=' + plan + '&secret=' + data.client_secret;
      } else {
        alert(data.error || 'Erreur lors de la création du paiement.');
      }
    } catch (e) {
      alert('Erreur réseau. Réessayez.');
    }
  }

  async function buyLead() {
    choosePlan('lead_unitaire');
  }
</script>