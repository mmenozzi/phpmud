<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\WebSocket;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Location\Direction;
use PHPMud\World\Domain\Location\Location;
use Webmozart\Assert\Assert;

final class MovementContext implements Context
{
    use WebSocketClientTrait;

    public function __construct(SharedStorage $sharedStorage)
    {
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @When /^I move to (north|east|south|west|up|down)$/
     */
    public function iMoveTo(Direction $direction): void
    {
        $this->sendMessage(['type' => 'move', 'direction' => $direction->value]);
    }

    /**
     * @Then /^I should see that I am in the (location "[^"]*")$/
     */
    public function iShouldSeeThatIAmInTheLocation(Location $location): void
    {
        $response = $this->getNextResponse();
        Assert::eq($response['type'], 'location');
        Assert::eq($response['id'], $location->getId()->toString());
        Assert::eq($response['name'], $location->getName());
        Assert::eq($response['description'], $location->getDescription());
    }

    /**
     * @Then /^I should see that there is a (location "[^"]*") (north|east|south|west|up|down) from here$/
     */
    public function iShouldSeeThatThereIsALocationSouthFromLocation(Location $location, Direction $direction): void
    {
        $this->sendMessage(['type' => 'look']);
        $response = $this->getNextResponse();
        Assert::eq($response['type'], 'location');
        Assert::isArray($response['exits']);
        Assert::eq(
            $response['exits'][$direction->value] ?? null,
            ['id' => $location->getId()->toString(), 'name' => $location->getName()],
        );
    }

    /**
     * @Then I should be told I cannot move there
     */
    public function iShouldBeToldICannotMoveThere(): void
    {
        Assert::eq($this->getNextResponse()['type'], 'move_failed');
    }

    /**
     * @Then /^"([^"]*)" should see me leave to (north|east|south|west|up|down)$/
     */
    public function characterShouldSeeMeLeaveTo(string $name, Direction $direction): void
    {
        $this->waitForBroadcast(
            ['type' => 'character_left', 'name' => $this->getMyCharacter()->getName(), 'via' => $direction->value],
            $name,
        );
    }

    /**
     * @Then /^"([^"]*)" should see me arrive from (north|east|south|west|up|down)$/
     */
    public function characterShouldSeeMeArriveFrom(string $name, Direction $direction): void
    {
        $this->waitForBroadcast(
            ['type' => 'character_arrived', 'name' => $this->getMyCharacter()->getName(), 'via' => $direction->opposite()->value],
            $name,
        );
    }

    private function getMyCharacter(): Character
    {
        $character = $this->sharedStorage->get('character');
        Assert::isInstanceOf($character, Character::class);

        return $character;
    }
}
