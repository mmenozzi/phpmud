<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Location;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class Location
{
    private readonly LocationId $id;

    /** @var Collection<string, LocationId> */
    private Collection $exits;

    public function __construct(
        private readonly string $name,
        private readonly string $description,
        ?LocationId $id = null,
        private readonly bool $startingLocation = false,
    ) {
        $this->id = $id ?? LocationId::generate();
        $this->exits = new ArrayCollection();
    }

    public function getId(): LocationId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isStartingLocation(): bool
    {
        return $this->startingLocation;
    }

    public function placeBorderingLocation(Location $newLocation, Direction $direction): void
    {
        $this->exits->set($direction->value, $newLocation->getId());
        if (null === $newLocation->getExit($direction->opposite())) {
            $newLocation->placeBorderingLocation($this, $direction->opposite());
        }
    }

    public function getExit(Direction $direction): ?LocationId
    {
        return $this->exits->get($direction->value);
    }
}
