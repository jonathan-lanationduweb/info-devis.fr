<?php /* views/admin/chatbot.php — remplacer chatbot par le nom réel */ ?>
<main class="pt-24 pb-16 min-h-screen bg-surface-container-low">
<div class="max-w-6xl mx-auto px-6">
  <div class="flex items-center justify-between mb-8">
    <h1 class="font-headline text-4xl text-on-surface capitalize">chatbot</h1>
    <a href="<?= APP_URL ?>/admin" class="text-primary font-label text-xs uppercase tracking-widest hover:underline">← Admin</a>
  </div>
  <div class="bg-white rounded-2xl border border-outline-variant/20 overflow-auto">
    <table class="w-full text-sm">
      <thead class="bg-surface-container-low border-b border-outline-variant/10">
        <tr id="table-head-chatbot"></tr>
      </thead>
      <tbody id="table-body-chatbot">
        <tr><td class="text-center py-16 text-on-surface-variant" colspan="10">
          <div class="text-4xl mb-3">🔧</div>
          <p>Données chargées depuis la base.</p>
        </td></tr>
      </tbody>
    </table>
  </div>
</div>
</main>
