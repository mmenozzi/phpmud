<?php

declare(strict_types=1);

namespace PHPMud\Domain\Entity;

use PHPMud\Domain\Direction;
use Symfony\Component\Uid\Uuid;

final class Character
{
    private readonly Uuid $id;

    public function __construct(
        private readonly string $firstName,
        private readonly string $lastName,
        private Location $location,
    ) {
        $this->id = Uuid::v6();
    }

    public function getId(): Uuid
    {
        return $this->id;
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
        if (null === $newLocation) {
            // TODO throw if $newLocation is null? For now we simply remain in the current location without feedback.
            return;
        }
        $this->location = $newLocation;
    }
}
