<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Time\Command;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Domain\Time\DayPhase;

final readonly class TickCommandResponse implements CommandResponseInterface
{
    public function __construct(private ?DayPhase $startedDayPhase)
    {
    }

    public function getStartedDayPhase(): ?DayPhase
    {
        return $this->startedDayPhase;
    }
}
