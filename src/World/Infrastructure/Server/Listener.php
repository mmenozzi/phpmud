<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

interface Listener
{
    public function start(): void;
}
