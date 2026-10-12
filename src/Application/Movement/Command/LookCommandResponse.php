<?php

declare(strict_types=1);

namespace PHPMud\Application\Movement\Command;

use PHPMud\Application\CommandResponseInterface;
use PHPMud\Application\Movement\View\LocationView;

final readonly class LookCommandResponse implements CommandResponseInterface
{
    public function __construct(private LocationView $locationView)
    {
    }

    public function getLocationView(): LocationView
    {
        return $this->locationView;
    }
}
