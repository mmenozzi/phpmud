<?php

declare(strict_types=1);

namespace PHPMud\Application\Movement\Command;

use PHPMud\Application\CommandResponseInterface;
use PHPMud\Domain\Direction;
use PHPMud\Domain\Entity\Location;

final readonly class MoveCommandResponse implements CommandResponseInterface
{
    public function __construct(private bool $successful, private Direction $direction, private Location $location)
    {
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getDirection(): Direction
    {
        return $this->direction;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }
}
