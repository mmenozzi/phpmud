<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/location_ids.php';

$handle = fopen(__DIR__ . '/locations.csv', 'r');
if (false === $handle) {
    throw new \RuntimeException('Cannot open locations.csv');
}
$header = fgetcsv($handle, escape: '');
$themeColumn = array_search('Scena', $header, true);
if (false === $themeColumn) {
    throw new \RuntimeException('Missing "Scena" column in locations.csv');
}

$scenes = [];
while (($data = fgetcsv($handle, escape: '')) !== false) {
    $theme = trim($data[$themeColumn] ?? '');
    if ('' === $theme) {
        continue;
    }
    $scenes[locationIdFromCsvId((int) trim($data[0]))->toString()] = ['theme' => $theme];
}
fclose($handle);

$target = __DIR__ . '/../client/content/scenes.json';
@mkdir(dirname($target), 0777, true);
file_put_contents($target, json_encode($scenes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
echo sprintf('Written %d scenes to %s', count($scenes), realpath($target)) . PHP_EOL;
