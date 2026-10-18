<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\RecordingNotifier;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Application\Time\CurrentDayPhase\CurrentDayPhaseCommand;
use PHPMud\World\Application\Time\CurrentDayPhase\CurrentDayPhaseCommandExecutor;
use PHPMud\World\Application\Time\CurrentDayPhase\CurrentDayPhaseCommandResponse;
use PHPMud\World\Application\Time\DayPhaseStarted\DayPhaseStartedNotification;
use PHPMud\World\Application\Time\Tick\TickCommand;
use PHPMud\World\Application\Time\Tick\TickCommandExecutor;
use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Time\DayPhase;
use Webmozart\Assert\Assert;

final class TimeContext implements Context
{
    public function __construct(
        private readonly SharedStorage $sharedStorage,
        private readonly TickCommandExecutor $tickCommandExecutor,
        private readonly RecordingNotifier $notifier,
        private readonly CurrentDayPhaseCommandExecutor $currentDayPhaseCommandExecutor,
    ) {
    }

    /**
     * @When /^an hour passes$/
     */
    public function anHourPasses(): void
    {
        $this->tickCommandExecutor->execute(new TickCommand());
    }

    /**
     * @Then /^I should be told that (dawn|noon|sunset|night) begins$/
     */
    public function iShouldBeToldThatDayPhaseBegins(DayPhase $dayPhase): void
    {
        Assert::eq($this->getStartedDayPhases(), [$dayPhase]);
    }

    /**
     * @Then /^I should be told that it is (dawn|noon|sunset|night)$/
     */
    public function iShouldBeToldThatItIs(DayPhase $dayPhase): void
    {
        $response = $this->currentDayPhaseCommandExecutor->execute(new CurrentDayPhaseCommand());
        Assert::isInstanceOf($response, CurrentDayPhaseCommandResponse::class);
        Assert::eq($response->getDayPhase(), $dayPhase);
    }

    /**
     * @Then /^I should not be told that a day phase begins$/
     */
    public function iShouldNotBeToldThatADayPhaseBegins(): void
    {
        Assert::isEmpty($this->getStartedDayPhases());
    }

    /**
     * @return list<DayPhase>
     */
    private function getStartedDayPhases(): array
    {
        /** @var Character $character */
        $character = $this->sharedStorage->get('character');
        $dayPhases = [];
        foreach ($this->notifier->getNotificationsReceivedBy($character->getId()) as $notification) {
            if ($notification instanceof DayPhaseStartedNotification) {
                $dayPhases[] = $notification->getDayPhase();
            }
        }

        return $dayPhases;
    }
}
