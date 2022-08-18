<?php

declare(strict_types=1);

namespace PHPMud\Domain;

enum Direction: string
{
    case North = 'north';
    case East = 'east';
    case South = 'south';
    case West = 'west';
    case Up = 'up';
    case Down = 'down';

    public function opposite(): self
    {
        return match ($this) {
            self::North => self::South,
            self::East => self::West,
            self::South => self::North,
            self::West => self::East,
            self::Up => self::Down,
            self::Down => self::Up,
        };
    }
}
