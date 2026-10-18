<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Time\DayPhaseStarted;

use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Domain\Time\DayPhase;

final readonly class DayPhaseStartedNotification implements NotificationInterface
{
    public function __construct(private DayPhase $dayPhase)
    {
    }

    public function getDayPhase(): DayPhase
    {
        return $this->dayPhase;
    }
}
