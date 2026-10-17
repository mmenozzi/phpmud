<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Repository\InMemory;

use PHPMud\World\Domain\Time\WorldClock;
use PHPMud\World\Domain\Time\WorldClockRepositoryInterface;

final class WorldClockRepository implements WorldClockRepositoryInterface
{
    private WorldClock $worldClock;

    public function __construct()
    {
        $this->worldClock = new WorldClock();
    }

    public function get(): WorldClock
    {
        return $this->worldClock;
    }

    public function save(WorldClock $worldClock): void
    {
        $this->worldClock = $worldClock;
    }
}
