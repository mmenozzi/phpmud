<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Hook;

use Amp\Loop;
use Behat\Behat\Context\Context;
use PHPMud\Domain\Repository\CharacterRepositoryInterface;
use PHPMud\Domain\Repository\LocationRepositoryInterface;

final class PurgeContext implements Context
{
    public function __construct(
        private readonly LocationRepositoryInterface $locationRepository,
        private readonly CharacterRepositoryInterface $characterRepository
    ) {
    }

    /**
     * @BeforeScenario
     */
    public function purge(): void
    {
        foreach ($this->locationRepository->findAll() as $location) {
            $this->locationRepository->remove($location);
        }
        foreach ($this->characterRepository->findAll() as $character) {
            $this->characterRepository->remove($character);
        }
    }
}
