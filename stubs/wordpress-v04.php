<?php
/**
 * Focused WordPress stubs for V0.4 simulation tests and static analysis.
 *
 * @package RestaurantSuite
 */

namespace {
	if ( ! defined( 'DAY_IN_SECONDS' ) ) {
		define( 'DAY_IN_SECONDS', 86400 );
	}

	function wp_strip_all_tags( string $text ): string {
		return trim( preg_replace( '/<[^>]*>/', '', $text ) ?? '' );
	}

	function wp_json_encode( mixed $value, int $flags = 0 ): string|false {
		return json_encode( $value, $flags );
	}

	function get_transient( string $key ): mixed {
		return $GLOBALS['crs_test_transients'][ $key ] ?? false;
	}

	function set_transient( string $key, mixed $value, int $expiration = 0 ): bool {
		$GLOBALS['crs_test_transients'][ $key ] = $value;
		return true;
	}

	function get_option( string $option, mixed $default = false ): mixed {
		return $GLOBALS['crs_test_options'][ $option ] ?? $default;
	}

	function get_woocommerce_currency(): string {
		return (string) ( $GLOBALS['crs_test_currency'] ?? 'USD' );
	}
}
