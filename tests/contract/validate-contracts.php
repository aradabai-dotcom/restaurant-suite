<?php
$root = dirname(__DIR__, 2);
$files = ['data-contract.json', 'statuses.json', 'events.json', 'permissions.json'];
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
echo "Contracts valid.\n";
