<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="flex items-stretch gap-2 max-w-xl">
  <input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="Rechercher…"
    class="flex-1 border border-outline-variant rounded-lg px-4 py-3 text-sm font-body bg-white" />
  <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg font-semibold text-sm hover:opacity-90 transition-all">
    Chercher
  </button>
</form>
