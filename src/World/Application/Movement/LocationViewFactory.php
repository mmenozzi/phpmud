<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement;

use PHPMud\World\Domain\Location\Direction;
use PHPMud\World\Domain\Location\Location;
use PHPMud\World\Domain\Location\LocationRepositoryInterface;

final readonly class LocationViewFactory
{
    public function __construct(private LocationRepositoryInterface $locationRepository)
    {
    }

    public function create(Location $location): LocationView
    {
        $exits = [];
        foreach (Direction::cases() as $direction) {
            $exit = $location->getExit($direction);
            if (null === $exit) {
                continue;
            }
            $exits[$direction->value] = $this->locationRepository->get($exit)->getName();
        }

        return new LocationView($location->getName(), $location->getDescription(), $exits);
    }
}
