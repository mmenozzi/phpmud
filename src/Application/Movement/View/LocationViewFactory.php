<?php

declare(strict_types=1);

namespace PHPMud\Application\Movement\View;

use PHPMud\Domain\Location\Direction;
use PHPMud\Domain\Location\Location;
use PHPMud\Domain\Location\LocationRepositoryInterface;

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
            $exits[$direction->value] = $this->locationRepository->get($exit);
        }

        return new LocationView($location, $exits);
    }
}
