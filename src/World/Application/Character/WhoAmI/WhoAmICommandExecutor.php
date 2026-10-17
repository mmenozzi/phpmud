<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Character\WhoAmI;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\ExecutorInterface;
use Webmozart\Assert\Assert;

final readonly class WhoAmICommandExecutor implements ExecutorInterface
{
    public function execute(CommandInterface $command): CommandResponseInterface
    {
        Assert::isInstanceOf($command, WhoAmICommand::class);

        return new WhoAmICommandResponse($command->getCharacter());
    }
}
