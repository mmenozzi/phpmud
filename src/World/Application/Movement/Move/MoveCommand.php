<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement\Move;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Location\Direction;

final readonly class MoveCommand implements CommandInterface
{
    public function __construct(private Character $character, private Direction $direction)
    {
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }

    public function getDirection(): Direction
    {
        return $this->direction;
    }
}
