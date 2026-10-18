<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Time;

use PHPMud\World\Domain\DomainEvent;

final readonly class DayPhaseStarted implements DomainEvent
{
    public function __construct(private DayPhase $dayPhase)
    {
    }

    public function getDayPhase(): DayPhase
    {
        return $this->dayPhase;
    }
}
