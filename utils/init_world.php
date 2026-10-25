<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/location_ids.php';

// read locations form CSV file
function readLocationsFromCsv(string $filepath): array
{
    if (!file_exists($filepath)) {
        throw new \RuntimeException("File not found: $filepath");
    }

    $locations = [];
    if (($handle = fopen($filepath, 'r')) !== false) {
        fgetcsv($handle, 1000); // Skip header line
        while (($data = fgetcsv($handle, 1000)) !== false) {
            if (count($data) < 2) {
                continue; // Skip invalid lines
            }
            $id = (int) trim($data[0]);
            $name = trim($data[1]);
            $description = trim($data[2]);
            $nord = trim($data[3] ?? '');
            $est = trim($data[4] ?? '');
            $sud = trim($data[5] ?? '');
            $ovest = trim($data[6] ?? '');
            $sopra = trim($data[7] ?? '');
            $sotto = trim($data[8] ?? '');
            $locations[$id] = [
                'name' => $name,
                'description' => $description,
                'north' => $nord,
                'east' => $est,
                'south' => $sud,
                'west' => $ovest,
                'up' => $sopra,
                'down' => $sotto
            ];
        }
        fclose($handle);
    }

    return $locations;
}

$locations = readLocationsFromCsv(__DIR__ . '/locations.csv');
@unlink('/tmp/phpmud.location');
$locationRepository = new \PHPMud\World\Infrastructure\Repository\Filesystem\LocationRepository('/tmp/phpmud.location');
$worldLocations = [];
foreach ($locations as $id => $locationData) {
    $worldLocations[$id] = new \PHPMud\World\Domain\Location\Location($locationData['name'], $locationData['description'], locationIdFromCsvId($id));
}

foreach ($locations as $id => $locationData) {
    foreach (\PHPMud\World\Domain\Location\Direction::cases() as $direction) {
        if (empty($locationData[$direction->value])) {
            continue;
        }
        $neighborId = (int) $locationData[$direction->value];
        if (!isset($locations[$neighborId])) {
            throw new \RuntimeException("Location $id has an exit {$direction->value} to the unknown location $neighborId");
        }
        if ((int) $locations[$neighborId][$direction->opposite()->value] !== $id) {
            throw new \RuntimeException("Location $id has an exit {$direction->value} to $neighborId, but $neighborId has no exit {$direction->opposite()->value} back to $id");
        }
        $worldLocations[$id]->placeBorderingLocation($worldLocations[$neighborId], $direction);
    }
}

foreach ($worldLocations as $location) {
    $locationRepository->save($location);
}

$character = new \PHPMud\World\Domain\Character\Character(\PHPMud\World\Domain\Character\CharacterName::fromString('John'), $worldLocations[1]->getId(), password_hash('p4ssw0rd', PASSWORD_DEFAULT));
@unlink('/tmp/phpmud.character');
$characterRepository = new \PHPMud\World\Infrastructure\Repository\Filesystem\CharacterRepository('/tmp/phpmud.character');
$characterRepository->save($character);
