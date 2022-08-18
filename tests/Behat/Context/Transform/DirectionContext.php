<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use PHPMud\Domain\Direction;

final class DirectionContext implements Context
{
    /**
     * @Transform /^(north|east|south|west|up|down)$/
     */
    public function transformDirectionFromString(string $direction): Direction
    {
        return Direction::from($direction);
    }
}
