<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement\Move;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\Movement\LocationView;
use PHPMud\World\Domain\Location\Direction;

final readonly class MoveCommandResponse implements CommandResponseInterface
{
    public function __construct(
        private bool $successful,
        private Direction $direction,
        private LocationView $locationView,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getDirection(): Direction
    {
        return $this->direction;
    }

    public function getLocationView(): LocationView
    {
        return $this->locationView;
    }
}
