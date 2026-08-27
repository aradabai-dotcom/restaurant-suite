<?php
/**
 * Order simulation renderer tests.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

use CRS\Orders\OrderSimulationRenderer;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 4) . '/stubs/wordpress-woocommerce-elementor.php';
require_once dirname(__DIR__, 4) . '/stubs/wordpress-v04.php';

/**
 * Covers the server-rendered simulation form boundary.
 */
final class OrderSimulationRendererTest extends TestCase {

	public function testRenderIsExplicitlySimulationOnly(): void {
		$html = ( new OrderSimulationRenderer() )->render();

		self::assertStringContainsString( 'data-crs-order-simulation', $html );
		self::assertStringContainsString( 'Aucune commande, modification de stock, paiement ou message ne sera envoyé.', $html );
		self::assertStringContainsString( 'data-crs-order-simulation-submit', $html );
		self::assertStringNotContainsString( 'crs:order:created', $html );
	}
}
