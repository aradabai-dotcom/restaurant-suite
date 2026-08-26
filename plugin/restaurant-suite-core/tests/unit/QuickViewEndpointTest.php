<?php
/**
 * Quick View endpoint tests.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

use CRS\QuickView\QuickViewEndpoint;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 4) . '/stubs/wordpress-woocommerce-elementor.php';
require_once dirname(__DIR__, 2) . '/src/QuickView/class-quickviewendpoint.php';

/**
 * Covers the isolated Quick View endpoint contract.
 */
final class QuickViewEndpointTest extends TestCase {

	/**
	 * Reset global fixture state after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		unset( $GLOBALS['crs_test_product'], $GLOBALS['crs_test_nonce_valid'] );
		parent::tearDown();
	}

	/**
	 * A valid nonce is accepted by the endpoint permission callback.
	 *
	 * @return void
	 */
	public function testAcceptsValidQuickViewNonce(): void {
		$request = new WP_REST_Request( array( 'X-WP-Nonce' => 'stub-nonce' ) );

		self::assertTrue( ( new QuickViewEndpoint() )->check_permission( $request ) );
	}

	/**
	 * An absent WooCommerce product returns a not-available error.
	 *
	 * @return void
	 */
	public function testRejectsMissingProduct(): void {
		$request = new WP_REST_Request( array( 'X-WP-Nonce' => 'stub-nonce' ), array( 'product_id' => 99 ) );
		$result  = ( new QuickViewEndpoint() )->get_fragment( $request );

		self::assertInstanceOf( WP_Error::class, $result );
		self::assertSame( 'crs_product_not_available', $result->code );
		self::assertSame( 404, $result->data['status'] );
	}

	/**
	 * A published product is represented in an escaped server fragment.
	 *
	 * @return void
	 */
	public function testRendersPublishedProductFragment(): void {
		$GLOBALS['crs_test_product'] = new class() {
			public function get_id(): int { return 16; }
			public function get_name(): string { return '<Fixture Burger>'; }
			public function get_permalink(): string { return 'https://example.test/product/fixture'; }
			public function get_short_description(): string { return 'Description fixture'; }
			public function get_price_html(): string { return '<span>$12.50</span>'; }
			public function is_visible(): bool { return true; }
			public function is_purchasable(): bool { return true; }
			public function is_in_stock(): bool { return true; }
			public function get_image( string $size, array $args = array() ): string { return ''; }
		};
		$request = new WP_REST_Request( array( 'X-WP-Nonce' => 'stub-nonce' ), array( 'product_id' => 16 ) );
		$result  = ( new QuickViewEndpoint() )->get_fragment( $request );

		self::assertInstanceOf( WP_REST_Response::class, $result );
		self::assertSame( 200, $result->status );
		self::assertSame( 16, $result->data['product_id'] );
		self::assertStringContainsString( '&lt;Fixture Burger&gt;', $result->data['html'] );
		self::assertStringContainsString( 'Voir la fiche produit', $result->data['html'] );
	}
}
