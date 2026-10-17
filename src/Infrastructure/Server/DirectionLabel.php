<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use PHPMud\Domain\Direction;

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
