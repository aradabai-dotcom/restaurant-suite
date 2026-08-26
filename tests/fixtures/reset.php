<?php
if (!defined('CRS_TEST_MODE') && getenv('CRS_TEST_MODE') !== '1') {
    fwrite(STDERR, "CRS_TEST_MODE required.\n");
    exit(1);
}
if (function_exists('wc_get_products')) {
    foreach (wc_get_products(['limit' => -1, 'status' => 'any']) as $product) {
        if ($product->get_meta('_crs_test_fixture')) { wp_delete_post($product->get_id(), true); }
    }
}
foreach (['manager@restaurant-suite.test', 'cuisine@restaurant-suite.test', 'livreur@restaurant-suite.test'] as $email) {
    $user = get_user_by('email', $email);
    if ($user) { require_once ABSPATH . 'wp-admin/includes/user.php'; wp_delete_user($user->ID); }
}
delete_option('crs_settings');
echo "Fixtures reset.\n";
