<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Hook;

use Behat\Behat\Context\Context;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use PHPMud\World\Domain\Location\LocationRepositoryInterface;
use PHPMud\World\Domain\Time\WorldClock;
use PHPMud\World\Domain\Time\WorldClockRepositoryInterface;

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
