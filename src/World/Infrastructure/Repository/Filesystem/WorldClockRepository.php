<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Repository\Filesystem;

use PHPMud\World\Domain\Time\WorldClock;
use PHPMud\World\Domain\Time\WorldClockRepositoryInterface;
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
        $tmpFilepath = tempnam(\dirname($this->filepath), basename($this->filepath));
        Assert::string($tmpFilepath);
        file_put_contents($tmpFilepath, serialize($worldClock));
        rename($tmpFilepath, $this->filepath);
    }
}
