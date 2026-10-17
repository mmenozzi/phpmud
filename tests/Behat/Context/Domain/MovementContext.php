<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Location\Direction;
use PHPMud\World\Domain\Location\Location;
use PHPMud\World\Domain\Location\LocationRepositoryInterface;
use PHPMud\World\Domain\Movement\MovementPolicy;
use PHPMud\World\Domain\Movement\NoExitInDirectionException;
use Webmozart\Assert\Assert;

final class MovementContext implements Context
{
    public function __construct(
        private readonly SharedStorage $sharedStorage,
        private readonly LocationRepositoryInterface $locationRepository,
        private readonly MovementPolicy $movementPolicy,
    ) {
    }

    /**
     * @When /^I move to (north|east|south|west|up|down)$/
     */
    public function iMoveTo(Direction $direction): void
    {
        /** @var Character $character */
        $character = $this->sharedStorage->get('character');
        $current = $this->locationRepository->get($character->getLocationId());
        try {
            $this->movementPolicy->move($character, $current, $direction);
        } catch (NoExitInDirectionException $exception) {
            $this->sharedStorage->set('movement_failure', $exception);
        }
    }

    /**
     * @Then /^I should see that I am in the (location "[^"]*")$/
     */
    public function iShouldSeeThatIAmInTheLocation(Location $location): void
    {
        /** @var Character $character */
        $character = $this->sharedStorage->get('character');
        Assert::true($character->getLocationId()->equals($location->getId()));
    }

    /**
     * @Then /^I should see that there is a (location "[^"]*") (north|east|south|west|up|down) from here$/
     */
    public function iShouldSeeThatThereIsALocationSouthFromLocation(Location $location, Direction $direction): void
    {
        /** @var Character $character */
        $character = $this->sharedStorage->get('character');
        $exit = $this->locationRepository->get($character->getLocationId())->getExit($direction);
        Assert::notNull($exit);
        Assert::true($exit->equals($location->getId()));
    }

    /**
     * @Then I should be told I cannot move there
     */
    public function iShouldBeToldICannotMoveThere(): void
    {
        Assert::isInstanceOf($this->sharedStorage->get('movement_failure'), NoExitInDirectionException::class);
    }
}
