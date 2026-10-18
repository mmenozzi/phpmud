<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\RecordingNotifier;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Application\Movement\CharacterMoved\CharacterArrivedNotification;
use PHPMud\World\Application\Movement\CharacterMoved\CharacterLeftNotification;
use PHPMud\World\Application\Movement\Move\MoveCommand;
use PHPMud\World\Application\Movement\Move\MoveCommandExecutor;
use PHPMud\World\Application\Movement\Move\MoveCommandResponse;
use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Location\Direction;
use PHPMud\World\Domain\Location\Location;
use PHPMud\World\Domain\Location\LocationRepositoryInterface;
use Webmozart\Assert\Assert;

final class MovementContext implements Context
{
    public function __construct(
        private readonly SharedStorage $sharedStorage,
        private readonly LocationRepositoryInterface $locationRepository,
        private readonly MoveCommandExecutor $moveCommandExecutor,
        private readonly RecordingNotifier $notifier,
    ) {
    }

    /**
     * @When /^I move to (north|east|south|west|up|down)$/
     */
    public function iMoveTo(Direction $direction): void
    {
        $this->sharedStorage->set(
            'move_response',
            $this->moveCommandExecutor->execute(new MoveCommand($this->getMyCharacter()->getId(), $direction)),
        );
    }

    /**
     * @Then /^I should see that I am in the (location "[^"]*")$/
     */
    public function iShouldSeeThatIAmInTheLocation(Location $location): void
    {
        Assert::true($this->getMyCharacter()->getLocationId()->equals($location->getId()));
    }

    /**
     * @Then /^I should see that there is a (location "[^"]*") (north|east|south|west|up|down) from here$/
     */
    public function iShouldSeeThatThereIsALocationSouthFromLocation(Location $location, Direction $direction): void
    {
        $exit = $this->locationRepository->get($this->getMyCharacter()->getLocationId())->getExit($direction);
        Assert::notNull($exit);
        Assert::true($exit->equals($location->getId()));
    }

    /**
     * @Then I should be told I cannot move there
     */
    public function iShouldBeToldICannotMoveThere(): void
    {
        $response = $this->sharedStorage->get('move_response');
        Assert::isInstanceOf($response, MoveCommandResponse::class);
        Assert::false($response->isSuccessful());
    }

    /**
     * @Then /^"([^"]*)" should see me leave to (north|east|south|west|up|down)$/
     */
    public function characterShouldSeeMeLeaveTo(string $name, Direction $direction): void
    {
        $this->assertNotified($name, new CharacterLeftNotification($this->getMyCharacter()->getName(), $direction));
    }

    /**
     * @Then /^"([^"]*)" should see me arrive from (north|east|south|west|up|down)$/
     */
    public function characterShouldSeeMeArriveFrom(string $name, Direction $direction): void
    {
        $this->assertNotified(
            $name,
            new CharacterArrivedNotification($this->getMyCharacter()->getName(), $direction->opposite()),
        );
    }

    private function assertNotified(string $name, NotificationInterface $expected): void
    {
        /** @var array<string, Character> $characters */
        $characters = $this->sharedStorage->get('characters');
        Assert::true(
            in_array($expected, $this->notifier->getNotificationsReceivedBy($characters[$name]->getId()), false),
            sprintf('"%s" was not notified.', $name),
        );
    }

    private function getMyCharacter(): Character
    {
        $character = $this->sharedStorage->get('character');
        Assert::isInstanceOf($character, Character::class);

        return $character;
    }
}
