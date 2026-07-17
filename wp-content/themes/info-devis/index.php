<?php
/**
 * Gabarit par défaut (fallback hiérarchie de templates).
 */

get_header();
?>
<section class="page-banner page-banner--blog">
  <div class="container max-w-screen-xl mx-auto px-8">
    <h1 class="page-banner__title"><?php echo is_home() ? 'Blog' : esc_html(wp_get_document_title()); ?></h1>
  </div>
</section>

<section class="py-16 bg-surface">
  <div class="max-w-screen-xl mx-auto px-8">
    <?php if (have_posts()) : ?>
      <div class="blog-grid">
        <?php while (have_posts()) : the_post(); ?>
          <a href="<?php the_permalink(); ?>" class="blog-card">
            <div class="blog-card__visual">
              <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('medium', ['style' => 'width:100%;height:100%;object-fit:cover;']); ?>
              <?php else : ?>
                <i class="fa-solid fa-newspaper" style="color:#fff;font-size:2rem;" aria-hidden="true"></i>
              <?php endif; ?>
            </div>
            <div class="blog-card__body">
              <h2 class="blog-card__title"><?php the_title(); ?></h2>
              <p class="blog-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>
              <div class="blog-card__footer">
                <span class="blog-card__date"><?php echo esc_html(get_the_date()); ?></span>
                <span class="blog-card__lire">Lire l'article →</span>
              </div>
            </div>
          </a>
        <?php endwhile; ?>
      </div>
      <div class="mt-10"><?php the_posts_pagination(); ?></div>
    <?php else : ?>
      <div class="blog-empty">
        <div class="blog-empty__icon"><i class="fa-regular fa-folder-open" aria-hidden="true"></i></div>
        <p>Aucun contenu pour le moment.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php
get_footer();
