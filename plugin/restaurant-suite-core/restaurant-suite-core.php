<?php
/**
 * Plugin Name: Restaurant Suite Core
 * Description: Core contracts and future restaurant modules for Restaurant Suite.
 * Version: 0.0.1
 * Requires at least: 6.2
 * Requires PHP: 8.2
 * Requires Plugins: woocommerce
 * License: GPL-2.0-or-later
 * Text Domain: restaurant-suite-core
 */

defined('ABSPATH') || exit;

define('CRS_VERSION', '0.0.1');
define('CRS_PLUGIN_FILE', __FILE__);
define('CRS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CRS_PLUGIN_URL', plugin_dir_url(__FILE__));

add_action('plugins_loaded', static function (): void {
    // Phase 0.0 only: no public menu, cart, dashboard or WhatsApp behavior is registered yet.
    if (!class_exists('WooCommerce')) {
        return;
    }
});
