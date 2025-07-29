<?php

declare(strict_types=1);

namespace PHPMud\Application\Movement\Command;

use PHPMud\Application\CommandResponseInterface;
use PHPMud\Domain\Entity\Location;

final readonly class LookCommandResponse implements CommandResponseInterface
{
    public function __construct(private Location $location)
    {
    }

    public function getLocation(): Location
    {
        return $this->location;
    }
}
