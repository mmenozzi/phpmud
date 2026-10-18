<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Time\CurrentDayPhase;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Domain\Time\DayPhase;

final readonly class CurrentDayPhaseCommandResponse implements CommandResponseInterface
{
    public function __construct(private DayPhase $dayPhase)
    {
    }

    public function getDayPhase(): DayPhase
    {
        return $this->dayPhase;
    }
}
