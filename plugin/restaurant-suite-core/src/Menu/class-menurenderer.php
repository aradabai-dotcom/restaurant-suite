<?php
/**
 * Server-side menu renderer.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Menu;

/**
 * Renders WooCommerce products as accessible menu cards.
 */
final class MenuRenderer {

	/**
	 * Constructor.
	 *
	 * @param MenuQuery $query Catalog query service.
	 */
	public function __construct( private readonly MenuQuery $query ) {
	}

	/**
	 * Render a menu from normalized WooCommerce query arguments.
	 *
	 * @param array<string, mixed> $raw_args Raw shortcode, block, or widget arguments.
	 * @return string
	 */
	public function render( array $raw_args = array() ): string {
		$args     = MenuArguments::normalize( $raw_args );
		$products = $this->query->get_products( $args );
		$this->enqueue_assets();

		if ( empty( $products ) ) {
			return '<section class="crs-menu crs-menu--empty" aria-live="polite"><p class="crs-menu__empty">' . esc_html__( 'Aucun plat disponible dans cette catégorie.', 'restaurant-suite-core' ) . '</p></section>';
		}

		$html  = sprintf(
			'<section class="crs-menu crs-menu--columns-%d" data-crs-menu data-crs-page="%d">',
			$args['columns'],
			$args['page']
		);
		$html .= '<div class="crs-menu__grid">';

		foreach ( $products as $product ) {
			if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
				continue;
			}
			$html .= $this->render_product( $product );
		}

		return $html . '</div></section>';
	}

	/**
	 * Enqueue the menu stylesheet only when a menu is rendered.
	 *
	 * @return void
	 */
	private function enqueue_assets(): void {
		if ( function_exists( 'wp_enqueue_style' ) ) {
			wp_enqueue_style(
				'crs-menu',
				CRS_PLUGIN_URL . 'assets/build/menu.css',
				array(),
				CRS_VERSION
			);
		}
	}

	/**
	 * Render one product card using WooCommerce getters.
	 *
	 * @param object $product WooCommerce product object.
	 * @return string
	 */
	private function render_product( object $product ): string {
		$id           = (int) $product->get_id();
		$name         = (string) $product->get_name();
		$url          = (string) $product->get_permalink();
		$is_available = (bool) $product->is_purchasable() && (bool) $product->is_in_stock();
		$is_variable  = method_exists( $product, 'is_type' ) && $product->is_type( 'variable' );
		$image        = method_exists( $product, 'get_image' ) ? (string) $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) : '';
		$price        = method_exists( $product, 'get_price_html' ) ? (string) $product->get_price_html() : '';
		$description  = method_exists( $product, 'get_short_description' ) ? (string) $product->get_short_description() : '';

		$html  = sprintf( '<article class="crs-menu__item" data-crs-product-id="%d">', $id );
		$html .= '<a class="crs-menu__media" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $name ) . '">';
		$html .= '' !== $image ? $image : '<span class="crs-menu__placeholder" aria-hidden="true"></span>';
		$html .= '</a><div class="crs-menu__body">';
		$html .= '<h3 class="crs-menu__title"><a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a></h3>';

		if ( '' !== $description ) {
			$html .= '<div class="crs-menu__description">' . wp_kses_post( wpautop( $description ) ) . '</div>';
		}
		if ( '' !== $price ) {
			$html .= '<div class="crs-menu__price">' . wp_kses_post( $price ) . '</div>';
		}

		if ( ! $is_available ) {
			$html .= '<p class="crs-menu__availability crs-menu__availability--unavailable" aria-label="' . esc_attr__( 'Indisponible', 'restaurant-suite-core' ) . '">' . esc_html__( 'Indisponible', 'restaurant-suite-core' ) . '</p>';
		} elseif ( $is_variable ) {
			$html .= '<a class="crs-menu__action" href="' . esc_url( $url ) . '">' . esc_html__( 'Voir les options', 'restaurant-suite-core' ) . '</a>';
		} else {
			$cart_url = function_exists( 'wc_get_cart_url' ) ? add_query_arg( 'add-to-cart', $id, wc_get_cart_url() ) : $url;
			$html    .= '<a class="crs-menu__action" href="' . esc_url( $cart_url ) . '" rel="nofollow">' . esc_html__( 'Ajouter au panier', 'restaurant-suite-core' ) . '</a>';
		}

		return $html . '</div></article>';
	}
}
