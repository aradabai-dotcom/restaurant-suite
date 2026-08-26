<?php
/**
 * Menu integration controller.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS;

use CRS\Menu\MenuRenderer;

/**
 * Registers the menu shortcode, block, and optional Elementor widget.
 */
final class MenuController {

	/**
	 * Constructor.
	 *
	 * @param MenuRenderer $renderer Shared server-side renderer.
	 */
	public function __construct( private readonly MenuRenderer $renderer ) {
	}

	/**
	 * Register WordPress and Elementor hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_public_entry_points' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widget' ) );
	}

	/**
	 * Register the shortcode and server-rendered block.
	 *
	 * @return void
	 */
	public function register_public_entry_points(): void {
		add_shortcode( 'crs_menu', array( $this, 'render_shortcode' ) );

		if ( function_exists( 'register_block_type' ) ) {
			register_block_type(
				CRS_PLUGIN_DIR . 'src/Blocks/menu',
				array( 'render_callback' => array( $this, 'render_block' ) )
			);
		}
	}

	/**
	 * Render the dynamic block.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content    Fallback block content.
	 * @return string
	 */
	public function render_block( array $attributes = array(), string $content = '' ): string {
		unset( $content );
		return $this->renderer->render( $attributes );
	}

	/**
	 * Render the menu shortcode.
	 *
	 * @param array<string, mixed> $attributes Shortcode attributes.
	 * @return string
	 */
	public function render_shortcode( array $attributes = array() ): string {
		return $this->renderer->render( $attributes );
	}

	/**
	 * Register the optional Elementor widget.
	 *
	 * @param object $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function register_elementor_widget( object $widgets_manager ): void {
		if ( ! class_exists( 'Elementor\\Widget_Base' ) ) {
			return;
		}

		require_once CRS_PLUGIN_DIR . 'src/Integrations/class-elementormenuwidget.php';
		$widgets_manager->register( new \CRS\Integrations\ElementorMenuWidget( $this->renderer ) );
	}
}
