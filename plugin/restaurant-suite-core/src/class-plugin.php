<?php
/**
 * Plugin bootstrap.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS;

use CRS\Menu\MenuQuery;
use CRS\Menu\MenuRenderer;
use CRS\Cart\CartEndpoint;
use CRS\QuickView\QuickViewEndpoint;

/**
 * Boots the Restaurant Suite plugin.
 */
final class Plugin {

	/**
	 * Register the plugin services.
	 *
	 * @return void
	 */
	public static function boot(): void {
		$renderer   = new MenuRenderer( new MenuQuery() );
		$controller = new MenuController( $renderer );
		$controller->register();
		( new QuickViewEndpoint() )->register();
		( new CartEndpoint() )->register();
	}
}
