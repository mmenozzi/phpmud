<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Hook;

use Behat\Behat\Context\Context;
use PHPMud\Domain\Entity\WorldClock;
use PHPMud\Domain\Repository\CharacterRepositoryInterface;
use PHPMud\Domain\Repository\LocationRepositoryInterface;
use PHPMud\Domain\Repository\WorldClockRepositoryInterface;

final class PurgeContext implements Context
{
    public function __construct(
        private readonly LocationRepositoryInterface $locationRepository,
        private readonly CharacterRepositoryInterface $characterRepository,
        private readonly WorldClockRepositoryInterface $worldClockRepository,
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
        $this->worldClockRepository->save(new WorldClock());
    }
}
