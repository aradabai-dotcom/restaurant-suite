<?php
/**
 * Contract registry for the Restaurant Suite plugin.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace RestaurantSuite\Contracts;

/**
 * Provides stable phase 0.0 contract identifiers.
 */
final class ContractRegistry {

	/**
	 * Stable order status identifiers.
	 *
	 * @var list<string>
	 */
	private const STATUS_IDS = array(
		'crs_pending_confirmation',
		'crs_confirmed',
		'crs_preparing',
		'crs_ready',
		'crs_out_for_delivery',
		'crs_completed',
		'crs_rejected',
	);

	/**
	 * Stable public event names.
	 *
	 * @var list<string>
	 */
	private const EVENT_NAMES = array(
		'crs:cart:add',
		'crs:cart:update',
		'crs:cart:remove',
		'crs:cart:refresh',
		'crs:quickview:open',
		'crs:quickview:close',
		'crs:order:created',
	);

	/**
	 * Return stable order status identifiers.
	 *
	 * @return list<string> Status identifiers.
	 */
	public static function status_ids(): array {
		return self::STATUS_IDS;
	}

	/**
	 * Return stable public event names.
	 *
	 * @return list<string> Event names.
	 */
	public static function event_names(): array {
		return self::EVENT_NAMES;
	}

	/**
	 * Determine whether a status identifier is registered.
	 *
	 * @param string $status Status identifier.
	 * @return bool Whether the status is registered.
	 */
	public static function has_status( string $status ): bool {
		return in_array( $status, self::STATUS_IDS, true );
	}

	/**
	 * Determine whether an event name is registered.
	 *
	 * @param string $event Event name.
	 * @return bool Whether the event is registered.
	 */
	public static function has_event( string $event ): bool {
		return in_array( $event, self::EVENT_NAMES, true );
	}
}
