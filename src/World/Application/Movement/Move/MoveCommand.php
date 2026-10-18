<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement\Move;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Domain\Location\Direction;

final readonly class MoveCommand implements CommandInterface
{
    public function __construct(private CharacterId $characterId, private Direction $direction)
    {
    }

    public function getCharacterId(): CharacterId
    {
        return $this->characterId;
    }

    public function getDirection(): Direction
    {
        return $this->direction;
    }
}
