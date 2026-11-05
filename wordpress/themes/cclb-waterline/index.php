<?php
/**
 * Fallback for anything without its own template (editor-made pages and posts):
 * the waterline hero with the page title, then the content in a readable column.
 */
defined( 'ABSPATH' ) || exit;
$title = is_singular() ? single_post_title( '', false ) : wp_strip_all_tags( get_the_archive_title() );
cclb_page( $title . ' | ' . cclb_parish( 'name' ), wp_strip_all_tags( get_the_excerpt() ) ?: $title, array() );
get_header();
echo cclb_surface( 'inner', '', '', $title ?: cclb_parish( 'name' ), '' ); // phpcs:ignore
?>
<section class="band band--normal" data-tone="air">
  <div class="wrap grid">
    <div class="prose" style="grid-column:1/-1;max-width:44rem">
      <?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
    </div>
  </div>
</section>
<?php get_footer(); ?>
