<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use PHPMud\World\Domain\Location\Direction;

final class DirectionLabel
{
    public static function of(Direction $direction): string
    {
        return match ($direction) {
            Direction::North => 'Nord',
            Direction::East => 'Est',
            Direction::South => 'Sud',
            Direction::West => 'Ovest',
            Direction::Up => 'Su',
            Direction::Down => 'Giù',
        };
    }
}
