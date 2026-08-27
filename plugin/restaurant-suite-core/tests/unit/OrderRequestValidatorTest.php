<?php
/**
 * Order request validator tests.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace RestaurantSuite\Tests;

use CRS\Orders\OrderRequestValidator;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 4) . '/stubs/wordpress-woocommerce-elementor.php';
require_once dirname(__DIR__, 4) . '/stubs/wordpress-v04.php';

final class OrderRequestValidatorTest extends TestCase {

	private OrderRequestValidator $validator;

	protected function setUp(): void {
		$this->validator = new OrderRequestValidator();
	}

	public function test_valid_pickup_request_passes_when_restaurant_is_open(): void {
		$errors = $this->validator->validate( $this->request(), $this->settings(), '25.00' );
		self::assertSame( array(), $errors );
	}

	public function test_closed_restaurant_is_rejected(): void {
		$settings = $this->settings();
		$settings['restaurant_open'] = false;
		$errors = $this->validator->validate( $this->request(), $settings, '25.00' );
		self::assertSame( 'restaurant_closed', $errors['restaurant'] );
	}

	public function test_delivery_requires_enabled_zone_and_address(): void {
		$request = $this->request();
		$request['fulfillment_method'] = 'delivery';
		$settings = $this->settings();
		$settings['delivery_enabled'] = true;
		$settings['delivery_zones'] = array( 'Centre' );
		$errors = $this->validator->validate( $request, $settings, '25.00' );
		self::assertSame( 'delivery_zone_unavailable', $errors['delivery_zone'] );
		self::assertSame( 'address_required_for_delivery', $errors['address'] );
	}

	public function test_minimum_order_is_checked_against_server_subtotal(): void {
		$settings = $this->settings();
		$settings['minimum_order'] = '30.00';
		$errors = $this->validator->validate( $this->request(), $settings, '25.00' );
		self::assertSame( 'minimum_order_not_reached', $errors['cart'] );
	}

	public function test_client_price_fields_are_rejected(): void {
		$request = $this->request();
		$request['total'] = '0.01';
		$errors = $this->validator->validate( $request, $this->settings(), '25.00' );
		self::assertSame( 'client_price_fields_forbidden', $errors['request'] );
	}

	public function test_invalid_identity_fields_are_reported(): void {
		$request = $this->request();
		$request['idempotency_key'] = 'short';
		$request['customer_name'] = 'x';
		$request['phone'] = 'not-a-phone';
		$errors = $this->validator->validate( $request, $this->settings(), '25.00' );
		self::assertSame( 'invalid_idempotency_key', $errors['idempotency_key'] );
		self::assertSame( 'invalid_customer_name', $errors['customer_name'] );
		self::assertSame( 'invalid_phone', $errors['phone'] );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function request(): array {
		return array(
			'idempotency_key'    => 'test-key-20260827-0001',
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
			'delivery_enabled' => false,
			'delivery_fee'     => '2.50',
			'delivery_zones'   => array(),
			'whatsapp_number'  => '+33123456789',
		);
	}
}
