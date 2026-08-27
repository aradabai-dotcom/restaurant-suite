<?php
/**
 * Restaurant settings value object.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Orders;

/**
 * Normalizes and validates versioned restaurant settings.
 */
final class RestaurantSettings {

	public const SCHEMA_VERSION = 1;

	/**
	 * Return safe defaults for the versioned settings option.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'enabled'          => false,
			'restaurant_open'  => false,
			'minimum_order'    => '0.00',
			'pickup_enabled'   => true,
			'delivery_enabled' => false,
			'delivery_fee'     => '0.00',
			'delivery_zones'   => array(),
			'whatsapp_number'  => '',
			'schema_version'   => self::SCHEMA_VERSION,
		);
	}

	/**
	 * Normalize an untrusted settings array without persisting it.
	 *
	 * @param array<string, mixed> $settings Raw settings.
	 * @return array<string, mixed>
	 */
	public static function normalize( array $settings ): array {
		$defaults = self::defaults();
		return array(
			'enabled'          => self::to_bool( $settings['enabled'] ?? $defaults['enabled'] ),
			'restaurant_open'  => self::to_bool( $settings['restaurant_open'] ?? $defaults['restaurant_open'] ),
			'minimum_order'    => self::money( $settings['minimum_order'] ?? $defaults['minimum_order'] ),
			'pickup_enabled'   => self::to_bool( $settings['pickup_enabled'] ?? $defaults['pickup_enabled'] ),
			'delivery_enabled' => self::to_bool( $settings['delivery_enabled'] ?? $defaults['delivery_enabled'] ),
			'delivery_fee'     => self::money( $settings['delivery_fee'] ?? $defaults['delivery_fee'] ),
			'delivery_zones'   => self::zones( $settings['delivery_zones'] ?? $defaults['delivery_zones'] ),
			'whatsapp_number'  => self::phone( $settings['whatsapp_number'] ?? $defaults['whatsapp_number'] ),
			'schema_version'   => self::SCHEMA_VERSION,
		);
	}

	/**
	 * Validate settings and return stable field errors.
	 *
	 * @param array<string, mixed> $settings Raw settings.
	 * @return array<string, string>
	 */
	public static function errors( array $settings ): array {
		$errors     = array();
		$normalized = self::normalize( $settings );
		if ( is_numeric( $settings['minimum_order'] ?? null ) && (float) $settings['minimum_order'] < 0 ) {
			$errors['minimum_order'] = 'minimum_order_non_negative';
		}
		if ( is_numeric( $settings['delivery_fee'] ?? null ) && (float) $settings['delivery_fee'] < 0 ) {
			$errors['delivery_fee'] = 'delivery_fee_non_negative';
		}
		if ( $normalized['delivery_enabled'] && empty( $normalized['delivery_zones'] ) ) {
			$errors['delivery_zones'] = 'delivery_zones_required';
		}
		if ( $normalized['enabled'] && '' === $normalized['whatsapp_number'] ) {
			$errors['whatsapp_number'] = 'whatsapp_number_required';
		}
		return $errors;
	}

	/**
	 * Normalize a boolean represented by JSON or form data.
	 *
	 * @param mixed $value Raw value.
	 */
	private static function to_bool( mixed $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}
		return in_array( strtolower( trim( (string) $value ) ), array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Normalize a non-negative money value to two decimal places.
	 *
	 * @param mixed $value Raw value.
	 */
	private static function money( mixed $value ): string {
		if ( ! is_numeric( $value ) ) {
			return '0.00';
		}
		return number_format( max( 0, (float) $value ), 2, '.', '' );
	}

	/**
	 * Normalize a bounded list of delivery zones.
	 *
	 * @param mixed $value Raw value.
	 * @return array<int, string>
	 */
	private static function zones( mixed $value ): array {
		if ( ! is_array( $value ) ) {
			return array();
		}
		$zones = array();
		foreach ( array_slice( $value, 0, 100 ) as $zone ) {
			$zone = trim( wp_strip_all_tags( (string) $zone ) );
			if ( '' !== $zone && strlen( $zone ) <= 120 ) {
				$zones[] = $zone;
			}
		}
		return array_values( array_unique( $zones ) );
	}

	/**
	 * Keep only a normalized phone string for server-side use.
	 *
	 * @param mixed $value Raw value.
	 */
	private static function phone( mixed $value ): string {
		$phone = preg_replace( '/[^0-9+]/', '', (string) $value );
		return is_string( $phone ) ? substr( $phone, 0, 32 ) : '';
	}
}
