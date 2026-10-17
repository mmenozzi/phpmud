<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement\View;

use PHPMud\World\Domain\Location\Location;

final readonly class LocationView
{
    /**
     * @param array<string, Location> $exits keyed by Direction value
     */
    public function __construct(private Location $location, private array $exits)
    {
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @return array<string, Location>
     */
    public function getExits(): array
    {
        return $this->exits;
    }
}
