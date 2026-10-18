<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Telnet;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Domain\Time\DayPhase;
use PHPMud\World\Infrastructure\Delivery\Telnet\DayPhaseMessage;
use Webmozart\Assert\Assert;

final class TimeContext implements Context
{
    use TelnetClientTrait;

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
        $this->waitForBroadcast(DayPhaseMessage::of($dayPhase));
    }

    /**
     * @Then /^I should be told that it is (dawn|noon|sunset|night)$/
     */
    public function iShouldBeToldThatItIs(DayPhase $dayPhase): void
    {
        Assert::eq($this->sharedStorage->get('current_day_phase.me'), DayPhaseMessage::current($dayPhase));
    }
}
