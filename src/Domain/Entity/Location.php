<?php

declare(strict_types=1);

namespace PHPMud\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPMud\Domain\Direction;
use Symfony\Component\Uid\Uuid;

final class Location
{
    private readonly Uuid $id;

    /** @var Collection<string, Location> */
    private Collection $neighbors;

    public function __construct(private readonly string $name, private readonly string $description)
    {
        $this->id = Uuid::v6();
        $this->neighbors = new ArrayCollection();
    }

    public function getId(): Uuid
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

    public function placeBorderingLocation(Location $newLocation, Direction $direction): void
    {
        $this->neighbors->set($direction->value, $newLocation);
        if (null === $newLocation->getNeighbor($direction->opposite())) {
            $newLocation->placeBorderingLocation($this, $direction->opposite());
        }
    }

    public function getNeighbor(Direction $direction): ?Location
    {
        return $this->neighbors->get($direction->value);
    }
}
