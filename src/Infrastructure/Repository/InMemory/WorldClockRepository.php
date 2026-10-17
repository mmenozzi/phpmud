<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Repository\InMemory;

use PHPMud\Domain\Time\WorldClock;
use PHPMud\Domain\Time\WorldClockRepositoryInterface;

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
