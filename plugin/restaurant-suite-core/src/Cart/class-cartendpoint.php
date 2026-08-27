<?php
/**
 * Cart REST endpoint.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Cart;

/**
 * Exposes the single Restaurant Suite cart store over WooCommerce's cart.
 */
final class CartEndpoint {

	/**
	 * Register the cart route.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_route' ) );
	}

	/**
	 * Register cart mutations and refresh.
	 *
	 * @return void
	 */
	public function register_route(): void {
		register_rest_route(
			'crs/v1',
			'/cart/(?P<action>add|update|remove|refresh)',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'action' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_key',
						'validate_callback' => static fn ( mixed $value ): bool => in_array( (string) $value, array( 'add', 'update', 'remove', 'refresh' ), true ),
					),
				),
			)
		);
	}

	/**
	 * Verify the standard WordPress REST nonce.
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
	 * Apply one cart action and return the normalized WooCommerce snapshot.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$woocommerce = function_exists( 'WC' ) ? WC() : null;
		if ( is_object( $woocommerce ) && ! is_object( $woocommerce->cart ?? null ) ) {
			if ( function_exists( 'wc_load_cart' ) ) {
				wc_load_cart();
				$woocommerce = function_exists( 'WC' ) ? WC() : $woocommerce;
			}
			if ( ! is_object( $woocommerce->cart ?? null ) && method_exists( $woocommerce, 'initialize_session' ) ) {
				$woocommerce->initialize_session();
			}
			if ( ! is_object( $woocommerce->cart ?? null ) && method_exists( $woocommerce, 'initialize_cart' ) ) {
				$woocommerce->initialize_cart();
			}
		}
		$cart = is_object( $woocommerce ) ? ( $woocommerce->cart ?? null ) : null;
		if ( ! is_object( $cart ) ) {
			return new \WP_Error(
				'crs_cart_unavailable',
				__( 'Le panier WooCommerce est momentanément indisponible.', 'restaurant-suite-core' ),
				array( 'status' => 503 )
			);
		}

		// WC_Cart loads the session contents lazily from get_cart(). REST requests can
		// have a cart object before its line items are hydrated, so hydrate before
		// update/remove attempt to address a cart item key.
		if ( method_exists( $cart, 'get_cart' ) ) {
			$cart->get_cart();
		}

		$action = (string) $request->get_param( 'action' );
		$result = true;
		switch ( $action ) {
			case 'add':
				$result = $this->add_item( $cart, $request );
				break;
			case 'update':
				$result = $this->update_item( $cart, $request );
				break;
			case 'remove':
				$result = $this->remove_item( $cart, $request );
				break;
			case 'refresh':
				break;
			default:
				return new \WP_Error(
					'crs_cart_invalid_action',
					__( 'Action panier inconnue.', 'restaurant-suite-core' ),
					array( 'status' => 400 )
				);
		}

		if ( false === $result || ( function_exists( 'is_wp_error' ) && is_wp_error( $result ) ) ) {
			return new \WP_Error(
				'crs_cart_action_failed',
				__( 'Le panier n’a pas pu être mis à jour.', 'restaurant-suite-core' ),
				array( 'status' => 409 )
			);
		}

		if ( method_exists( $cart, 'calculate_totals' ) ) {
			$cart->calculate_totals();
		}

		return new \WP_REST_Response( $this->snapshot( $cart ), 200 );
	}

	/**
	 * Add an item using WooCommerce's product and variation validation.
	 *
	 * @param object           $cart WooCommerce cart object.
	 * @param \WP_REST_Request $request REST request.
	 * @return mixed
	 */
	private function add_item( object $cart, \WP_REST_Request $request ): mixed {
		if ( ! method_exists( $cart, 'add_to_cart' ) ) {
			return false;
		}

		$product_id   = absint( $request->get_param( 'product_id' ) );
		$raw_quantity = $request->get_param( 'quantity' );
		$quantity     = max( 1, absint( null === $raw_quantity ? 1 : $raw_quantity ) );
		$variation_id = absint( $request->get_param( 'variation_id' ) );
		$variation    = $this->normalize_variation( $request->get_param( 'variation' ) );

		if ( $product_id < 1 ) {
			return false;
		}

		if ( 0 === $variation_id && ! empty( $variation ) && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $product_id );
			if ( is_object( $product ) && method_exists( $product, 'get_matching_variation' ) ) {
				$variation_id = absint( $product->get_matching_variation( $variation ) );
			}
		}

		return $cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
	}

	/**
	 * Update a line quantity.
	 *
	 * @param object           $cart WooCommerce cart object.
	 * @param \WP_REST_Request $request REST request.
	 * @return mixed
	 */
	private function update_item( object $cart, \WP_REST_Request $request ): mixed {
		$key      = sanitize_text_field( (string) $request->get_param( 'cart_item_key' ) );
		$quantity = max( 0, absint( $request->get_param( 'quantity' ) ) );
		if ( '' === $key || ! method_exists( $cart, 'set_quantity' ) ) {
			return false;
		}

		return $cart->set_quantity( $key, $quantity, true );
	}

	/**
	 * Remove a line from the WooCommerce cart.
	 *
	 * @param object           $cart WooCommerce cart object.
	 * @param \WP_REST_Request $request REST request.
	 * @return mixed
	 */
	private function remove_item( object $cart, \WP_REST_Request $request ): mixed {
		$key = sanitize_text_field( (string) $request->get_param( 'cart_item_key' ) );
		if ( '' === $key || ! method_exists( $cart, 'remove_cart_item' ) ) {
			return false;
		}

		$result = $cart->remove_cart_item( $key );
		if ( false === $result && method_exists( $cart, 'get_cart' ) ) {
			$items = $cart->get_cart();
			return ! is_array( $items ) || ! isset( $items[ $key ] );
		}

		return $result;
	}

	/**
	 * Normalize variation attributes without trusting client-side pricing.
	 *
	 * @param mixed $raw_variation Raw request variation map.
	 * @return array<string, string>
	 */
	private function normalize_variation( mixed $raw_variation ): array {
		if ( ! is_array( $raw_variation ) ) {
			return array();
		}

		$variation = array();
		foreach ( $raw_variation as $name => $value ) {
			if ( is_string( $name ) && is_scalar( $value ) ) {
				$variation[ sanitize_key( $name ) ] = sanitize_text_field( (string) $value );
			}
		}

		return $variation;
	}

	/**
	 * Build the normalized client snapshot from the WooCommerce cart.
	 *
	 * @param object $cart WooCommerce cart object.
	 * @return array<string, mixed>
	 */
	private function snapshot( object $cart ): array {
		$items = method_exists( $cart, 'get_cart' ) ? $cart->get_cart() : array();
		return array(
			'count'       => method_exists( $cart, 'get_cart_contents_count' ) ? (int) $cart->get_cart_contents_count() : 0,
			'lines'       => $this->render_lines( is_array( $items ) ? $items : array() ),
			'subtotal'    => method_exists( $cart, 'get_cart_subtotal' ) ? (string) $cart->get_cart_subtotal() : '',
			'notices'     => $this->get_notices(),
			'errors'      => array(),
			'cartUrl'     => function_exists( 'wc_get_cart_url' ) ? (string) wc_get_cart_url() : '',
			'checkoutUrl' => function_exists( 'wc_get_checkout_url' ) ? (string) wc_get_checkout_url() : '',
		);
	}

	/**
	 * Render cart lines as a controlled server fragment.
	 *
	 * @param array<string, mixed> $items WooCommerce cart items.
	 * @return string
	 */
	private function render_lines( array $items ): string {
		if ( empty( $items ) ) {
			return '<p class="crs-cart__empty" role="status">' . esc_html__( 'Votre panier est vide.', 'restaurant-suite-core' ) . '</p>';
		}

		$html = '<ul class="crs-cart__lines">';
		foreach ( $items as $key => $item ) {
			$product  = is_array( $item ) ? ( $item['data'] ?? null ) : null;
			$quantity = is_array( $item ) ? max( 0, (int) ( $item['quantity'] ?? 0 ) ) : 0;
			if ( ! is_object( $product ) || $quantity < 1 || ! method_exists( $product, 'get_name' ) ) {
				continue;
			}

			$name  = (string) $product->get_name();
			$price = is_array( $item ) ? (string) ( $item['line_total'] ?? '' ) : '';
			$html .= '<li class="crs-cart__line" data-crs-cart-line data-cart-item-key="' . esc_attr( (string) $key ) . '">';
			$html .= '<span class="crs-cart__line-name">' . esc_html( $name ) . '</span>';
			$html .= '<span class="crs-cart__line-quantity"><button type="button" data-crs-cart-decrease aria-label="' . esc_attr__( 'Diminuer la quantité', 'restaurant-suite-core' ) . '">−</button><span data-crs-cart-quantity>' . esc_html( (string) $quantity ) . '</span><button type="button" data-crs-cart-increase aria-label="' . esc_attr__( 'Augmenter la quantité', 'restaurant-suite-core' ) . '">+</button></span>';
			$html .= '<span class="crs-cart__line-total">' . wp_kses_post( $price ) . '</span>';
			$html .= '<button type="button" class="crs-cart__remove" data-crs-cart-remove>' . esc_html__( 'Supprimer', 'restaurant-suite-core' ) . '</button></li>';
		}

		return $html . '</ul>';
	}

	/**
	 * Return WooCommerce notices without exposing raw request data.
	 *
	 * @return array<int, string>
	 */
	private function get_notices(): array {
		if ( ! function_exists( 'wc_get_notices' ) ) {
			return array();
		}

		$notices = wc_get_notices();
		if ( ! is_array( $notices ) ) {
			return array();
		}

		$output = array();
		foreach ( $notices as $group ) {
			if ( ! is_array( $group ) ) {
				continue;
			}
			foreach ( $group as $notice ) {
				if ( is_array( $notice ) && isset( $notice['notice'] ) ) {
					$output[] = wp_kses_post( (string) $notice['notice'] );
				}
			}
		}

		if ( function_exists( 'wc_clear_notices' ) ) {
			wc_clear_notices();
		}

		return $output;
	}
}
