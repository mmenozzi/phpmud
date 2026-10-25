<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Character\CharacterName;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use PHPMud\World\Domain\Character\Sex;
use PHPMud\World\Domain\Location\Location;

final class CharacterContext implements Context
{
    private const DEFAULT_CHARACTER_NAME = 'Phate';

    public function __construct(
        private readonly CharacterRepositoryInterface $characterRepository,
        private readonly SharedStorage $sharedStorage,
    ) {
    }

    /**
     * @Given /^my character is in the (location "([^"]*)")$/
     */
    public function myCharacterIsInTheLocation(Location $location): void
    {
        $character = new Character(
            CharacterName::fromString(self::DEFAULT_CHARACTER_NAME),
            Sex::Male,
            $location->getId(),
            password_hash('p4ssw0rd', PASSWORD_DEFAULT),
        );
        $this->characterRepository->save($character);
        $this->sharedStorage->set('character', $character);
    }

    /**
     * @Given /^there is the character "([^"]*)" in the (location "[^"]*")$/
     */
    public function thereIsTheCharacterInTheLocation(string $name, Location $location): void
    {
        $character = new Character(CharacterName::fromString($name), Sex::Male, $location->getId(), password_hash('p4ssw0rd', PASSWORD_DEFAULT));
        $this->characterRepository->save($character);
        $characters = $this->sharedStorage->has('characters') ? $this->sharedStorage->get('characters') : [];
        $this->sharedStorage->set('characters', [...$characters, $name => $character]);
    }

    /**
     * @Given /^there is the character "([^"]*)" with password "([^"]*)" in the (location "[^"]*")$/
     */
    public function thereIsTheCharacterWithPasswordInTheLocation(string $name, string $password, Location $location): void
    {
        $character = new Character(CharacterName::fromString($name), Sex::Male, $location->getId(), password_hash($password, PASSWORD_DEFAULT));
        $this->characterRepository->save($character);
        $this->sharedStorage->set('character', $character);
    }
}
