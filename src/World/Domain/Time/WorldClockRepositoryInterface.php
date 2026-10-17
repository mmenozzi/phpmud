<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Time;

interface WorldClockRepositoryInterface
{
    public function get(): WorldClock;

    public function save(WorldClock $worldClock): void;
}
