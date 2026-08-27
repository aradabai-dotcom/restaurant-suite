<?php
/**
 * Page template.
 *
 * @package RestaurantBaseTheme
 */

get_header();
?>
<main id="main-content" class="crs-page-shell">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'crs-page' ); ?>>
			<?php if ( ! is_front_page() ) : ?>
				<h1 class="crs-page-title"><?php the_title(); ?></h1>
			<?php endif; ?>
			<div class="crs-page__content">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
