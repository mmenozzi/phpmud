<?php

declare(strict_types=1);

namespace PHPMud\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPMud\Domain\Direction;
use Symfony\Component\Uid\Uuid;

final class Location
{
    use IdTrait;

    /** @var Collection<Direction, Location> */
    private Collection $neighbors;

    public function __construct(private readonly string $name)
    {
        $this->createId();
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

    public function placeBorderingLocation(Location $newLocation, Direction $direction): void
    {
        $this->neighbors->set($direction->value, $newLocation);
        if ($newLocation->getNeighbor($direction->opposite()) === null) {
            $newLocation->placeBorderingLocation($this, $direction->opposite());
        }
    }

    public function getNeighbor(Direction $direction): ?Location
    {
        return $this->neighbors->get($direction->value);
    }
}
