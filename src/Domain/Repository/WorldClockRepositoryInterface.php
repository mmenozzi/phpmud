<?php

declare(strict_types=1);

namespace PHPMud\Domain\Repository;

use PHPMud\Domain\Entity\WorldClock;

interface WorldClockRepositoryInterface
{
    public function get(): WorldClock;

    public function save(WorldClock $worldClock): void;
}
