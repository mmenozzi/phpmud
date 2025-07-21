<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

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
unlink('/tmp/phpmud.location');
$locationRepository = new \PHPMud\Infrastructure\Repository\Filesystem\LocationRepository('/tmp/phpmud.location');
$alreadyAddedLocations = [];
foreach ($locations as $id => $locationData) {
    if (!in_array($id, $alreadyAddedLocations, true)) {
        $location = new \PHPMud\Domain\Entity\Location($locationData['name'], $locationData['description']);
        $locationRepository->add($location);
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
        $neighbor = new \PHPMud\Domain\Entity\Location($neighborData['name'], $neighborData['description']);
        $direction = \PHPMud\Domain\Direction::from($directionString);
        $location->placeBorderingLocation($neighbor, \PHPMud\Domain\Direction::from($directionString));
        $locationRepository->add($neighbor);
        $alreadyAddedLocations[] = $neighborId;
    }
}

$character = new \PHPMud\Domain\Entity\Character('John', 'Doe', $locationRepository->findAll()->first());
unlink('/tmp/phpmud.character');
$characterRepository = new \PHPMud\Infrastructure\Repository\Filesystem\CharacterRepository('/tmp/phpmud.character');
$characterRepository->add($character);
