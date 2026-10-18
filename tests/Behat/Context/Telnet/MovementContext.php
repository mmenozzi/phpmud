<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Telnet;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Location\Direction;
use PHPMud\World\Domain\Location\Location;
use PHPMud\World\Infrastructure\Delivery\Telnet\DirectionLabel;
use PHPMud\World\Infrastructure\Delivery\Telnet\MovementMessage;
use Webmozart\Assert\Assert;

final class MovementContext implements Context
{
    use TelnetClientTrait;

    public function __construct(SharedStorage $sharedStorage)
    {
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @When /^I move to (north|east|south|west|up|down)$/
     */
    public function iMoveTo(Direction $direction): void
    {
        $this->writeLine($direction->value);
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
        $this->writeLine('look');
        Assert::contains($this->getNextResponse(), sprintf('%s: %s', DirectionLabel::of($direction), $location->getName()));
    }

    /**
     * @Then I should be told I cannot move there
     */
    public function iShouldBeToldICannotMoveThere(): void
    {
        Assert::contains($this->getNextResponse(), 'Non puoi muoverti in quella direzione!');
    }

    /**
     * @Then /^"([^"]*)" should see me leave to (north|east|south|west|up|down)$/
     */
    public function characterShouldSeeMeLeaveTo(string $name, Direction $direction): void
    {
        $this->waitForBroadcast(MovementMessage::left($this->getMyCharacter()->getName(), $direction), $name);
    }

    /**
     * @Then /^"([^"]*)" should see me arrive from (north|east|south|west|up|down)$/
     */
    public function characterShouldSeeMeArriveFrom(string $name, Direction $direction): void
    {
        $this->waitForBroadcast(MovementMessage::arrived($this->getMyCharacter()->getName(), $direction->opposite()), $name);
    }

    /**
     * @Then /^I should see "([^"]*)" here$/
     */
    public function iShouldSeeHere(string $name): void
    {
        Assert::inArray($name, $this->getCharacterNamesHere());
    }

    /**
     * @Then /^I should not see myself here$/
     */
    public function iShouldNotSeeMyselfHere(): void
    {
        Assert::false(in_array($this->getMyCharacter()->getName(), $this->getCharacterNamesHere(), true));
    }

    /**
     * @return list<string>
     */
    private function getCharacterNamesHere(): array
    {
        $this->writeLine('look');
        foreach (explode(PHP_EOL, $this->getNextResponse()) as $line) {
            if (1 === preg_match("/^Qui (?:c'è|ci sono): (.+)\\.$/u", $line, $matches)) {
                return explode(', ', $matches[1]);
            }
        }

        return [];
    }

    private function getMyCharacter(): Character
    {
        $character = $this->sharedStorage->get('character');
        Assert::isInstanceOf($character, Character::class);

        return $character;
    }
}
