<?php

declare(strict_types=1);

namespace PHPMud\Application\Movement\Command;

use PHPMud\Application\CommandResponseInterface;
use PHPMud\Application\Movement\View\LocationView;
use PHPMud\Domain\Location\Direction;

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
