<?php
/**
 * Plugin Name: Restaurant Suite Core
 * Description: Core contracts and restaurant modules for Restaurant Suite.
 * Version: 0.2.0
 * Requires at least: 6.2
 * Requires PHP: 8.2
 * Requires Plugins: woocommerce
 * License: GPL-2.0-or-later
 * Text Domain: restaurant-suite-core
 */

defined('ABSPATH') || exit;

define('CRS_VERSION', '0.2.0');
define('CRS_PLUGIN_FILE', __FILE__);
define('CRS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CRS_PLUGIN_URL', plugin_dir_url(__FILE__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'CRS\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = strtolower(str_replace('\\', '/', substr($class, strlen($prefix))));
    $relative = str_replace('class-', '', $relative);
    $path = CRS_PLUGIN_DIR . 'src/' . dirname($relative) . '/class-' . basename($relative) . '.php';
    if (is_readable($path)) {
        require_once $path;
    }
});

require_once CRS_PLUGIN_DIR . 'src/Contracts/class-contractregistry.php';
require_once CRS_PLUGIN_DIR . 'src/Menu/class-menuarguments.php';
require_once CRS_PLUGIN_DIR . 'src/Menu/class-menuquery.php';
require_once CRS_PLUGIN_DIR . 'src/Menu/class-menurenderer.php';
require_once CRS_PLUGIN_DIR . 'src/class-plugin.php';
require_once CRS_PLUGIN_DIR . 'src/class-menucontroller.php';

add_action('plugins_loaded', static function (): void {
    if (!class_exists('WooCommerce')) {
        return;
    }

    \CRS\Plugin::boot();
}, 20);
