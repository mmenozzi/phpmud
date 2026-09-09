<?php

declare(strict_types=1);

namespace PHPMud\Application\Character\CommandExecutor;

use PHPMud\Application\Character\Command\WhoAmICommand;
use PHPMud\Application\Character\Command\WhoAmICommandResponse;
use PHPMud\Application\CommandInterface;
use PHPMud\Application\CommandResponseInterface;
use PHPMud\Application\ExecutorInterface;
use Webmozart\Assert\Assert;

final readonly class WhoAmICommandExecutor implements ExecutorInterface
{
    public function execute(CommandInterface $command): CommandResponseInterface
    {
        Assert::isInstanceOf($command, WhoAmICommand::class);

        return new WhoAmICommandResponse($command->getCharacter());
    }
}
