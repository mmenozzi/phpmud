<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\WebSocket;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Domain\Time\DayPhase;
use Webmozart\Assert\Assert;

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

    /**
     * @Then /^I should be told that it is (dawn|noon|sunset|night)$/
     */
    public function iShouldBeToldThatItIs(DayPhase $dayPhase): void
    {
        Assert::eq($this->sharedStorage->get('current_day_phase.me'), ['type' => 'day_phase', 'day_phase' => $dayPhase->value]);
    }
}
