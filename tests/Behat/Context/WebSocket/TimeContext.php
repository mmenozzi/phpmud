<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\WebSocket;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Domain\Time\DayPhase;

final class TimeContext implements Context
{
    use WebSocketClientTrait;

    public function __construct(SharedStorage $sharedStorage)
    {
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @When /^an hour passes$/
     */
    public function anHourPasses(): void
    {
    }

    /**
     * @Then /^I should be told that (dawn|noon|sunset|night) begins$/
     */
    public function iShouldBeToldThatDayPhaseBegins(DayPhase $dayPhase): void
    {
        $this->waitForBroadcast(['type' => 'day_phase_started', 'day_phase' => $dayPhase->value]);
    }
}
