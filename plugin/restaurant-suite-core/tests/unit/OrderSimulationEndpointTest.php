<?php
/**
 * Order simulation endpoint tests.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

use CRS\Orders\InMemoryIdempotencyRepository;
use CRS\Orders\OrderRequestValidator;
use CRS\Orders\OrderSimulationEndpoint;
use CRS\Orders\OrderSimulationService;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 4) . '/stubs/wordpress-woocommerce-elementor.php';
require_once dirname(__DIR__, 4) . '/stubs/wordpress-v04.php';

/**
 * Covers the V0.4 simulation REST boundary without writing WooCommerce orders.
 */
final class OrderSimulationEndpointTest extends TestCase {

	private OrderSimulationService $service;

	protected function setUp(): void {
		$GLOBALS['crs_test_routes']              = array();
		$GLOBALS['crs_test_order_create_calls']  = 0;
		$GLOBALS['crs_test_nonce_valid']         = true;
		$GLOBALS['crs_test_transients']          = array();
		$GLOBALS['crs_test_options']            = array();
		$this->service                          = new OrderSimulationService(
			new OrderRequestValidator(),
			new InMemoryIdempotencyRepository()
		);
	}

	protected function tearDown(): void {
		unset(
			$GLOBALS['crs_test_routes'],
			$GLOBALS['crs_test_order_create_calls'],
			$GLOBALS['crs_test_nonce_valid'],
			$GLOBALS['crs_test_transients'],
			$GLOBALS['crs_test_options'],
			$GLOBALS['crs_test_wc'],
			$GLOBALS['crs_test_currency']
		);
		parent::tearDown();
	}

	public function testInvalidNonceReturnsForbiddenError(): void {
		$GLOBALS['crs_test_nonce_valid'] = false;
		$request = new WP_REST_Request( array( 'X-WP-Nonce' => 'invalid' ) );
		$result  = ( new OrderSimulationEndpoint() )->permission_check( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'crs_invalid_nonce', $result->code );
		self::assertSame( 403, $result->data['status'] );
	}

	public function testRouteIsRegisteredWithSimulationCallback(): void {
		$endpoint = new OrderSimulationEndpoint();
		$endpoint->register_route();
		$route = $GLOBALS['crs_test_routes'][0] ?? array();

		self::assertSame( 'crs/v1', $route['namespace'] );
		self::assertSame( '/order/simulate', $route['route'] );
		self::assertSame( 'POST', $route['args']['methods'] );
		self::assertSame( array( $endpoint, 'handle' ), $route['args']['callback'] );
		self::assertSame( array( $endpoint, 'permission_check' ), $route['args']['permission_callback'] );
	}

	public function testValidRequestUsesWooCommerceSnapshotAndStaysSimulationOnly(): void {
		$this->installCartFixture();
		$result = $this->endpoint()->handle( new WP_REST_Request( array(), $this->request() ) );

		self::assertInstanceOf( WP_REST_Response::class, $result );
		self::assertSame( 200, $result->status );
		self::assertSame( 'accepted_simulation', $result->data['decision'] );
		self::assertFalse( $result->data['would_create_order'] );
		self::assertSame( '25.00', $result->data['snapshot']['subtotal'] );
		self::assertSame( 16, $result->data['snapshot']['lines'][0]['product_id'] );
		self::assertSame( 0, $GLOBALS['crs_test_order_create_calls'] );
	}

	public function testClientPriceFieldIsRejectedWhileAbsentPriceFieldsAreAccepted(): void {
		$this->installCartFixture();
		$payload          = $this->request();
		$payload['price'] = '0.01';
		$rejected         = $this->endpoint()->handle( new WP_REST_Request( array(), $payload ) );
		$accepted         = $this->endpoint()->handle( new WP_REST_Request( array(), $this->request( 'endpoint-key-accepted' ) ) );

		self::assertSame( 422, $rejected->status );
		self::assertSame( 'client_price_fields_forbidden', $rejected->data['errors']['request'] );
		self::assertSame( 200, $accepted->status );
		self::assertSame( 'accepted_simulation', $accepted->data['decision'] );
	}

	public function testInvalidRequestIsRejected(): void {
		$this->installCartFixture();
		$payload                 = $this->request();
		$payload['customer_name'] = '';
		$result                  = $this->endpoint()->handle( new WP_REST_Request( array(), $payload ) );

		self::assertSame( 422, $result->status );
		self::assertSame( 'rejected', $result->data['decision'] );
		self::assertSame( 'invalid_customer_name', $result->data['errors']['customer_name'] );
	}

	public function testSameKeyAndContextIsReused(): void {
		$this->installCartFixture();
		$endpoint = $this->endpoint();
		$first    = $endpoint->handle( new WP_REST_Request( array(), $this->request() ) );
		$second   = $endpoint->handle( new WP_REST_Request( array(), $this->request() ) );

		self::assertFalse( $first->data['reused'] );
		self::assertTrue( $second->data['reused'] );
		self::assertSame( $first->data['attempt_id'], $second->data['attempt_id'] );
	}

	public function testSameKeyWithDifferentContextReturnsIdempotencyConflict(): void {
		$this->installCartFixture();
		$endpoint = $this->endpoint();
		$endpoint->handle( new WP_REST_Request( array(), $this->request() ) );
		$changed          = $this->request();
		$changed['note']  = 'Autre contexte de test';
		$result           = $endpoint->handle( new WP_REST_Request( array(), $changed ) );

		self::assertSame( 422, $result->status );
		self::assertSame( 'idempotency_conflict', $result->data['code'] );
		self::assertSame( 'idempotency_conflict', $result->data['errors']['idempotency_key'] );
		self::assertSame( 0, $GLOBALS['crs_test_order_create_calls'] );
	}

	public function testEmptyCartIsRejectedWithoutOrderCreation(): void {
		$cart = new class() {
			/** @return array<string, mixed> */
			public function get_cart(): array { return array(); }
			public function get_subtotal(): string { return '0.00'; }
		};
		$GLOBALS['crs_test_wc'] = (object) array( 'cart' => $cart );
		$result = $this->endpoint()->handle( new WP_REST_Request( array(), $this->request( 'endpoint-empty-cart' ) ) );

		self::assertSame( 422, $result->status );
		self::assertSame( 'cart_empty', $result->data['errors']['cart'] );
		self::assertSame( 0, $GLOBALS['crs_test_order_create_calls'] );
	}

	/**
	 * @param string $key Idempotency key.
	 * @return array<string, mixed>
	 */
	private function request( string $key = 'endpoint-key-20260827-01' ): array {
		return array(
			'idempotency_key'    => $key,
			'customer_name'      => 'Client Test',
			'phone'              => '+33123456789',
			'address'            => '',
			'fulfillment_method' => 'pickup',
			'delivery_zone'      => '',
			'note'               => 'Sans oignon',
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function settings(): array {
		return array(
			'enabled'          => true,
			'restaurant_open'  => true,
			'minimum_order'    => '10.00',
			'pickup_enabled'   => true,
			'delivery_enabled' => true,
			'delivery_fee'     => '2.50',
			'delivery_zones'   => array( 'Centre' ),
			'whatsapp_number'  => '+33123456789',
		);
	}

	private function endpoint(): OrderSimulationEndpoint {
		return new OrderSimulationEndpoint( $this->service, null, fn(): array => $this->settings() );
	}

	private function installCartFixture(): void {
		$product = new class() {
			public function get_id(): int { return 16; }
			public function get_name(): string { return '[CRS TEST] Burger Simple'; }
			public function is_purchasable(): bool { return true; }
			public function is_in_stock(): bool { return true; }
		};
		$cart = new class( $product ) {
			private object $product;
			public function __construct( object $product ) { $this->product = $product; }
			/** @return array<string, array<string, mixed>> */
			public function get_cart(): array {
				return array(
					'fixture-line' => array(
						'data'       => $this->product,
						'quantity'   => 1,
						'line_total' => '25.00',
					),
				);
			}
			public function get_subtotal(): string { return '25.00'; }
		};
		$GLOBALS['crs_test_wc'] = (object) array( 'cart' => $cart );
	}
}
