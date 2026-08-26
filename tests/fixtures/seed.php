<?php
if (!function_exists('wc_get_product')) {
    fwrite(STDERR, "WooCommerce must be active before seeding fixtures.\n");
    exit(1);
}
$term = term_exists('CRS Test Menu', 'product_cat');
$term_id = $term ? (int) $term['term_id'] : (int) wp_insert_term('CRS Test Menu', 'product_cat')['term_id'];
$empty = term_exists('CRS Empty Category', 'product_cat');
if (!$empty) { wp_insert_term('CRS Empty Category', 'product_cat'); }
$products = [
    ['sku' => 'crs-test-simple', 'name' => 'CRS Test Simple', 'price' => '9.90', 'stock' => 20],
    ['sku' => 'crs-test-out-of-stock', 'name' => 'CRS Test Out of Stock', 'price' => '12.50', 'stock' => 0],
];
foreach ($products as $fixture) {
    $id = wc_get_product_id_by_sku($fixture['sku']);
    $product = $id ? wc_get_product($id) : new WC_Product_Simple();
    $product->set_name($fixture['name']);
    $product->set_sku($fixture['sku']);
    $product->set_regular_price($fixture['price']);
    $product->set_manage_stock(true);
    $product->set_stock_quantity($fixture['stock']);
    $product->set_stock_status($fixture['stock'] > 0 ? 'instock' : 'outofstock');
    $product->set_category_ids([$term_id]);
    $product->update_meta_data('_crs_test_fixture', 1);
    $product->save();
}
foreach ([
    ['user_login' => 'crs-manager', 'user_email' => 'manager@restaurant-suite.test', 'role' => 'shop_manager'],
    ['user_login' => 'crs-cuisine', 'user_email' => 'cuisine@restaurant-suite.test', 'role' => 'subscriber'],
    ['user_login' => 'crs-livreur', 'user_email' => 'livreur@restaurant-suite.test', 'role' => 'subscriber'],
] as $user) {
    if (!username_exists($user['user_login'])) {
        wp_insert_user(['user_login' => $user['user_login'], 'user_pass' => wp_generate_password(24, true), 'user_email' => $user['user_email'], 'role' => $user['role']]);
    }
}
update_option('crs_settings', ['schema_version' => 1, 'restaurant_name' => 'Restaurant Suite Test', 'timezone' => 'Europe/Paris', 'whatsapp_number' => '+33000000000', 'order_minimum' => '0.00'], false);
echo "Fixtures seeded.\n";
