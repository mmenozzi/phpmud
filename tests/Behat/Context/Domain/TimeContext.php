<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use PHPMud\Application\Time\Command\TickCommand;
use PHPMud\Application\Time\Command\TickCommandResponse;
use PHPMud\Application\Time\CommandExecutor\TickCommandExecutor;
use PHPMud\Domain\DayPhase;
use PHPMud\Tests\Behat\Service\SharedStorage;
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
