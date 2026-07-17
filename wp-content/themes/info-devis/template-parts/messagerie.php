<?php
/**
 * Interface de messagerie partagée (client et artisan).
 * Args : ['role' => 'client'|'artisan', 'threads' => array, 'user' => WP_User, 'title' => string]
 * Fil ouvert via ?d={demande_id}&f={fiche_id}.
 */

$idv_role    = $args['role'];
$idv_threads = $args['threads'];
$idv_user    = $args['user'];

// Fil ouvert : depuis l'URL, sinon le plus récent.
$idv_open_d = (int) ($_GET['d'] ?? 0);
$idv_open_f = (int) ($_GET['f'] ?? 0);
if (!$idv_open_d && $idv_threads) {
    $idv_open_d = (int) $idv_threads[0]['demande_id'];
    $idv_open_f = (int) $idv_threads[0]['fiche_id'];
}

$idv_messages = [];
$idv_open_ok  = false;
if ($idv_open_d && $idv_open_f && idc_msg_role_in_thread($idv_open_d, $idv_open_f, $idv_user) !== '') {
    $idv_open_ok = true;
    idc_msg_mark_read($idv_open_d, $idv_open_f, $idv_user->ID);
    $idv_messages = idc_msg_get($idv_open_d, $idv_open_f);
}

$idv_nonce = wp_create_nonce('idc_message_send');
?>

<div class="md:ml-72 pt-24 md:pt-28 pb-24 md:pb-8 px-0 md:px-8 min-h-screen">
  <div class="max-w-[1200px] mx-auto">

    <header class="px-6 md:px-0 mb-6">
      <div class="flex items-center gap-4 mb-3">
        <span class="bg-primary/10 text-primary px-3 py-1 text-[10px] font-bold tracking-[0.2em] uppercase rounded-full">Messagerie</span>
        <span class="h-px w-12 bg-outline-variant/30"></span>
      </div>
      <h1 class="text-4xl md:text-5xl font-headline italic font-bold text-on-surface leading-tight">Messages</h1>
    </header>

    <?php if (!$idv_threads) : ?>
      <div class="mx-6 md:mx-0 p-10 bg-surface-container-low rounded-2xl text-center">
        <span class="material-symbols-outlined text-5xl text-outline-variant mb-4 block">forum</span>
        <p class="font-headline text-xl mb-2">Aucune conversation pour le moment</p>
        <p class="text-on-surface-variant text-sm">
          <?php echo $idv_role === 'client'
              ? 'Les échanges avec vos artisans apparaîtront ici dès qu\'un professionnel aura répondu à une demande.'
              : 'Les échanges avec vos clients apparaîtront ici dès que vous aurez accepté une opportunité.'; ?>
        </p>
      </div>
    <?php else : ?>

    <div class="grid md:grid-cols-[320px_1fr] gap-0 md:gap-6 bg-surface md:bg-transparent border md:border-0 border-outline-variant/10 rounded-2xl overflow-hidden" style="min-height:60vh;">

      <!-- Liste des fils -->
      <aside class="border-r md:border md:rounded-2xl border-outline-variant/10 bg-surface overflow-y-auto" style="max-height:70vh;">
        <?php foreach ($idv_threads as $idv_t) :
            $idv_is_open = ($idv_t['demande_id'] === $idv_open_d && $idv_t['fiche_id'] === $idv_open_f);
            $idv_label   = $idv_role === 'client' ? $idv_t['fiche_title'] : ($idv_t['reference'] ?: $idv_t['demande']);
            $idv_sub     = $idv_role === 'client' ? ($idv_t['reference'] ?: $idv_t['demande']) : '';
            $idv_href    = add_query_arg(['d' => $idv_t['demande_id'], 'f' => $idv_t['fiche_id']]);
        ?>
          <a href="<?php echo esc_url($idv_href); ?>"
             class="flex items-start gap-3 px-4 py-4 border-b border-outline-variant/10 transition-colors <?php echo $idv_is_open ? 'bg-primary/5 border-l-2 border-l-primary' : 'hover:bg-surface-container-low'; ?>">
            <span class="material-symbols-outlined text-primary/70 mt-0.5">account_circle</span>
            <div class="min-w-0 flex-1">
              <div class="flex items-center justify-between gap-2">
                <p class="font-bold text-sm text-on-surface truncate"><?php echo esc_html($idv_label); ?></p>
                <?php if ($idv_t['unread'] > 0) : ?>
                  <span class="shrink-0 bg-primary text-on-primary text-[10px] font-bold rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center"><?php echo (int) $idv_t['unread']; ?></span>
                <?php endif; ?>
              </div>
              <?php if ($idv_sub) : ?><p class="text-[11px] text-on-surface-variant/70 truncate"><?php echo esc_html($idv_sub); ?></p><?php endif; ?>
              <p class="text-xs text-on-surface-variant truncate mt-1"><?php echo esc_html(wp_trim_words($idv_t['last_body'] ?: 'Nouvelle conversation', 8)); ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </aside>

      <!-- Conversation -->
      <section class="flex flex-col bg-surface md:border md:rounded-2xl border-outline-variant/10" style="max-height:70vh;">
        <?php if (!$idv_open_ok) : ?>
          <div class="flex-1 flex items-center justify-center text-on-surface-variant text-sm p-8">Sélectionnez une conversation.</div>
        <?php else :
            $idv_other = $idv_role === 'client' ? get_the_title($idv_open_f) : (get_post_meta($idv_open_d, '_idc_contact_name', true) ?: 'Client');
            $idv_ref   = (string) get_post_meta($idv_open_d, '_idc_reference', true);
        ?>
          <header class="px-6 py-4 border-b border-outline-variant/10 flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">account_circle</span>
            <div>
              <p class="font-bold text-sm"><?php echo esc_html($idv_other); ?></p>
              <p class="text-[11px] text-on-surface-variant">Demande <?php echo esc_html($idv_ref); ?></p>
            </div>
            <?php if ($idv_role === 'client') : ?>
              <a href="<?php echo esc_url(get_permalink($idv_open_f)); ?>" class="ml-auto text-primary text-xs font-bold hover:underline">Voir la fiche</a>
            <?php endif; ?>
          </header>

          <div id="msg-list" class="flex-1 overflow-y-auto px-6 py-6 space-y-4">
            <?php foreach ($idv_messages as $idv_m) :
                $idv_mine = (int) $idv_m->sender_user_id === (int) $idv_user->ID;
            ?>
              <div class="flex <?php echo $idv_mine ? 'justify-end' : 'justify-start'; ?>">
                <div class="max-w-[75%] rounded-2xl px-4 py-2.5 <?php echo $idv_mine ? 'bg-primary text-on-primary rounded-br-sm' : 'bg-surface-container text-on-surface rounded-bl-sm'; ?>">
                  <p class="text-sm whitespace-pre-wrap break-words"><?php echo nl2br(esc_html($idv_m->body)); ?></p>
                  <p class="text-[10px] mt-1 <?php echo $idv_mine ? 'text-on-primary/60' : 'text-on-surface-variant/60'; ?>"><?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($idv_m->created_at))); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <form id="msg-form" class="border-t border-outline-variant/10 p-4 flex items-end gap-3">
            <input type="hidden" name="demande_id" value="<?php echo (int) $idv_open_d; ?>">
            <input type="hidden" name="fiche_id" value="<?php echo (int) $idv_open_f; ?>">
            <input type="hidden" name="idc_msg_nonce" value="<?php echo esc_attr($idv_nonce); ?>">
            <textarea name="body" rows="1" required maxlength="4000" placeholder="Écrivez votre message…"
                      class="flex-1 resize-none bg-surface-container border-none focus:ring-1 focus:ring-primary p-3 rounded-xl font-body text-sm"></textarea>
            <button type="submit" class="bg-primary text-on-primary rounded-xl px-5 py-3 font-bold text-sm hover:opacity-90 transition-all flex items-center gap-2 shrink-0">
              <span class="material-symbols-outlined text-lg">send</span>
            </button>
          </form>
        <?php endif; ?>
      </section>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($idv_open_ok) : ?>
<script>
(function () {
  const AJAX = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
  const list = document.getElementById('msg-list');
  const form = document.getElementById('msg-form');
  const ta   = form.querySelector('[name=body]');
  const mine = <?php echo (int) $idv_user->ID; ?>;
  list.scrollTop = list.scrollHeight;

  ta.addEventListener('input', () => { ta.style.height = 'auto'; ta.style.height = Math.min(ta.scrollHeight, 120) + 'px'; });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = ta.value.trim();
    if (!body) return;
    const btn = form.querySelector('button');
    btn.disabled = true;
    const fd = new FormData(form);
    fd.append('action', 'idc_message_send');
    try {
      const res = await fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' });
      const data = await res.json();
      if (data.success) {
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-end';
        wrap.innerHTML = '<div class="max-w-[75%] rounded-2xl px-4 py-2.5 bg-primary text-on-primary rounded-br-sm">'
          + '<p class="text-sm whitespace-pre-wrap break-words"></p>'
          + '<p class="text-[10px] mt-1 text-on-primary/60">' + data.message.at + '</p></div>';
        wrap.querySelector('.text-sm').textContent = data.message.body;
        list.appendChild(wrap);
        list.scrollTop = list.scrollHeight;
        ta.value = ''; ta.style.height = 'auto';
      } else {
        alert(data.error || 'Erreur lors de l\'envoi.');
      }
    } catch (err) {
      alert('Erreur réseau. Réessayez.');
    }
    btn.disabled = false;
  });
})();
</script>
<?php endif; ?>
