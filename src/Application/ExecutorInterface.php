<?php

declare(strict_types=1);

namespace PHPMud\Application;

interface ExecutorInterface
{
    public function execute(CommandInterface $command): CommandResponseInterface;
}
