<?php
/**
 * WordPress transient idempotency repository.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Orders;

/**
 * Persists simulation attempts through core WordPress transients only.
 */
final class WordPressTransientIdempotencyRepository implements IdempotencyRepository {

	private const EXPIRATION = DAY_IN_SECONDS;

	/**
	 * {@inheritDoc}
	 *
	 * @param string $key Idempotency key.
	 */
	public function find( string $key ): ?array {
		$value = get_transient( $this->transient_key( $key ) );
		if ( ! is_array( $value ) || ! isset( $value['fingerprint'], $value['result'] ) || ! is_array( $value['result'] ) ) {
			return null;
		}
		return array(
			'fingerprint' => (string) $value['fingerprint'],
			'result'      => $value['result'],
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param string               $key         Idempotency key.
	 * @param string               $fingerprint Request fingerprint.
	 * @param array<string, mixed> $result      Result without PII.
	 */
	public function save( string $key, string $fingerprint, array $result ): void {
		set_transient(
			$this->transient_key( $key ),
			array(
				'fingerprint' => $fingerprint,
				'result'      => $result,
			),
			self::EXPIRATION
		);
	}

	/**
	 * Hash the key to avoid storing a customer-provided value in a key name.
	 *
	 * @param string $key Idempotency key.
	 */
	private function transient_key( string $key ): string {
		return 'crs_sim_' . substr( hash( 'sha256', $key ), 0, 32 );
	}
}
