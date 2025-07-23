<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Socket;

use Behat\Behat\Context\Context;
use PHPMud\Domain\Direction;
use PHPMud\Domain\Entity\Location;
use PHPMud\Tests\Behat\Service\SharedStorage;
use Webmozart\Assert\Assert;

final class MovementContext implements Context
{
    use SocketResponseTrait;

    public function __construct(SharedStorage $sharedStorage)
    {
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @When /^I move to (north|east|south|west|up|down)$/
     */
    public function iMoveTo(Direction $direction): void
    {
        socket_write($this->sharedStorage->get('socket'), $direction->value.PHP_EOL);
    }

    /**
     * @Then /^I should see that I am in the (location "[^"]*")$/
     */
    public function iShouldSeeThatIAmInTheLocation(Location $location)
    {
        $response = $this->getNextResponse();
        Assert::contains($response, $location->getName());
        Assert::contains($response, $location->getDescription());
    }

    /**
     * @Then /^I should see that there is a (location "[^"]*") (north|east|south|west|up|down) from here$/
     */
    public function iShouldSeeThatThereIsALocationSouthFromLocation(Location $location, Direction $direction)
    {
        socket_write($this->sharedStorage->get('socket'), 'look'.PHP_EOL);
        Assert::contains($this->getNextResponse(), sprintf('%s: %s', ucwords($direction->value), $location->getName()));
    }
}
