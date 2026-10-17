<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Repository\Filesystem;

use PHPMud\Domain\Entity\WorldClock;
use PHPMud\Domain\Repository\WorldClockRepositoryInterface;
use Webmozart\Assert\Assert;

final class WorldClockRepository implements WorldClockRepositoryInterface
{
    public function __construct(private readonly string $filepath)
    {
        if (!file_exists($this->filepath)) {
            $this->save(new WorldClock());
        }
    }

    public function get(): WorldClock
    {
        $data = file_get_contents($this->filepath);
        Assert::string($data);
        $worldClock = unserialize($data);
        Assert::isInstanceOf($worldClock, WorldClock::class);

        return $worldClock;
    }

    public function save(WorldClock $worldClock): void
    {
        file_put_contents($this->filepath.'.tmp', serialize($worldClock));
        rename($this->filepath.'.tmp', $this->filepath);
    }
}
