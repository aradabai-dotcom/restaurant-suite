<?php
/**
 * Elementor menu widget integration.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Integrations;

use CRS\Menu\MenuArguments;
use CRS\Menu\MenuRenderer;

/**
 * Optional Elementor widget for the Restaurant Suite menu.
 */
final class ElementorMenuWidget extends \Elementor\Widget_Base {

	/**
	 * Constructor.
	 *
	 * @param MenuRenderer              $renderer Shared server-side renderer.
	 * @param array<string, mixed>      $data    Elementor widget data.
	 * @param array<string, mixed>|null $args    Elementor widget arguments.
	 */
	public function __construct( private readonly MenuRenderer $renderer, array $data = array(), ?array $args = null ) {
		parent::__construct( $data, $args );
	}

	/**
	 * Return the stable widget identifier.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'crs-menu';
	}

	/**
	 * Return the translated widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Restaurant Suite — Menu', 'restaurant-suite-core' );
	}

	/**
	 * Return the Elementor icon identifier.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-food-menu';
	}

	/**
	 * Return the Elementor widget categories.
	 *
	 * @return array<int, string>
	 */
	public function get_categories(): array {
		return array( 'general' );
	}

	/**
	 * Register the presentation controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'crs_menu_content',
			array(
				'label' => esc_html__( 'Menu', 'restaurant-suite-core' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'category',
			array(
				'label'       => esc_html__( 'Catégorie WooCommerce', 'restaurant-suite-core' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'burgers',
				'label_block' => true,
			)
		);
		$this->add_control(
			'limit',
			array(
				'label'   => esc_html__( 'Produits par page', 'restaurant-suite-core' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 100,
				'default' => 12,
			)
		);
		$this->add_control(
			'columns',
			array(
				'label'   => esc_html__( 'Colonnes', 'restaurant-suite-core' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'default' => '3',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the shared menu markup.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		echo wp_kses_post( $this->renderer->render( MenuArguments::normalize( $settings ) ) );
	}
}
