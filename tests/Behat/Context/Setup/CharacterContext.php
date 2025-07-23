<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use PHPMud\Domain\Entity\Character;
use PHPMud\Domain\Entity\Location;
use PHPMud\Domain\Repository\CharacterRepositoryInterface;
use PHPMud\Tests\Behat\Service\SharedStorage;

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
            self::DEFAULT_CHARACTER_NAME,
            $location,
            password_hash('p4ssw0rd', PASSWORD_DEFAULT),
        );
        $this->characterRepository->add($character);
        $this->sharedStorage->set('character', $character);
    }

    /**
     * @Given /^there is the character "([^"]*)" with password "([^"]*)" in the (location "[^"]*")$/
     */
    public function thereIsTheCharacterWithPasswordInTheLocation(string $name, string $password, Location $location): void
    {
        $character = new Character($name, $location, password_hash($password, PASSWORD_DEFAULT));
        $this->characterRepository->add($character);
        $this->sharedStorage->set('character', $character);
    }
}
