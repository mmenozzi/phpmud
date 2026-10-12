<?php

declare(strict_types=1);

namespace PHPMud\Application\Help\CommandExecutor;

use PHPMud\Application\CommandInterface;
use PHPMud\Application\CommandResponseInterface;
use PHPMud\Application\ExecutorInterface;
use PHPMud\Application\Help\Command\UnknownCommand;
use PHPMud\Application\Help\Command\UnknownCommandResponse;
use Webmozart\Assert\Assert;

final class UnknownCommandExecutor implements ExecutorInterface
{
    public function execute(CommandInterface $command): CommandResponseInterface
    {
        Assert::isInstanceOf($command, UnknownCommand::class);

        return new UnknownCommandResponse();
    }
}
