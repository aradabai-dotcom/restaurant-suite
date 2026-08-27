<?php
/**
 * Theme header.
 *
 * @package RestaurantBaseTheme
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#main-content"><?php esc_html_e( 'Aller au contenu', 'restaurant-base-theme' ); ?></a>
<header class="crs-site-header">
	<div class="crs-site-header__inner">
		<a class="crs-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
		<nav class="crs-site-nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'restaurant-base-theme' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'crs-site-nav__list',
					'fallback_cb'    => 'restaurant_base_fallback_menu',
				)
			);
			?>
		</nav>
		<a class="crs-header-cart" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/panier/' ) ); ?>" data-crs-cart-open>
			<span aria-hidden="true">◎</span>
			<?php esc_html_e( 'Panier', 'restaurant-base-theme' ); ?>
			<span data-crs-cart-count>0</span>
		</a>
	</div>
</header>
