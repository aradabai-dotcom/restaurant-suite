<?php
/**
 * Cart endpoint tests.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

use CRS\Cart\CartEndpoint;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 4) . '/stubs/wordpress-woocommerce-elementor.php';
require_once dirname(__DIR__, 2) . '/src/Cart/class-cartendpoint.php';

/**
 * Covers the isolated Cart Drawer endpoint contract.
 */
final class CartEndpointTest extends TestCase {

	/**
	 * Reset global fixture state after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		unset( $GLOBALS['crs_test_wc'], $GLOBALS['crs_test_nonce_valid'], $GLOBALS['crs_test_notices'] );
		parent::tearDown();
	}

	/**
	 * A valid REST nonce is accepted and an invalid one is rejected.
	 *
	 * @return void
	 */
	public function testChecksRestNonce(): void {
		$request = new WP_REST_Request( array( 'X-WP-Nonce' => 'stub-nonce' ) );
		self::assertTrue( ( new CartEndpoint() )->check_permission( $request ) );

		$GLOBALS['crs_test_nonce_valid'] = false;
		self::assertFalse( ( new CartEndpoint() )->check_permission( $request ) );
	}

	/**
	 * Refresh returns one normalized WooCommerce snapshot with escaped line data.
	 *
	 * @return void
	 */
	public function testRefreshReturnsNormalizedSnapshot(): void {
		$product = new class() {
			public function get_name(): string { return '<Fixture Burger>'; }
		};
		$cart = new class( $product ) {
			/** @var array<string, array<string, mixed>> */
			public array $items;
			public function __construct( object $product ) {
				$this->items = array( 'line-1' => array( 'data' => $product, 'quantity' => 2, 'line_total' => '<span>$25.00</span>' ) );
			}
			/** @return array<string, array<string, mixed>> */
			public function get_cart(): array { return $this->items; }
			public function get_cart_contents_count(): int { return 2; }
			public function get_cart_subtotal(): string { return '<span>$25.00</span>'; }
			public function calculate_totals(): void {}
		};
		$GLOBALS['crs_test_wc'] = (object) array( 'cart' => $cart );
		$GLOBALS['crs_test_notices'] = array( 'notice' => array( array( 'notice' => 'Fixture notice' ) ) );

		$result = ( new CartEndpoint() )->handle( new WP_REST_Request( array(), array( 'action' => 'refresh' ) ) );

		self::assertInstanceOf( WP_REST_Response::class, $result );
		self::assertSame( 200, $result->status );
		self::assertSame( 2, $result->data['count'] );
		self::assertStringContainsString( '&lt;Fixture Burger&gt;', $result->data['lines'] );
		self::assertStringContainsString( 'Fixture notice', $result->data['notices'][0] );
		self::assertSame( 'https://example.test/checkout/', $result->data['checkoutUrl'] );
	}

	/**
	 * Add, update and remove delegate to WooCommerce's cart API.
	 *
	 * @return void
	 */
	public function testMutationsUseWooCommerceCart(): void {
		$product = new class() {
			public function get_name(): string { return 'Fixture Tacos'; }
		};
		$cart = new class( $product ) {
			/** @var array<string, array<string, mixed>> */
			public array $items = array();
			private object $product;
			public function __construct( object $product ) { $this->product = $product; }
			public function add_to_cart( int $product_id, int $quantity, int $variation_id, array $variation ): string {
				$this->items['new-line'] = array( 'data' => $this->product, 'quantity' => $quantity, 'line_total' => '$12.50' );
				return 'new-line';
			}
			public function set_quantity( string $key, int $quantity, bool $refresh ): bool {
				if ( 0 === $quantity ) { unset( $this->items[ $key ] ); } elseif ( isset( $this->items[ $key ] ) ) { $this->items[ $key ]['quantity'] = $quantity; }
				return true;
			}
			public function remove_cart_item( string $key ): bool { unset( $this->items[ $key ] ); return true; }
			/** @return array<string, array<string, mixed>> */
			public function get_cart(): array { return $this->items; }
			public function get_cart_contents_count(): int { return array_sum( array_map( static fn ( array $item ): int => (int) $item['quantity'], $this->items ) ); }
			public function get_cart_subtotal(): string { return '$12.50'; }
			public function calculate_totals(): void {}
		};
		$GLOBALS['crs_test_wc'] = (object) array( 'cart' => $cart );
		$endpoint = new CartEndpoint();

		$add = $endpoint->handle( new WP_REST_Request( array(), array( 'action' => 'add', 'product_id' => 16, 'quantity' => 2, 'variation' => array( 'attribute_taille' => 'Grande', '<bad>' => '<script>' ) ) ) );
		self::assertInstanceOf( WP_REST_Response::class, $add );
		self::assertSame( 2, $add->data['count'] );

		$update = $endpoint->handle( new WP_REST_Request( array(), array( 'action' => 'update', 'cart_item_key' => 'new-line', 'quantity' => 3 ) ) );
		self::assertInstanceOf( WP_REST_Response::class, $update );
		self::assertSame( 3, $update->data['count'] );

		$remove = $endpoint->handle( new WP_REST_Request( array(), array( 'action' => 'remove', 'cart_item_key' => 'new-line' ) ) );
		self::assertInstanceOf( WP_REST_Response::class, $remove );
		self::assertSame( 0, $remove->data['count'] );
		self::assertStringContainsString( 'Votre panier est vide', $remove->data['lines'] );
	}
}
