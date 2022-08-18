<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use PHPMud\Domain\Direction;
use PHPMud\Domain\Entity\Character;
use PHPMud\Domain\Entity\Location;
use PHPMud\Domain\Repository\CharacterRepositoryInterface;
use PHPMud\Tests\Behat\Service\SharedStorage;
use Webmozart\Assert\Assert;

final class MovementContext implements Context
{
    public function __construct(private readonly SharedStorage $sharedStorage)
    {
    }

    /**
     * @When /^I move to (north|east|south|west|up|down)$/
     */
    public function iMoveTo(Direction $direction): void
    {
        $character = $this->sharedStorage->get('character');
        $character->moveTo($direction);
    }

    /**
     * @Then /^I should see that I am in the (location "([^"]*)")$/
     */
    public function iShouldSeeThatIAmInTheLocation(Location $location)
    {
        $character = $this->sharedStorage->get('character');
        Assert::eq($character->getLocation()->getName(), $location->getName());
    }
}
