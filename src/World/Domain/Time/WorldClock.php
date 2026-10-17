<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Time;

final class WorldClock
{
    private const int HOURS_PER_DAY = 24;

    public function __construct(private int $hour = 0)
    {
        if ($hour < 0 || $hour >= self::HOURS_PER_DAY) {
            throw new \InvalidArgumentException(sprintf('Hour must be between 0 and %d, %d given.', self::HOURS_PER_DAY - 1, $hour));
        }
    }

    public function getHour(): int
    {
        return $this->hour;
    }

    public function tick(): ?DayPhase
    {
        $this->hour = ($this->hour + 1) % self::HOURS_PER_DAY;

        return DayPhase::startingAt($this->hour);
    }
}
