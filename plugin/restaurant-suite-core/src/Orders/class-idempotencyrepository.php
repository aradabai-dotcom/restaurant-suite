<?php
/**
 * Idempotency repository contract.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Orders;

/**
 * Stores only non-sensitive idempotency fingerprints and simulation results.
 */
interface IdempotencyRepository {

	/**
	 * Find a previous attempt by its client-provided key.
	 *
	 * @param string $key Idempotency key.
	 * @return array{fingerprint:string,result:array<string,mixed>}|null
	 */
	public function find( string $key ): ?array;

	/**
	 * Persist a non-sensitive result for the current process or adapter.
	 *
	 * @param string               $key         Idempotency key.
	 * @param string               $fingerprint Request fingerprint.
	 * @param array<string, mixed> $result      Result without PII.
	 * @return void
	 */
	public function save( string $key, string $fingerprint, array $result ): void;
}
