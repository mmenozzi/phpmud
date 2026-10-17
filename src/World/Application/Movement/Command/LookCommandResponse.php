<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement\Command;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\Movement\View\LocationView;

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
