<?php

declare(strict_types=1);

namespace PHPMud\Application\Time\Command;

use PHPMud\Application\CommandResponseInterface;
use PHPMud\Domain\Time\DayPhase;

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
