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
    private const DEFAULT_CHARACTER_FIRST_NAME = 'John';
    private const DEFAULT_CHARACTER_LAST_NAME = 'Doe';

    public function __construct(
        private readonly CharacterRepositoryInterface $characterRepository,
        private readonly SharedStorage $sharedStorage
    ) {
    }

    /**
     * @Given /^my character is in the (location "([^"]*)")$/
     */
    public function myCharacterIsInTheLocation(Location $location): void
    {
        $character = new Character(self::DEFAULT_CHARACTER_FIRST_NAME, self::DEFAULT_CHARACTER_LAST_NAME, $location);
        $this->characterRepository->add($character);
        $this->sharedStorage->set('character', $character);
    }
}
