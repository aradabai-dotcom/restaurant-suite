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
	function register_rest_route( string $namespace, string $route, array $args = array() ): bool { return true; }
	function rest_url( string $path = '' ): string { return '/' . ltrim( $path, '/' ); }
	function wp_create_nonce( string $action = '-1' ): string { return 'stub-nonce'; }
	function wp_verify_nonce( string $nonce, string $action = '-1' ): int|false {
		return false === ( $GLOBALS['crs_test_nonce_valid'] ?? true ) ? false : 1;
	}
	function absint( mixed $value ): int { return abs( (int) $value ); }
	function sanitize_key( string $key ): string { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', $key ) ?? '' ); }
	function sanitize_text_field( string $text ): string { return trim( strip_tags( $text ) ); }
	function is_wp_error( mixed $value ): bool { return $value instanceof WP_Error; }
	function WC(): object { return $GLOBALS['crs_test_wc'] ?? (object) array(); }
	function wc_load_cart(): void {}
	function get_post_status( int $post_id ): string { return 'publish'; }
	function wc_get_product( int $product_id ): mixed {
		return $GLOBALS['crs_test_product'] ?? null;
	}
	function woocommerce_template_single_add_to_cart(): void { echo '<form class="variations_form" data-product_variations="[]"><select name="attribute_taille"><option value="petite">Petite</option></select></form>'; }
	function wp_enqueue_style( string $handle, string $src = '', array $deps = array(), string|bool|null $ver = false, string $media = 'all' ): void {}
	function wp_register_script( string $handle, string $src = '', array $deps = array(), string|bool|null $ver = false, array|bool $args = false ): bool { return true; }
	function wp_localize_script( string $handle, string $object_name, array $l10n ): bool { return true; }
	function wp_enqueue_script( string $handle ): void {}
	function __( string $text, string $domain = 'default' ): string { return $text; }
	function esc_html__( string $text, string $domain = 'default' ): string { return esc_html( $text ); }
	function esc_html( string $text ): string { return htmlspecialchars( $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ); }
	function esc_attr__( string $text, string $domain = 'default' ): string { return esc_attr( $text ); }
	function esc_attr( string $text ): string { return htmlspecialchars( $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ); }
	function esc_url( string $url ): string { return $url; }
	function wpautop( string $text ): string { return $text; }
	function wp_kses_post( string $html ): string { return $html; }
	/**
	 * @param array<string, mixed> $allowed_html
	 */
	function wp_kses( string $html, array $allowed_html ): string { return $html; }
	function add_query_arg( string $key, int|string $value, string $url ): string { return $url; }
	function wc_get_cart_url(): string { return 'https://example.test/cart/'; }
	function wc_get_checkout_url(): string { return 'https://example.test/checkout/'; }
	function wc_get_notices(): array { return $GLOBALS['crs_test_notices'] ?? array(); }
	function wc_clear_notices(): void { $GLOBALS['crs_test_notices'] = array(); }
}

namespace {
	class WP_REST_Request {
		/**
		 * @var array<string, string>
		 */
		private array $headers;
		/**
		 * @var array<string, mixed>
		 */
		private array $params;

		/**
		 * @param array<string, string> $headers
		 * @param array<string, mixed>  $params
		 */
		public function __construct( array $headers = array(), array $params = array() ) {
			$this->headers = $headers;
			$this->params  = $params;
		}

		public function get_header( string $key ): string { return (string) ( $this->headers[ $key ] ?? '' ); }
		public function get_param( string $key ): mixed { return $this->params[ $key ] ?? null; }
	}

	final class WP_REST_Response {
		public mixed $data;
		public int $status;

		public function __construct( mixed $data = null, int $status = 200 ) {
			$this->data   = $data;
			$this->status = $status;
		}
	}

	final class WP_Error {
		public string $code;
		public string $message;
		/**
		 * @var array<string, mixed>
		 */
		public array $data;

		/**
		 * @param array<string, mixed> $data
		 */
		public function __construct( string $code = '', string $message = '', array $data = array() ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}
	}

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
