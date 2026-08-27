<?php

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 260, 'flex-height' => true, 'flex-width' => true ) );
		register_nav_menus( array( 'primary' => __( 'Primary Menu', 'restaurant-base-theme' ) ) );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		wp_enqueue_style(
			'restaurant-base-theme',
			get_stylesheet_uri(),
			array(),
			(string) wp_get_theme()->get( 'Version' )
		);
	}
);

/**
 * Provide a useful menu before a restaurant configures a WordPress menu.
 *
 * @return void
 */
function restaurant_base_fallback_menu(): void {
	$items = array(
		home_url( '/' )             => __( 'Accueil', 'restaurant-base-theme' ),
		home_url( '/boutique/' )    => __( 'Menu', 'restaurant-base-theme' ),
		function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/panier/' ) => __( 'Panier', 'restaurant-base-theme' ),
	);
	echo '<ul class="crs-site-nav__list">';
	foreach ( $items as $url => $label ) {
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}
