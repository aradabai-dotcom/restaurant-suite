<?php
/**
 * Server-rendered order simulation form.
 *
 * @package RestaurantSuite
 */

declare(strict_types=1);

namespace CRS\Orders;

/**
 * Registers and renders the strict simulation-only customer form.
 */
final class OrderSimulationRenderer {

	/**
	 * Register the public simulation shortcode.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_shortcode' ) );
	}

	/**
	 * Register the [crs_order_simulation] shortcode.
	 *
	 * @return void
	 */
	public function register_shortcode(): void {
		add_shortcode( 'crs_order_simulation', array( $this, 'render' ) );
	}

	/**
	 * Render the simulation-only form and enqueue its assets.
	 *
	 * @return string
	 */
	public function render(): string {
		$this->enqueue_assets();
		return '<section class="crs-order-simulation" data-crs-order-simulation aria-labelledby="crs-order-simulation-title">'
			. '<div class="crs-order-simulation__notice" role="note">'
			. '<strong>' . esc_html__( 'Simulation uniquement', 'restaurant-suite-core' ) . '</strong>'
			. '<span>' . esc_html__( 'Aucune commande, modification de stock, paiement ou message ne sera envoyé.', 'restaurant-suite-core' ) . '</span>'
			. '</div>'
			. '<h2 id="crs-order-simulation-title">' . esc_html__( 'Tester ma demande', 'restaurant-suite-core' ) . '</h2>'
			. '<form class="crs-order-simulation__form" data-crs-order-simulation-form>'
			. '<div class="crs-order-simulation__fields">'
			. '<p><label for="crs-simulation-name">' . esc_html__( 'Nom', 'restaurant-suite-core' ) . '</label><input id="crs-simulation-name" name="customer_name" type="text" autocomplete="name" required maxlength="120"></p>'
			. '<p><label for="crs-simulation-phone">' . esc_html__( 'Téléphone', 'restaurant-suite-core' ) . '</label><input id="crs-simulation-phone" name="phone" type="tel" autocomplete="tel" required maxlength="32"></p>'
			. '<p><label for="crs-simulation-method">' . esc_html__( 'Mode de retrait', 'restaurant-suite-core' ) . '</label><select id="crs-simulation-method" name="fulfillment_method" required><option value="pickup">' . esc_html__( 'Retrait sur place', 'restaurant-suite-core' ) . '</option><option value="delivery">' . esc_html__( 'Livraison', 'restaurant-suite-core' ) . '</option></select></p>'
			. '<p><label for="crs-simulation-zone">' . esc_html__( 'Zone de livraison (si nécessaire)', 'restaurant-suite-core' ) . '</label><input id="crs-simulation-zone" name="delivery_zone" type="text" maxlength="120"></p>'
			. '<p class="crs-order-simulation__field--wide"><label for="crs-simulation-address">' . esc_html__( 'Adresse (si livraison)', 'restaurant-suite-core' ) . '</label><textarea id="crs-simulation-address" name="address" rows="3" maxlength="500"></textarea></p>'
			. '<p class="crs-order-simulation__field--wide"><label for="crs-simulation-note">' . esc_html__( 'Note (facultatif)', 'restaurant-suite-core' ) . '</label><textarea id="crs-simulation-note" name="note" rows="3" maxlength="500"></textarea></p>'
			. '</div>'
			. '<button type="submit" data-crs-order-simulation-submit>' . esc_html__( 'Lancer la simulation', 'restaurant-suite-core' ) . '</button>'
			. '</form>'
			. '<div class="crs-order-simulation__result" data-crs-order-simulation-result role="status" aria-live="polite" hidden></div>'
			. '</section>';
	}

	/**
	 * Enqueue assets only when the simulation shortcode is rendered.
	 *
	 * @return void
	 */
	private function enqueue_assets(): void {
		if ( function_exists( 'wp_enqueue_style' ) ) {
			wp_enqueue_style(
				'crs-order-simulation',
				CRS_PLUGIN_URL . 'assets/build/order-simulation.css',
				array(),
				CRS_VERSION
			);
		}
		if ( ! function_exists( 'wp_register_script' ) || ! function_exists( 'wp_enqueue_script' ) ) {
			return;
		}
		wp_register_script(
			'crs-order-simulation',
			CRS_PLUGIN_URL . 'assets/build/order-simulation.js',
			array(),
			CRS_VERSION,
			true
		);
		wp_localize_script(
			'crs-order-simulation',
			'CRS_ORDER_SIMULATION_CONFIG',
			array(
				'restUrl' => rest_url( 'crs/v1/order/simulate' ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'labels'  => array(
					'loading' => esc_html__( 'Simulation en cours…', 'restaurant-suite-core' ),
					'success' => esc_html__( 'Simulation acceptée : aucune commande n’a été créée.', 'restaurant-suite-core' ),
					'error'   => esc_html__( 'La simulation n’a pas pu être validée. Vérifiez les champs et le panier.', 'restaurant-suite-core' ),
				),
			)
		);
		wp_enqueue_script( 'crs-order-simulation' );
	}
}
