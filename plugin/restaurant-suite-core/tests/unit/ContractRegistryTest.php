<?php
/**
 * Tests for the contract registry.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace RestaurantSuite\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RestaurantSuite\Contracts\ContractRegistry;

/**
 * Ensures phase 0.0 identifiers remain stable.
 */
final class ContractRegistryTest extends TestCase {

	/**
	 * Test stable status identifiers.
	 */
	public function testStatusIdentifiersRemainStable(): void {
		self::assertSame(
			array(
				'crs_pending_confirmation',
				'crs_confirmed',
				'crs_preparing',
				'crs_ready',
				'crs_out_for_delivery',
				'crs_completed',
				'crs_rejected',
			),
			ContractRegistry::status_ids()
		);
	}

	/**
	 * Test stable event names.
	 */
	public function testEventNamesRemainStable(): void {
		self::assertSame(
			array(
				'crs:cart:add',
				'crs:cart:update',
				'crs:cart:remove',
				'crs:cart:refresh',
				'crs:quickview:open',
				'crs:quickview:close',
				'crs:order:created',
			),
			ContractRegistry::event_names()
		);
	}

	/**
	 * Test rejection of unknown identifiers.
	 */
	public function testUnknownIdentifiersAreRejected(): void {
		self::assertTrue( ContractRegistry::has_status( 'crs_ready' ) );
		self::assertFalse( ContractRegistry::has_status( 'ready' ) );
		self::assertTrue( ContractRegistry::has_event( 'crs:cart:refresh' ) );
		self::assertFalse( ContractRegistry::has_event( 'cart:refresh' ) );
	}
}
