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
        $header = fgetcsv($handle, 1000);
        $startingColumn = array_search('Partenza', $header, true);
        if (false === $startingColumn) {
            throw new \RuntimeException('Missing "Partenza" column in locations.csv');
        }
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
                'down' => $sotto,
                'starting' => 'si' === trim($data[$startingColumn] ?? ''),
            ];
        }
        fclose($handle);
    }

    return $locations;
}

$locations = readLocationsFromCsv(__DIR__ . '/locations.csv');
$startingLocationIds = array_keys(array_filter($locations, static fn (array $location): bool => $location['starting']));
if (1 !== count($startingLocationIds)) {
    throw new \RuntimeException(sprintf('Exactly one location must be marked as "Partenza", found %d', count($startingLocationIds)));
}
@unlink('/tmp/phpmud.location');
$locationRepository = new \PHPMud\World\Infrastructure\Repository\Filesystem\LocationRepository('/tmp/phpmud.location');
$worldLocations = [];
foreach ($locations as $id => $locationData) {
    $worldLocations[$id] = new \PHPMud\World\Domain\Location\Location($locationData['name'], $locationData['description'], locationIdFromCsvId($id), $locationData['starting']);
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

$character = new \PHPMud\World\Domain\Character\Character(\PHPMud\World\Domain\Character\CharacterName::fromString('John'), $worldLocations[$startingLocationIds[0]]->getId(), password_hash('p4ssw0rd', PASSWORD_DEFAULT));
@unlink('/tmp/phpmud.character');
$characterRepository = new \PHPMud\World\Infrastructure\Repository\Filesystem\CharacterRepository('/tmp/phpmud.character');
$characterRepository->save($character);
