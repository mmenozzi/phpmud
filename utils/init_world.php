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
$alreadyAddedLocations = [];
foreach ($locations as $id => $locationData) {
    if (!in_array($id, $alreadyAddedLocations, true)) {
        $location = new \PHPMud\World\Domain\Location\Location($locationData['name'], $locationData['description'], locationIdFromCsvId($id));
        $locationRepository->save($location);
        $alreadyAddedLocations[] = $id;
    } else {
        $location = $locationRepository->findByName($locationData['name'])->first();
    }

    foreach (['north', 'east', 'south', 'west', 'up', 'down'] as $directionString) {
        if (empty($locationData[$directionString])) {
            continue;
        }
        $neighborId = (int) $locationData[$directionString];
        if (in_array($neighborId, $alreadyAddedLocations, true)) {
            continue; // Skip already added locations
        }

        $neighborData = $locations[$neighborId];
        $neighbor = new \PHPMud\World\Domain\Location\Location($neighborData['name'], $neighborData['description'], locationIdFromCsvId($neighborId));
        $direction = \PHPMud\World\Domain\Location\Direction::from($directionString);
        $location->placeBorderingLocation($neighbor, \PHPMud\World\Domain\Location\Direction::from($directionString));
        $locationRepository->save($neighbor);
        $locationRepository->save($location);
        $alreadyAddedLocations[] = $neighborId;
    }
}

$character = new \PHPMud\World\Domain\Character\Character('John', $locationRepository->findAll()->first()->getId(), password_hash('p4ssw0rd', PASSWORD_DEFAULT));
@unlink('/tmp/phpmud.character');
$characterRepository = new \PHPMud\World\Infrastructure\Repository\Filesystem\CharacterRepository('/tmp/phpmud.character');
$characterRepository->save($character);
