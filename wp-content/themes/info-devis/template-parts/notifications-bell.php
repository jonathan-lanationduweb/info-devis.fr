<?php
/**
 * Cloche de notifications + menu déroulant (porté de views/partials/notifications_bell.php).
 * Affichée dans l'en-tête pour les utilisateurs connectés (client/artisan).
 * Alimentée par l'API AJAX idc_notifications_list / idc_notifications_read.
 */
if (!defined('ABSPATH') || !is_user_logged_in()) {
    return;
}
$idv_notif_nonce = wp_create_nonce('idc_notifications');
$idv_notif_ajax  = admin_url('admin-ajax.php');
?>
<div class="notif-bell-wrap" id="idv-notif-wrap">
  <button type="button" class="notif-bell-btn" id="idv-notif-btn" aria-label="Notifications">
    <i class="fa-solid fa-bell" style="font-size:16px;"></i>
    <span class="notif-bell-badge hidden" id="idv-notif-badge">0</span>
  </button>
  <div class="notif-dropdown" id="idv-notif-dropdown" role="menu" aria-label="Notifications">
    <div class="notif-dropdown__header">
      <span class="notif-dropdown__title">Notifications</span>
      <button type="button" id="idv-notif-markall" class="text-xs text-primary hover:underline font-bold">Tout marquer lu</button>
    </div>
    <div class="notif-dropdown__list" id="idv-notif-list">
      <div class="notif-empty">Aucune notification</div>
    </div>
  </div>
</div>

<script>
(function () {
  const AJAX  = '<?php echo esc_js($idv_notif_ajax); ?>';
  const NONCE = '<?php echo esc_js($idv_notif_nonce); ?>';
  const HOME  = '<?php echo esc_js(home_url('')); ?>';
  const btn = document.getElementById('idv-notif-btn');
  const wrap = document.getElementById('idv-notif-wrap');
  const dropdown = document.getElementById('idv-notif-dropdown');
  const badge = document.getElementById('idv-notif-badge');
  const list = document.getElementById('idv-notif-list');
  const markAll = document.getElementById('idv-notif-markall');
  if (!btn) return;

  const ICONS = {
    new_lead: 'fa-lightbulb', rdv_demande: 'fa-calendar-plus', rdv_confirmed: 'fa-calendar-check',
    avis_new: 'fa-star', devis_signe: 'fa-file-signature', message_new: 'fa-comment-dots',
  };
  const getIcon = t => ICONS[t] || 'fa-bell';
  const linkFor = it => {
    switch (it.type) {
      case 'new_lead':      return HOME + '/dashboard/artisan/leads/';
      case 'rdv_demande':   return HOME + '/dashboard/artisan/rdv/';
      case 'rdv_confirmed': return HOME + '/mes-rdv/';
      case 'avis_new':      return HOME + '/dashboard/artisan/avis/';
      case 'devis_signe':   return HOME + '/dashboard/artisan/rdv/';
      case 'message_new':   return HOME + '/dashboard/artisan/messages/';
      default:              return null;
    }
  };
  const esc = s => String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

  async function load() {
    try {
      const res = await fetch(AJAX + '?action=idc_notifications_list', { credentials: 'same-origin' });
      const data = await res.json();
      if (!data.success) return;
      if (data.unread > 0) { badge.textContent = data.unread > 99 ? '99+' : data.unread; badge.classList.remove('hidden'); }
      else { badge.classList.add('hidden'); }
      if (!data.items || !data.items.length) {
        list.innerHTML = '<div class="notif-empty"><i class="fa-solid fa-bell-slash" style="font-size:24px;opacity:0.4"></i><br><br>Aucune notification</div>';
        return;
      }
      list.innerHTML = data.items.map(it => {
        const link = linkFor(it);
        const cls = it.is_read == 0 ? 'notif-item notif-item--unread' : 'notif-item';
        const inner = '<div class="notif-item__icon"><i class="fa-solid ' + getIcon(it.type) + '" style="font-size:14px;"></i></div>'
          + '<div class="notif-item__body"><p class="notif-item__title">' + esc(it.title) + '</p>'
          + (it.body ? '<p class="notif-item__text">' + esc(it.body) + '</p>' : '')
          + '<p class="notif-item__time">' + esc(it.ago || '') + '</p></div>';
        return link
          ? '<a href="' + link + '" class="' + cls + '" data-notif-id="' + it.id + '" style="text-decoration:none;color:inherit;">' + inner + '</a>'
          : '<div class="' + cls + '" data-notif-id="' + it.id + '">' + inner + '</div>';
      }).join('');
      list.querySelectorAll('[data-notif-id]').forEach(el => el.addEventListener('click', () => markRead(el.getAttribute('data-notif-id'))));
    } catch (e) {}
  }
  async function markRead(id) {
    const fd = new FormData(); fd.append('action', 'idc_notifications_read'); fd.append('id', id); fd.append('idc_notif_nonce', NONCE);
    try { await fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' }); } catch (e) {}
  }
  markAll.addEventListener('click', async e => {
    e.stopPropagation();
    const fd = new FormData(); fd.append('action', 'idc_notifications_read'); fd.append('all', '1'); fd.append('idc_notif_nonce', NONCE);
    try { await fetch(AJAX, { method: 'POST', body: fd, credentials: 'same-origin' }); load(); } catch (e) {}
  });
  btn.addEventListener('click', e => { e.stopPropagation(); dropdown.classList.toggle('open'); if (dropdown.classList.contains('open')) load(); });
  document.addEventListener('click', e => { if (!wrap.contains(e.target)) dropdown.classList.remove('open'); });
  load();
  setInterval(load, 60000);
})();
</script>
