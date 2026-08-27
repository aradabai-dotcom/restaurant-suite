<?php
/**
 * Restaurant settings tests.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace RestaurantSuite\Tests;

use CRS\Orders\RestaurantSettings;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 4) . '/stubs/wordpress-v04.php';

final class RestaurantSettingsTest extends TestCase {

	public function test_defaults_are_disabled_and_safe(): void {
		$defaults = RestaurantSettings::defaults();
		self::assertFalse( $defaults['enabled'] );
		self::assertFalse( $defaults['restaurant_open'] );
		self::assertSame( '0.00', $defaults['minimum_order'] );
		self::assertSame( 1, $defaults['schema_version'] );
	}

	public function test_normalize_bounds_money_zones_and_phone(): void {
		$normalized = RestaurantSettings::normalize(
			array(
				'enabled'          => 'on',
				'restaurant_open'  => 'true',
				'minimum_order'    => '12.5',
				'delivery_enabled' => 1,
				'delivery_zones'   => array( ' Centre ', '<b>Nord</b>', '' ),
				'whatsapp_number'  => '+33 (1) 23-45-67-89',
			)
		);
		self::assertTrue( $normalized['enabled'] );
		self::assertTrue( $normalized['restaurant_open'] );
		self::assertSame( '12.50', $normalized['minimum_order'] );
		self::assertSame( array( 'Centre', 'Nord' ), $normalized['delivery_zones'] );
		self::assertSame( '+33123456789', $normalized['whatsapp_number'] );
	}

	public function test_invalid_enabled_settings_are_reported(): void {
		$errors = RestaurantSettings::errors(
			array(
				'enabled'          => true,
				'minimum_order'    => '-1.00',
				'delivery_enabled' => true,
				'delivery_zones'   => array(),
				'whatsapp_number'  => '',
			)
		);
		self::assertSame( 'minimum_order_non_negative', $errors['minimum_order'] );
		self::assertSame( 'delivery_zones_required', $errors['delivery_zones'] );
		self::assertSame( 'whatsapp_number_required', $errors['whatsapp_number'] );
	}
}
