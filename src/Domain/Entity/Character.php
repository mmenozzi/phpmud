<?php

declare(strict_types=1);

namespace PHPMud\Domain\Entity;

use PHPMud\Domain\Direction;

final class Character
{
    use IdTrait;

    public function __construct(
        private readonly string $firstName,
        private readonly string $lastName,
        private Location $location
    ) {
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function moveTo(Direction $direction): void
    {
        $newLocation = $this->getLocation()->getNeighbor($direction);
        // TODO throw if $newLocation is null
        $this->location = $newLocation;
    }
}
