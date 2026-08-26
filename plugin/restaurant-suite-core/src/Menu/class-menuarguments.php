<?php
/**
 * Menu argument normalization.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Menu;

/**
 * Normalizes presentation arguments before a catalog query.
 */
final class MenuArguments {

	/**
	 * Normalize raw shortcode, block, or widget arguments.
	 *
	 * @param array<string, mixed> $raw Raw presentation arguments.
	 * @return array{category:string, limit:int, page:int, columns:int, orderby:string, order:string}
	 */
	public static function normalize( array $raw ): array {
		$raw_category = (string) ( $raw['category'] ?? '' );
		$category     = function_exists( 'sanitize_title' )
			? (string) sanitize_title( $raw_category )
			: strtolower( (string) preg_replace( '/[^a-z0-9_-]+/i', '-', $raw_category ) );

		$limit   = filter_var( $raw['limit'] ?? 12, FILTER_VALIDATE_INT );
		$page    = filter_var( $raw['page'] ?? 1, FILTER_VALIDATE_INT );
		$columns = filter_var( $raw['columns'] ?? 3, FILTER_VALIDATE_INT );
		$orderby = (string) ( $raw['orderby'] ?? 'menu_order' );
		$order   = strtoupper( (string) ( $raw['order'] ?? 'ASC' ) );

		$limit   = false === $limit ? 12 : (int) $limit;
		$page    = false === $page ? 1 : (int) $page;
		$columns = false === $columns ? 3 : (int) $columns;

		$allowed_orderby = array( 'menu_order', 'date', 'title', 'rand' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'menu_order';
		}

		return array(
			'category' => $category,
			'limit'    => max( 1, min( 100, $limit ) ),
			'page'     => max( 1, $page ),
			'columns'  => max( 1, min( 4, $columns ) ),
			'orderby'  => $orderby,
			'order'    => 'DESC' === $order ? 'DESC' : 'ASC',
		);
	}
}
