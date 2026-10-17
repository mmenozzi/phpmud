<?php

declare(strict_types=1);

namespace PHPMud\Application\Access\CommandExecutor;

use PHPMud\Application\Access\Command\IdentifyCharacterCommand;
use PHPMud\Application\Access\Command\IdentifyCharacterCommandResponse;
use PHPMud\Application\CommandInterface;
use PHPMud\Application\ExecutorInterface;
use PHPMud\Domain\Repository\CharacterRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class IdentifyCharacterCommandExecutor implements ExecutorInterface
{
    public function __construct(private CharacterRepositoryInterface $characterRepository)
    {
    }

    public function execute(CommandInterface $command): IdentifyCharacterCommandResponse
    {
        Assert::isInstanceOf($command, IdentifyCharacterCommand::class);
        $characterName = $command->getCharacterName();

        return new IdentifyCharacterCommandResponse(
            $characterName,
            null !== $this->characterRepository->findOneByName($characterName),
        );
    }
}
