<?php
/**
 * V0.4 order simulation service.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Orders;

/**
 * Validates an order attempt and returns a non-persistent simulation result.
 */
final class OrderSimulationService {

	/**
	 * Build the simulation service with injectable validation and storage.
	 *
	 * @param OrderRequestValidator $validator  Request validator.
	 * @param IdempotencyRepository $repository Idempotency repository.
	 */
	public function __construct(
		private OrderRequestValidator $validator,
		private IdempotencyRepository $repository
	) {}

	/**
	 * Simulate an order without creating a WooCommerce order or changing stock.
	 *
	 * @param array<string, mixed> $request  Untrusted request fields.
	 * @param array<string, mixed> $settings Versioned restaurant settings.
	 * @param array<string, mixed> $snapshot Server-owned WooCommerce snapshot.
	 * @return array<string, mixed>
	 */
	public function simulate( array $request, array $settings, array $snapshot ): array {
		$key         = (string) ( $request['idempotency_key'] ?? '' );
		$normalized  = $this->normalize_snapshot( $snapshot );
		$fingerprint = $this->fingerprint( $request, $normalized );
		$attempt_id  = substr( hash( 'sha256', 'crs-attempt|' . $key ), 0, 16 );
		$existing    = $this->repository->find( $key );

		if ( null !== $existing ) {
			if ( ! hash_equals( $existing['fingerprint'], $fingerprint ) ) {
				return array(
					'decision'   => 'rejected',
					'code'       => 'idempotency_conflict',
					'attempt_id' => $attempt_id,
					'reused'     => false,
					'errors'     => array(
						'idempotency_key' => 'idempotency_conflict',
					),
				);
			}
			$result           = $existing['result'];
			$result['reused'] = true;
			return $result;
		}

		$errors = $this->validator->validate( $request, $settings, $normalized['subtotal'] );
		if ( empty( $normalized['lines'] ) ) {
			$errors['cart'] = 'cart_empty';
		} elseif ( ! $normalized['cart_valid'] ) {
			$errors['cart'] = 'cart_snapshot_invalid';
		}
		if ( $errors ) {
			$result = array(
				'decision'   => 'rejected',
				'code'       => 'validation_failed',
				'attempt_id' => $attempt_id,
				'reused'     => false,
				'errors'     => $errors,
			);
			$this->repository->save( $key, $fingerprint, $result );
			return $result;
		}

		$method = trim( wp_strip_all_tags( (string) ( $request['fulfillment_method'] ?? '' ) ) );
		$fee    = 'delivery' === $method ? $this->money( $settings['delivery_fee'] ?? '0.00' ) : '0.00';
		$total  = $this->money_from_cents( $this->money_cents( $normalized['subtotal'] ) + $this->money_cents( $fee ) );
		$result = array(
			'decision'           => 'accepted_simulation',
			'code'               => 'simulation_only',
			'attempt_id'         => $attempt_id,
			'reused'             => false,
			'would_create_order' => false,
			'status'             => 'crs_pending_confirmation',
			'snapshot'           => array(
				'lines'        => $normalized['lines'],
				'subtotal'     => $normalized['subtotal'],
				'delivery_fee' => $fee,
				'total'        => $total,
				'currency'     => $normalized['currency'],
			),
			'errors'             => array(),
		);
		$this->repository->save( $key, $fingerprint, $result );
		return $result;
	}

	/**
	 * Normalize only server-owned snapshot fields.
	 *
	 * @param array<string, mixed> $snapshot Raw server snapshot.
	 * @return array{lines:array<int,array<string,mixed>>,subtotal:string,currency:string,cart_valid:bool}
	 */
	private function normalize_snapshot( array $snapshot ): array {
		$lines      = array();
		$cart_valid = false !== ( $snapshot['cart_valid'] ?? true );
		$raw_lines  = is_array( $snapshot['lines'] ?? null ) ? $snapshot['lines'] : array();
		foreach ( $raw_lines as $line ) {
			if ( ! is_array( $line ) ) {
				continue;
			}
			$product_id = max( 0, (int) ( $line['product_id'] ?? 0 ) );
			if ( 0 === $product_id ) {
				$cart_valid = false;
			}
			$lines[] = array(
				'key'          => substr( hash( 'sha256', (string) ( $line['key'] ?? '' ) ), 0, 16 ),
				'product_id'   => $product_id,
				'variation_id' => max( 0, (int) ( $line['variation_id'] ?? 0 ) ),
				'name'         => trim( wp_strip_all_tags( (string) ( $line['name'] ?? '' ) ) ),
				'quantity'     => max( 1, min( 999, (int) ( $line['quantity'] ?? 1 ) ) ),
				'line_total'   => $this->money( $line['line_total'] ?? '0.00' ),
			);
		}
		$currency = strtoupper( preg_replace( '/[^A-Z]/', '', (string) ( $snapshot['currency'] ?? 'USD' ) ) ?? 'USD' );
		if ( '' === $currency ) {
			$currency = 'USD';
		}
		return array(
			'lines'      => $lines,
			'subtotal'   => $this->money( $snapshot['subtotal'] ?? '0.00' ),
			'currency'   => substr( $currency, 0, 3 ),
			'cart_valid' => $cart_valid,
		);
	}

	/**
	 * Hash request context and server snapshot without storing plaintext PII.
	 *
	 * @param array<string, mixed> $request  Request context.
	 * @param array<string, mixed> $snapshot Server snapshot.
	 */
	private function fingerprint( array $request, array $snapshot ): string {
		$context = array(
			'customer_name'      => (string) ( $request['customer_name'] ?? '' ),
			'phone'              => (string) ( $request['phone'] ?? '' ),
			'address'            => (string) ( $request['address'] ?? '' ),
			'fulfillment_method' => (string) ( $request['fulfillment_method'] ?? '' ),
			'delivery_zone'      => (string) ( $request['delivery_zone'] ?? '' ),
			'note'               => (string) ( $request['note'] ?? '' ),
			'snapshot'           => $snapshot,
		);
		return hash( 'sha256', (string) wp_json_encode( $context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	}

	/**
	 * Normalize money to two decimals.
	 *
	 * @param mixed $value Money value.
	 */
	private function money( mixed $value ): string {
		if ( ! is_numeric( $value ) ) {
			return '0.00';
		}
		return number_format( max( 0, (float) $value ), 2, '.', '' );
	}

	/**
	 * Format integer cents as a decimal amount.
	 *
	 * @param int $cents Amount in cents.
	 */
	private function money_from_cents( int $cents ): string {
		return number_format( max( 0, $cents ) / 100, 2, '.', '' );
	}

	/**
	 * Convert money to integer cents.
	 *
	 * @param mixed $value Money value.
	 */
	private function money_cents( mixed $value ): int {
		if ( ! is_numeric( $value ) ) {
			return 0;
		}
		return (int) round( (float) $value * 100 );
	}
}
