<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Application\Time\Tick\TickCommand;
use PHPMud\World\Application\Time\Tick\TickCommandExecutor;
use PHPMud\World\Application\Time\Tick\TickCommandResponse;
use PHPMud\World\Domain\Time\DayPhase;
use Webmozart\Assert\Assert;

final class TimeContext implements Context
{
    public function __construct(
        private readonly SharedStorage $sharedStorage,
        private readonly TickCommandExecutor $tickCommandExecutor,
    ) {
    }

    /**
     * @When /^an hour passes$/
     */
    public function anHourPasses(): void
    {
        $this->sharedStorage->set('tick_response', $this->tickCommandExecutor->execute(new TickCommand()));
    }

    /**
     * @Then /^I should be told that (dawn|noon|sunset|night) begins$/
     */
    public function iShouldBeToldThatDayPhaseBegins(DayPhase $dayPhase): void
    {
        Assert::same($this->getTickResponse()->getStartedDayPhase(), $dayPhase);
    }

    /**
     * @Then /^I should not be told that a day phase begins$/
     */
    public function iShouldNotBeToldThatADayPhaseBegins(): void
    {
        Assert::null($this->getTickResponse()->getStartedDayPhase());
    }

    private function getTickResponse(): TickCommandResponse
    {
        $response = $this->sharedStorage->get('tick_response');
        Assert::isInstanceOf($response, TickCommandResponse::class);

        return $response;
    }
}
