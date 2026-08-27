<?php
/**
 * Theme footer.
 *
 * @package RestaurantBaseTheme
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="crs-site-footer">
	<div class="crs-site-footer__inner">
		<div>
			<p class="crs-site-footer__title"><?php bloginfo( 'name' ); ?></p>
			<p class="crs-site-footer__copy"><?php esc_html_e( 'Une expérience de restaurant claire, rapide et pensée pour le mobile.', 'restaurant-base-theme' ); ?></p>
		</div>
		<p class="crs-site-footer__copy">&copy; <?php echo esc_html( (string) gmdate( 'Y' ) ); ?></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
