<?php
/**
 * Main fallback template.
 *
 * @package RestaurantBaseTheme
 */

get_header();
?>
<main id="main-content" class="crs-page-shell">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'crs-entry' ); ?>>
				<h1 class="crs-page-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<div class="crs-entry__content"><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<h1 class="crs-page-title"><?php esc_html_e( 'Bienvenue', 'restaurant-base-theme' ); ?></h1>
	<?php endif; ?>
</main>
<?php
get_footer();
