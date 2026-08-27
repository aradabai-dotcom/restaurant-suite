<?php
/**
 * REST endpoint for non-persistent order simulation.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Orders;

/**
 * Exposes the V0.4 simulation contract without creating orders.
 */
final class OrderSimulationEndpoint {

	/**
	 * Callable that returns a server-owned cart snapshot.
	 *
	 * @var callable():array<string,mixed>
	 */
	private $snapshot_loader;

	/**
	 * Callable that returns versioned restaurant settings.
	 *
	 * @var callable():array<string,mixed>
	 */
	private $settings_loader;

	/**
	 * Build the simulation endpoint with injectable loaders for tests.
	 *
	 * @param OrderSimulationService|null $service         Simulation service.
	 * @param callable|null               $snapshot_loader Cart snapshot loader.
	 * @param callable|null               $settings_loader Settings loader.
	 */
	public function __construct(
		private ?OrderSimulationService $service = null,
		?callable $snapshot_loader = null,
		?callable $settings_loader = null
	) {
		$this->snapshot_loader = $snapshot_loader ?? fn(): array => $this->cart_snapshot();
		$this->settings_loader = $settings_loader ?? fn(): array => $this->restaurant_settings();
	}

	/**
	 * Register the public simulation route.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_route' ) );
	}

	/**
	 * Register the simulation route during the REST API bootstrap.
	 *
	 * @return void
	 */
	public function register_route(): void {
		register_rest_route(
			'crs/v1',
			'/order/simulate',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle' ),
				'permission_callback' => array( $this, 'permission_check' ),
			)
		);
	}

	/**
	 * Check the standard WordPress REST nonce.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return bool|\WP_Error
	 */
	public function permission_check( \WP_REST_Request $request ): bool|\WP_Error {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_Error( 'crs_invalid_nonce', 'Nonce REST invalide.', array( 'status' => 403 ) );
		}
		return true;
	}

	/**
	 * Handle a simulation request.
	 *
	 * @param \WP_REST_Request $request REST request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$service      = $this->service ?? new OrderSimulationService(
			new OrderRequestValidator(),
			new WordPressTransientIdempotencyRepository()
		);
		$request_data = array();
		foreach (
			array(
				'idempotency_key',
				'customer_name',
				'phone',
				'address',
				'fulfillment_method',
				'delivery_zone',
				'note',
				'price',
				'total',
				'subtotal',
			) as $field
		) {
			if ( $request->has_param( $field ) ) {
				$request_data[ $field ] = $request->get_param( $field );
			}
		}
		$result = $service->simulate(
			$request_data,
			call_user_func( $this->settings_loader ),
			call_user_func( $this->snapshot_loader )
		);
		$status = 'rejected' === ( $result['decision'] ?? '' ) ? 422 : 200;
		return new \WP_REST_Response( $result, $status );
	}

	/**
	 * Build a server-owned snapshot from the current WooCommerce cart.
	 *
	 * @return array<string, mixed>
	 */
	private function cart_snapshot(): array {
		if ( function_exists( 'wc_load_cart' ) ) {
			wc_load_cart();
		}
		$wc = WC();
		if ( ! isset( $wc->cart ) || ! is_object( $wc->cart ) || ! method_exists( $wc->cart, 'get_cart' ) ) {
			return array(
				'lines'      => array(),
				'subtotal'   => '0.00',
				'currency'   => 'USD',
				'cart_valid' => false,
			);
		}
		$items      = $wc->cart->get_cart();
		$lines      = array();
		$subtotal   = 0.0;
		$cart_valid = true;
		foreach ( is_array( $items ) ? $items : array() as $key => $item ) {
			if ( ! is_array( $item ) || ! isset( $item['data'] ) || ! is_object( $item['data'] ) ) {
				continue;
			}
			$product    = $item['data'];
			$product_id = method_exists( $product, 'get_id' ) ? (int) $product->get_id() : 0;
			if ( 0 === $product_id ) {
				$cart_valid = false;
			}
			if ( method_exists( $product, 'is_purchasable' ) && ! $product->is_purchasable() ) {
				$cart_valid = false;
			}
			if ( method_exists( $product, 'is_in_stock' ) && ! $product->is_in_stock() ) {
				$cart_valid = false;
			}
			$quantity   = max( 1, min( 999, (int) ( $item['quantity'] ?? 1 ) ) );
			$line_total = (float) ( $item['line_total'] ?? 0 );
			$subtotal  += $line_total;
			$lines[]    = array(
				'key'          => (string) $key,
				'product_id'   => $product_id,
				'variation_id' => (int) ( $item['variation_id'] ?? 0 ),
				'name'         => method_exists( $product, 'get_name' ) ? (string) $product->get_name() : '',
				'quantity'     => $quantity,
				'line_total'   => number_format( max( 0, $line_total ), 2, '.', '' ),
			);
		}
		if ( method_exists( $wc->cart, 'get_subtotal' ) ) {
			$subtotal = (float) $wc->cart->get_subtotal();
		}
		$currency = function_exists( 'get_woocommerce_currency' ) ? (string) get_woocommerce_currency() : 'USD';
		return array(
			'lines'      => $lines,
			'subtotal'   => number_format( max( 0, $subtotal ), 2, '.', '' ),
			'currency'   => $currency,
			'cart_valid' => $cart_valid,
		);
	}

	/**
	 * Read versioned restaurant settings without exposing sensitive fields.
	 *
	 * @return array<string, mixed>
	 */
	private function restaurant_settings(): array {
		if ( function_exists( 'get_option' ) ) {
			$value = get_option( 'crs_settings', RestaurantSettings::defaults() );
			return is_array( $value ) ? $value : RestaurantSettings::defaults();
		}
		return RestaurantSettings::defaults();
	}
}
