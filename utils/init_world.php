<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

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
            $id = trim($data[0]);
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

$locations = readLocationsFromCsv(__DIR__ . '/src/Infrastructure/Resources/locations.csv');
$locationRepository = new \PHPMud\Infrastructure\Repository\Filesystem\LocationRepository('/tmp/phpmud.location');
$addedLocations = [];
foreach ($locations as $id => $locationData) {
    $location = new \PHPMud\Domain\Entity\Location($locationData['name'], $locationData['description']);
    foreach (['north', 'east', 'south', 'west', 'up', 'down'] as $directionString) {
        if (empty($locationData[$directionString])) {
            continue;
        }
        $neighborId = $locationData[$directionString];
        $neighborData = $locations[$neighborId];
        $neighbor = new \PHPMud\Domain\Entity\Location($neighborData['name'], $neighborData['description']);
        $direction = \PHPMud\Domain\Direction::from($directionString);
        $location->placeBorderingLocation($neighbor, \PHPMud\Domain\Direction::from($directionString));
        $locationRepository->add($neighbor);
        $addedLocations[] = $neighborId;
    }
    $locationRepository->add($location);
    $addedLocations[] = $id;
}

$initialLocation = $locationRepository->findAll()->first();
$character = new \PHPMud\Domain\Entity\Character('John', 'Doe', $initialLocation);
$characterRepository = new \PHPMud\Infrastructure\Repository\Filesystem\CharacterRepository('/tmp/phpmud.character');
$characterRepository->add($character);
