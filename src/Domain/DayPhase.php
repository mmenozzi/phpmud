<?php

declare(strict_types=1);

namespace PHPMud\Domain;

enum DayPhase: string
{
    case Dawn = 'dawn';
    case Noon = 'noon';
    case Sunset = 'sunset';
    case Night = 'night';

    public static function startingAt(int $hour): ?self
    {
        foreach (self::cases() as $dayPhase) {
            if ($dayPhase->startingHour() === $hour) {
                return $dayPhase;
            }
        }

        return null;
    }

    public function startingHour(): int
    {
        return match ($this) {
            self::Dawn => 6,
            self::Noon => 12,
            self::Sunset => 18,
            self::Night => 21,
        };
    }
}
