<?php
/**
 * Validates the V0.4 customer request without writing an order.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Orders;

/**
 * Applies server-side request and restaurant rule validation.
 */
final class OrderRequestValidator {

	/**
	 * Validate a request against server-owned settings and cart subtotal.
	 *
	 * @param array<string, mixed> $request         Untrusted request fields.
	 * @param array<string, mixed> $settings        Versioned restaurant settings.
	 * @param string               $cart_subtotal   WooCommerce subtotal.
	 * @return array<string, string> Stable field or rule errors.
	 */
	public function validate( array $request, array $settings, string $cart_subtotal ): array {
		$normalized = RestaurantSettings::normalize( $settings );
		$errors     = RestaurantSettings::errors( $settings );
		$errors     = array_merge( $errors, $this->validate_fields( $request ) );

		if ( ! $normalized['enabled'] ) {
			$errors['service'] = 'service_disabled';
		} elseif ( ! $normalized['restaurant_open'] ) {
			$errors['restaurant'] = 'restaurant_closed';
		}

		$method = $this->text( $request['fulfillment_method'] ?? '' );
		if ( 'pickup' === $method && ! $normalized['pickup_enabled'] ) {
			$errors['fulfillment_method'] = 'pickup_unavailable';
		}
		if ( 'delivery' === $method ) {
			if ( ! $normalized['delivery_enabled'] ) {
				$errors['fulfillment_method'] = 'delivery_unavailable';
			} elseif ( ! $this->zone_allowed( $request['delivery_zone'] ?? '', $normalized['delivery_zones'] ) ) {
				$errors['delivery_zone'] = 'delivery_zone_unavailable';
			}
			if ( '' === $this->text( $request['address'] ?? '' ) ) {
				$errors['address'] = 'address_required_for_delivery';
			}
		}

		if ( $this->money_cents( $cart_subtotal ) < $this->money_cents( $normalized['minimum_order'] ) ) {
			$errors['cart'] = 'minimum_order_not_reached';
		}
		return $errors;
	}

	/**
	 * Validate fields independent of restaurant state.
	 *
	 * @param array<string, mixed> $request Untrusted request fields.
	 * @return array<string, string>
	 */
	private function validate_fields( array $request ): array {
		$errors    = array();
		$forbidden = array( 'price', 'prices', 'subtotal', 'total', 'tax', 'taxes', 'fee', 'fees' );
		if ( array_intersect( $forbidden, array_keys( $request ) ) ) {
			$errors['request'] = 'client_price_fields_forbidden';
		}

		$key = $this->text( $request['idempotency_key'] ?? '' );
		if ( ! preg_match( '/^[A-Za-z0-9._:-]{16,128}$/', $key ) ) {
			$errors['idempotency_key'] = 'invalid_idempotency_key';
		}

		$name = $this->text( $request['customer_name'] ?? '' );
		if ( strlen( $name ) < 2 || strlen( $name ) > 120 ) {
			$errors['customer_name'] = 'invalid_customer_name';
		}

		$phone  = $this->text( $request['phone'] ?? '' );
		$digits = preg_replace( '/[^0-9]/', '', $phone );
		if ( strlen( $phone ) < 7 || strlen( $phone ) > 32 || ! is_string( $digits ) || strlen( $digits ) < 7 || ! preg_match( '/^\+?[0-9 ()-]{7,32}$/', $phone ) ) {
			$errors['phone'] = 'invalid_phone';
		}

		$method = $this->text( $request['fulfillment_method'] ?? '' );
		if ( ! in_array( $method, array( 'pickup', 'delivery' ), true ) ) {
			$errors['fulfillment_method'] = 'invalid_fulfillment_method';
		}

		foreach (
			array(
				'address'       => 500,
				'delivery_zone' => 120,
				'note'          => 500,
			) as $field => $max
		) {
			$value = $this->text( $request[ $field ] ?? '' );
			if ( strlen( $value ) > $max ) {
				$errors[ $field ] = 'field_too_long';
			}
		}
		return $errors;
	}

	/**
	 * Check a delivery zone against normalized settings.
	 *
	 * @param mixed              $value Allowed zone candidate.
	 * @param array<int, string> $zones Allowed zones.
	 */
	private function zone_allowed( mixed $value, array $zones ): bool {
		$zone    = strtolower( $this->text( $value ) );
		$allowed = array_map( 'strtolower', $zones );
		return '' !== $zone && in_array( $zone, $allowed, true );
	}

	/**
	 * Convert a decimal amount to integer cents for deterministic comparison.
	 *
	 * @param mixed $value Money value.
	 */
	private function money_cents( mixed $value ): int {
		if ( ! is_numeric( $value ) ) {
			return 0;
		}
		return (int) round( (float) $value * 100 );
	}

	/**
	 * Strip tags and trim a scalar text field.
	 *
	 * @param mixed $value Raw value.
	 */
	private function text( mixed $value ): string {
		return trim( wp_strip_all_tags( (string) $value ) );
	}
}
