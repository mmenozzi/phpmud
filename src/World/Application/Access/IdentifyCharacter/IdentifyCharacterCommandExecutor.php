<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\IdentifyCharacter;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\ExecutorInterface;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
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
