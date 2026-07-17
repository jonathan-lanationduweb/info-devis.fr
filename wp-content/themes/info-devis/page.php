<?php
/**
 * Gabarit page générique — bannière sombre originale (.page-banner) + contenu.
 */

get_header();

while (have_posts()) :
    the_post();
    ?>
    <section class="page-banner page-banner--metier">
      <div class="container max-w-screen-xl mx-auto px-8">
        <h1 class="page-banner__title"><?php the_title(); ?></h1>
      </div>
    </section>

    <section class="py-16 bg-surface">
      <div class="max-w-screen-xl mx-auto px-8">
        <div class="entry-content font-body leading-relaxed">
          <?php the_content(); ?>
        </div>
      </div>
    </section>
    <?php
endwhile;

get_footer();
