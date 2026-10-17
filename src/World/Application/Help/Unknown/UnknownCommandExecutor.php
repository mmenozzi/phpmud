<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Help\Unknown;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\ExecutorInterface;
use Webmozart\Assert\Assert;

final class UnknownCommandExecutor implements ExecutorInterface
{
    public function execute(CommandInterface $command): CommandResponseInterface
    {
        Assert::isInstanceOf($command, UnknownCommand::class);

        return new UnknownCommandResponse();
    }
}
