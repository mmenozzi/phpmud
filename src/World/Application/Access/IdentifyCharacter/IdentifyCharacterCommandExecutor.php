<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\IdentifyCharacter;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\ExecutorInterface;
use PHPMud\World\Domain\Character\CharacterName;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use PHPMud\World\Domain\Character\InvalidCharacterNameException;
use Webmozart\Assert\Assert;

final readonly class IdentifyCharacterCommandExecutor implements ExecutorInterface
{
    public function __construct(private CharacterRepositoryInterface $characterRepository)
    {
    }

    public function execute(CommandInterface $command): IdentifyCharacterCommandResponse
    {
        Assert::isInstanceOf($command, IdentifyCharacterCommand::class);
        try {
            $characterName = CharacterName::fromString($command->getCharacterName());
        } catch (InvalidCharacterNameException) {
            return IdentifyCharacterCommandResponse::notAllowed();
        }

        $character = $this->characterRepository->findOneByName($characterName);
        if (null === $character) {
            return IdentifyCharacterCommandResponse::unknown($characterName);
        }

        return IdentifyCharacterCommandResponse::existing($character->getName());
    }
}
