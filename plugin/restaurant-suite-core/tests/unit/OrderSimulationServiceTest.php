<?php
/**
 * Order simulation service tests.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace RestaurantSuite\Tests;

use CRS\Orders\InMemoryIdempotencyRepository;
use CRS\Orders\OrderRequestValidator;
use CRS\Orders\OrderSimulationService;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 4) . '/stubs/wordpress-woocommerce-elementor.php';
require_once dirname(__DIR__, 4) . '/stubs/wordpress-v04.php';

final class OrderSimulationServiceTest extends TestCase {

	private OrderSimulationService $service;

	protected function setUp(): void {
		$this->service = new OrderSimulationService(
			new OrderRequestValidator(),
			new InMemoryIdempotencyRepository()
		);
	}

	public function test_valid_request_is_accepted_without_creating_an_order(): void {
		$result = $this->service->simulate( $this->request(), $this->settings(), $this->snapshot() );
		self::assertSame( 'accepted_simulation', $result['decision'] );
		self::assertFalse( $result['would_create_order'] );
		self::assertSame( '25.00', $result['snapshot']['total'] );
		self::assertSame( 1, count( $result['snapshot']['lines'] ) );
	}

	public function test_delivery_fee_is_recalculated_from_settings(): void {
		$request = $this->request();
		$request['fulfillment_method'] = 'delivery';
		$request['address'] = '1 rue de Test';
		$request['delivery_zone'] = 'Centre';
		$result = $this->service->simulate( $request, $this->settings(), $this->snapshot() );
		self::assertSame( 'accepted_simulation', $result['decision'] );
		self::assertSame( '2.50', $result['snapshot']['delivery_fee'] );
		self::assertSame( '27.50', $result['snapshot']['total'] );
	}

	public function test_same_key_and_same_context_reuses_the_result(): void {
		$request = $this->request();
		$first = $this->service->simulate( $request, $this->settings(), $this->snapshot() );
		$second = $this->service->simulate( $request, $this->settings(), $this->snapshot() );
		self::assertFalse( $first['reused'] );
		self::assertTrue( $second['reused'] );
		self::assertSame( $first['attempt_id'], $second['attempt_id'] );
		self::assertSame( $first['snapshot'], $second['snapshot'] );
	}

	public function test_same_key_with_changed_context_is_rejected(): void {
		$request = $this->request();
		$this->service->simulate( $request, $this->settings(), $this->snapshot() );
		$request['note'] = 'Avec supplément de test';
		$result = $this->service->simulate( $request, $this->settings(), $this->snapshot() );
		self::assertSame( 'idempotency_conflict', $result['code'] );
		self::assertSame( 'idempotency_conflict', $result['errors']['idempotency_key'] );
	}

	public function test_invalid_request_is_rejected_and_replayable(): void {
		$request = $this->request();
		$request['fulfillment_method'] = 'delivery';
		$result = $this->service->simulate( $request, $this->settings(), $this->snapshot() );
		$replay = $this->service->simulate( $request, $this->settings(), $this->snapshot() );
		self::assertSame( 'rejected', $result['decision'] );
		self::assertSame( 'address_required_for_delivery', $result['errors']['address'] );
		self::assertTrue( $replay['reused'] );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function request(): array {
		return array(
			'idempotency_key'    => 'simulation-key-20260827-01',
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

	/**
	 * @return array<string, mixed>
	 */
	private function snapshot(): array {
		return array(
			'currency' => 'USD',
			'subtotal' => '25.00',
			'lines'    => array(
				array(
					'key'          => 'server-cart-key',
					'product_id'   => 16,
					'variation_id' => 0,
					'name'        => '[CRS TEST] Burger Simple',
					'quantity'    => 1,
					'line_total'  => '25.00',
				),
			),
		);
	}
}
