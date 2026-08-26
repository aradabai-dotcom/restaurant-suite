<?php
/**
 * WooCommerce catalog query service.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Menu;

/**
 * Builds and executes product queries through WooCommerce APIs.
 */
final class MenuQuery {

	/**
	 * Build arguments for WC_Product_Query.
	 *
	 * @param array{category:string, limit:int, page:int, columns:int, orderby:string, order:string} $args Normalized menu arguments.
	 * @return array<string, mixed>
	 */
	public static function build_args( array $args ): array {
		$query = array(
			'status'     => 'publish',
			'limit'      => $args['limit'],
			'page'       => $args['page'],
			'paginate'   => false,
			'return'     => 'objects',
			'orderby'    => $args['orderby'],
			'order'      => $args['order'],
			'visibility' => 'catalog',
		);

		if ( '' !== $args['category'] ) {
			$query['category'] = array( $args['category'] );
		}

		return $query;
	}

	/**
	 * Execute the catalog query.
	 *
	 * @param array{category:string, limit:int, page:int, columns:int, orderby:string, order:string} $args Normalized menu arguments.
	 * @return array<int, object>
	 */
	public function get_products( array $args ): array {
		if ( ! class_exists( 'WC_Product_Query' ) ) {
			return array();
		}

		$products = ( new \WC_Product_Query( self::build_args( $args ) ) )->get_products();

		return (array) $products;
	}
}
