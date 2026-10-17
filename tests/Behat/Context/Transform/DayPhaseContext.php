<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use PHPMud\World\Domain\Time\DayPhase;

final class DayPhaseContext implements Context
{
    /**
     * @Transform /^(dawn|noon|sunset|night)$/
     */
    public function transformDayPhaseFromString(string $dayPhase): DayPhase
    {
        return DayPhase::from($dayPhase);
    }
}
