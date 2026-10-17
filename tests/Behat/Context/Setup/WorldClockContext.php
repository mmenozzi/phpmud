<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use PHPMud\Domain\Entity\WorldClock;
use PHPMud\Domain\Repository\WorldClockRepositoryInterface;

final class WorldClockContext implements Context
{
    public function __construct(private readonly WorldClockRepositoryInterface $worldClockRepository)
    {
    }

    /**
     * @Given /^it is (\d+) o'clock$/
     */
    public function itIsOClock(int $hour): void
    {
        $this->worldClockRepository->save(new WorldClock($hour));
    }
}
