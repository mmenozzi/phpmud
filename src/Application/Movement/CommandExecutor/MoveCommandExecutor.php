<?php

declare(strict_types=1);

namespace PHPMud\Application\Movement\CommandExecutor;

use PHPMud\Application\CommandInterface;
use PHPMud\Application\ExecutorInterface;
use PHPMud\Application\Movement\Command\MoveCommand;
use PHPMud\Application\Movement\Command\MoveCommandResponse;
use PHPMud\Domain\Repository\CharacterRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class MoveCommandExecutor implements ExecutorInterface
{
    public function __construct(
        private CharacterRepositoryInterface $characterRepository,
    ) {
    }

    public function execute(CommandInterface $command): MoveCommandResponse
    {
        Assert::isInstanceOf($command, MoveCommand::class);
        $character = $command->getCharacter();
        $direction = $command->getDirection();
        try {
            $character->moveTo($direction);
        } catch (\Throwable) {
            return new MoveCommandResponse(false, $direction, $character->getLocation());
        }

        // Add method also flushes the changes to the repository
        $this->characterRepository->add($character);

        return new MoveCommandResponse(true, $direction, $character->getLocation());
    }
}
