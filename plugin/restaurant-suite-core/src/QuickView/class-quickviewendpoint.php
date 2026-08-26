<?php
/**
 * Quick View REST endpoint.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\QuickView;

/**
 * Provides the authenticated-by-nonce Quick View fragment endpoint.
 */
final class QuickViewEndpoint {

	/**
	 * Register the public route.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_route' ) );
	}

	/**
	 * Register the product fragment route.
	 *
	 * @return void
	 */
	public function register_route(): void {
		register_rest_route(
			'crs/v1',
			'/quick-view/(?P<product_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_fragment' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'product_id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
						'validate_callback' => static fn ( mixed $value ): bool => (int) $value > 0,
					),
				),
			)
		);
	}

	/**
	 * Verify the short-lived public nonce.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return bool
	 */
	public function check_permission( \WP_REST_Request $request ): bool {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( '' === $nonce ) {
			$nonce = (string) $request->get_param( '_wpnonce' );
		}

		return 1 === wp_verify_nonce( $nonce, 'wp_rest' );
	}

	/**
	 * Return the escaped product fragment.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_fragment( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$product_id = absint( $request->get_param( 'product_id' ) );
		$product    = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : false;

		if ( ! is_object( $product ) || 'publish' !== get_post_status( $product_id ) || ( method_exists( $product, 'is_visible' ) && ! $product->is_visible() ) ) {
			return new \WP_Error(
				'crs_product_not_available',
				__( 'Ce produit n’est pas disponible.', 'restaurant-suite-core' ),
				array( 'status' => 404 )
			);
		}

		return new \WP_REST_Response(
			array(
				'product_id' => $product_id,
				'html'       => $this->render_fragment( $product ),
			),
			200
		);
	}

	/**
	 * Render product data and the WooCommerce-native purchase form.
	 *
	 * @param object $product Public WooCommerce product object.
	 * @return string
	 */
	private function render_fragment( object $product ): string {
		$id          = (int) $product->get_id();
		$name        = (string) $product->get_name();
		$url         = (string) $product->get_permalink();
		$description = method_exists( $product, 'get_short_description' ) ? (string) $product->get_short_description() : '';
		$price       = method_exists( $product, 'get_price_html' ) ? (string) $product->get_price_html() : '';
		$available   = method_exists( $product, 'is_purchasable' ) && method_exists( $product, 'is_in_stock' ) && $product->is_purchasable() && $product->is_in_stock();
		$image       = method_exists( $product, 'get_image' ) ? (string) $product->get_image( 'woocommerce_single', array( 'loading' => 'eager' ) ) : '';

		$html  = sprintf( '<div class="crs-quickview__fragment" data-crs-quickview-fragment data-product-id="%d">', $id );
		$html .= '<div class="crs-quickview__image">' . ( '' !== $image ? $image : '<span class="crs-quickview__placeholder" aria-hidden="true"></span>' ) . '</div>';
		$html .= '<div class="crs-quickview__details"><h2 class="crs-quickview__product-title">' . esc_html( $name ) . '</h2>';

		if ( '' !== $price ) {
			$html .= '<div class="crs-quickview__price">' . wp_kses_post( $price ) . '</div>';
		}
		if ( '' !== $description ) {
			$html .= '<div class="crs-quickview__description">' . wp_kses_post( wpautop( $description ) ) . '</div>';
		}

		if ( ! $available ) {
			$html .= '<p class="crs-quickview__availability" role="status">' . esc_html__( 'Indisponible', 'restaurant-suite-core' ) . '</p>';
		} else {
			$html .= $this->render_native_cart_form( $product );
		}

		$html .= '<a class="crs-quickview__permalink" href="' . esc_url( $url ) . '">' . esc_html__( 'Voir la fiche produit', 'restaurant-suite-core' ) . '</a>';
		return $html . '</div></div>';
	}

	/**
	 * Render WooCommerce’s native simple or variable add-to-cart form.
	 *
	 * @param object $product_object WooCommerce product object.
	 * @return string
	 */
	private function render_native_cart_form( object $product_object ): string {
		if ( ! function_exists( 'woocommerce_template_single_add_to_cart' ) ) {
			return '';
		}

		global $product;
		$previous_product = $product;
		$product          = $product_object;

		ob_start();
		woocommerce_template_single_add_to_cart();
		$form = (string) ob_get_clean();

		$product = $previous_product;
		return wp_kses(
			$form,
			array(
				'form'   => array(
					'action'  => true,
					'class'   => true,
					'data-*'  => true,
					'enctype' => true,
					'method'  => true,
				),
				'div'    => array(
					'class'  => true,
					'data-*' => true,
				),
				'table'  => array( 'class' => true ),
				'tbody'  => array(),
				'tr'     => array(),
				'th'     => array(
					'class' => true,
					'scope' => true,
				),
				'td'     => array( 'class' => true ),
				'label'  => array(
					'class' => true,
					'for'   => true,
				),
				'select' => array(
					'aria-label' => true,
					'class'      => true,
					'data-*'     => true,
					'id'         => true,
					'name'       => true,
				),
				'option' => array(
					'selected' => true,
					'value'    => true,
				),
				'input'  => array(
					'autocomplete' => true,
					'class'        => true,
					'max'          => true,
					'min'          => true,
					'name'         => true,
					'step'         => true,
					'type'         => true,
					'value'        => true,
				),
				'button' => array(
					'class'    => true,
					'disabled' => true,
					'name'     => true,
					'type'     => true,
					'value'    => true,
				),
				'span'   => array(
					'aria-hidden' => true,
					'class'       => true,
				),
				'p'      => array( 'class' => true ),
			)
		);
	}
}
