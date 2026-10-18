<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement;

use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use PHPMud\World\Domain\Location\Direction;
use PHPMud\World\Domain\Location\Location;
use PHPMud\World\Domain\Location\LocationRepositoryInterface;

final readonly class LocationViewFactory
{
    public function __construct(
        private LocationRepositoryInterface $locationRepository,
        private CharacterRepositoryInterface $characterRepository,
    ) {
    }

    public function create(Location $location, CharacterId $viewer): LocationView
    {
        $exits = [];
        foreach (Direction::cases() as $direction) {
            $exit = $location->getExit($direction);
            if (null === $exit) {
                continue;
            }
            $exits[$direction->value] = ['id' => $exit->toString(), 'name' => $this->locationRepository->get($exit)->getName()];
        }

        $characters = [];
        foreach ($this->characterRepository->findByLocation($location->getId()) as $character) {
            if ($character->getId()->equals($viewer)) {
                continue;
            }
            $characters[] = ['name' => $character->getName()];
        }

        return new LocationView($location->getId()->toString(), $location->getName(), $location->getDescription(), $exits, $characters);
    }
}
