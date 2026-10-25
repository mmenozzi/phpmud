<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use PHPMud\World\Domain\Character\Sex;
use PHPMud\World\Domain\Location\Direction;

final class MovementMessage
{
    public static function left(string $characterName, ?Direction $via): string
    {
        return match ($via) {
            null => sprintf('%s se ne va.', $characterName),
            Direction::Up => sprintf('%s va verso l\'alto.', $characterName),
            Direction::Down => sprintf('%s va verso il basso.', $characterName),
            default => sprintf('%s va verso %s.', $characterName, mb_strtolower(DirectionLabel::of($via))),
        };
    }

    public static function arrived(string $characterName, ?Direction $via): string
    {
        return match ($via?->opposite()) {
            null => sprintf('%s arriva.', $characterName),
            Direction::Up => sprintf('%s arriva dall\'alto.', $characterName),
            Direction::Down => sprintf('%s arriva dal basso.', $characterName),
            default => sprintf('%s arriva da %s.', $characterName, mb_strtolower(DirectionLabel::of($via->opposite()))),
        };
    }

    public static function appeared(string $characterName, Sex $sex): string
    {
        return sprintf('%s è %s dal nulla.', $characterName, Sex::Female === $sex ? 'apparsa' : 'apparso');
    }
}
