<?php
/**
 * WordPress transient idempotency repository tests.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace RestaurantSuite\Tests;

use CRS\Orders\WordPressTransientIdempotencyRepository;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 4) . '/stubs/wordpress-v04.php';

final class WordPressTransientIdempotencyRepositoryTest extends TestCase {

	protected function tearDown(): void {
		$GLOBALS['crs_test_transients'] = array();
		parent::tearDown();
	}

	public function test_saved_record_can_be_found_by_the_same_key(): void {
		$repository = new WordPressTransientIdempotencyRepository();
		$result     = array( 'decision' => 'accepted_simulation', 'attempt_id' => 'abc123' );
		$repository->save( 'simulation-key-20260827-02', 'fingerprint', $result );
		$found = $repository->find( 'simulation-key-20260827-02' );
		self::assertIsArray( $found );
		self::assertSame( 'fingerprint', $found['fingerprint'] );
		self::assertSame( $result, $found['result'] );
	}

	public function test_unknown_key_returns_null(): void {
		$repository = new WordPressTransientIdempotencyRepository();
		self::assertNull( $repository->find( 'simulation-key-20260827-03' ) );
	}
}
