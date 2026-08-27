<?php
/**
 * In-memory idempotency repository for simulation and tests.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Orders;

/**
 * Keeps attempts in memory and is never a production persistence adapter.
 */
final class InMemoryIdempotencyRepository implements IdempotencyRepository {

	/**
	 * In-memory records keyed by idempotency key.
	 *
	 * @var array<string, array{fingerprint:string,result:array<string,mixed>}>
	 */
	private array $records = array();

	/**
	 * {@inheritDoc}
	 *
	 * @param string $key Idempotency key.
	 */
	public function find( string $key ): ?array {
		return $this->records[ $key ] ?? null;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param string               $key         Idempotency key.
	 * @param string               $fingerprint Request fingerprint.
	 * @param array<string, mixed> $result      Result without PII.
	 */
	public function save( string $key, string $fingerprint, array $result ): void {
		$this->records[ $key ] = array(
			'fingerprint' => $fingerprint,
			'result'      => $result,
		);
	}
}
