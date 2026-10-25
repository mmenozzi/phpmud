<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use PHPMud\World\Domain\Location\Direction;
use PHPMud\World\Domain\Location\Location;
use PHPMud\World\Domain\Location\LocationRepositoryInterface;

final class LocationContext implements Context
{
    public function __construct(private readonly LocationRepositoryInterface $locationRepository)
    {
    }

    /**
     * @Given /^there is the location "([^"]*)"$/
     */
    public function thereIsTheLocation(string $locationName): void
    {
        $location = new Location($locationName, 'Description of '.$locationName);
        $this->locationRepository->save($location);
    }

    /**
     * @Given /^there is the starting location "([^"]*)"$/
     */
    public function thereIsTheStartingLocation(string $locationName): void
    {
        $location = new Location($locationName, 'Description of '.$locationName, startingLocation: true);
        $this->locationRepository->save($location);
    }

    /**
     * @Given /^there is the location "([^"]*)" (north|east|south|west|up|down) from (location "([^"]*)")$/
     */
    public function thereIsTheLocationFromLocation(
        string $newLocationName,
        Direction $direction,
        Location $existentLocation,
    ): void {
        $newLocation = new Location($newLocationName, 'Description of '.$newLocationName);
        $existentLocation->placeBorderingLocation($newLocation, $direction);
        $this->locationRepository->save($newLocation);
        $this->locationRepository->save($existentLocation);
    }
}
