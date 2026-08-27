<?php
$root = dirname(__DIR__, 2);
$files = [
    'data-contract.json',
    'statuses.json',
    'events.json',
    'permissions.json',
    'restaurant-settings.json',
    'order-request.json',
];
foreach ($files as $file) {
    $path = $root . '/docs/contracts/' . $file;
    if (!is_file($path)) { fwrite(STDERR, "Missing contract: {$file}\n"); exit(1); }
    json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}
$statuses = json_decode(file_get_contents($root . '/docs/contracts/statuses.json'), true, 512, JSON_THROW_ON_ERROR);
$ids = array_column($statuses['statuses'], 'id');
foreach ($statuses['transitions'] as $transition) {
    if (!in_array($transition[0], $ids, true) || !in_array($transition[1], $ids, true)) { fwrite(STDERR, "Unknown status transition\n"); exit(1); }
}
$events = json_decode(file_get_contents($root . '/docs/contracts/events.json'), true, 512, JSON_THROW_ON_ERROR);
$names = array_column($events['events'], 'name');
if (count($names) !== count(array_unique($names))) { fwrite(STDERR, "Duplicate event name\n"); exit(1); }
$settings = json_decode(file_get_contents($root . '/docs/contracts/restaurant-settings.json'), true, 512, JSON_THROW_ON_ERROR);
$required_settings = ['enabled', 'restaurant_open', 'minimum_order', 'pickup_enabled', 'delivery_enabled', 'delivery_fee', 'delivery_zones', 'whatsapp_number'];
if (array_diff($required_settings, array_keys($settings['fields'] ?? []))) { fwrite(STDERR, "Incomplete restaurant settings contract\n"); exit(1); }
$request = json_decode(file_get_contents($root . '/docs/contracts/order-request.json'), true, 512, JSON_THROW_ON_ERROR);
$forbidden = ['price', 'prices', 'subtotal', 'total', 'tax', 'taxes', 'fee', 'fees'];
if (array_intersect($forbidden, $request['server_owned_fields'] ?? []) === []) { fwrite(STDERR, "Order contract must identify server-owned price fields\n"); exit(1); }
if (array_intersect($forbidden, array_keys($request['fields'] ?? []))) { fwrite(STDERR, "Client price field found in order request\n"); exit(1); }
echo "Contracts valid.\n";
