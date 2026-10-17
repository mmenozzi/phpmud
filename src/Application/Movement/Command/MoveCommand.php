<?php

declare(strict_types=1);

namespace PHPMud\Application\Movement\Command;

use PHPMud\Application\CommandInterface;
use PHPMud\Domain\Character\Character;
use PHPMud\Domain\Location\Direction;

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
