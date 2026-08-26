<?php
/**
 * Static-analysis stubs for the WordPress/WooCommerce/Elementor runtime.
 *
 * @package RestaurantSuite
 */

namespace {
	if ( ! defined( 'CRS_PLUGIN_URL' ) ) {
		define( 'CRS_PLUGIN_URL', '' );
	}
	if ( ! defined( 'CRS_PLUGIN_DIR' ) ) {
		define( 'CRS_PLUGIN_DIR', '' );
	}
	if ( ! defined( 'CRS_VERSION' ) ) {
		define( 'CRS_VERSION', '0.0.0' );
	}

	function add_action( string $hook, callable $callback, int $priority = 10, int $accepted_args = 1 ): void {}
	function add_shortcode( string $tag, callable $callback ): void {}
	function register_block_type( string $block_type, array $args = array() ): mixed { return null; }
	function wp_enqueue_style( string $handle, string $src = '', array $deps = array(), string|bool|null $ver = false, string $media = 'all' ): void {}
	function esc_html__( string $text, string $domain = 'default' ): string { return $text; }
	function esc_html( string $text ): string { return $text; }
	function esc_attr__( string $text, string $domain = 'default' ): string { return $text; }
	function esc_attr( string $text ): string { return $text; }
	function esc_url( string $url ): string { return $url; }
	function wpautop( string $text ): string { return $text; }
	function wp_kses_post( string $html ): string { return $html; }
	function add_query_arg( string $key, int|string $value, string $url ): string { return $url; }
	function wc_get_cart_url(): string { return ''; }
}

namespace {
	final class WC_Product_Query {
		/**
		 * @param array<string, mixed> $args
		 */
		public function __construct( array $args = array() ) {}

		/**
		 * @return array<int, object>
		 */
		public function get_products(): array { return array(); }
	}
}

namespace Elementor {
	abstract class Widget_Base {
		/**
		 * @param array<mixed>      $data
		 * @param array<mixed>|null $args
		 */
		public function __construct( array $data = array(), ?array $args = null ) {}
		protected function start_controls_section( string $id, array $args = array() ): void {}
		protected function add_control( string $id, array $args = array() ): void {}
		protected function end_controls_section(): void {}
		/**
		 * @return array<string, mixed>
		 */
		protected function get_settings_for_display(): array { return array(); }
	}

	final class Controls_Manager {
		public const TAB_CONTENT = 'content';
		public const TEXT = 'text';
		public const NUMBER = 'number';
		public const SELECT = 'select';
	}
}
