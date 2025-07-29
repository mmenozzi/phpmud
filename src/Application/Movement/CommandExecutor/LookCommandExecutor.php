<?php

declare(strict_types=1);

namespace PHPMud\Application\Movement\CommandExecutor;

use PHPMud\Application\CommandInterface;
use PHPMud\Application\CommandResponseInterface;
use PHPMud\Application\ExecutorInterface;
use PHPMud\Application\Movement\Command\LookCommand;
use PHPMud\Application\Movement\Command\LookCommandResponse;
use Webmozart\Assert\Assert;

final readonly class LookCommandExecutor implements ExecutorInterface
{
    public function execute(CommandInterface $command): CommandResponseInterface
    {
        Assert::isInstanceOf($command, LookCommand::class);
        $character = $command->getCharacter();
        Assert::notNull($character->getLocation());

        return new LookCommandResponse($character->getLocation());
    }
}
