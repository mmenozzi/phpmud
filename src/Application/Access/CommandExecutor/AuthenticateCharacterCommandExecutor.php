<?php

declare(strict_types=1);

namespace PHPMud\Application\Access\CommandExecutor;

use PHPMud\Application\Access\Command\AuthenticateCharacterCommand;
use PHPMud\Application\Access\Command\AuthenticateCharacterCommandResponse;
use PHPMud\Application\CommandInterface;
use PHPMud\Application\ExecutorInterface;
use PHPMud\Domain\Character\CharacterRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class AuthenticateCharacterCommandExecutor implements ExecutorInterface
{
    public function __construct(private CharacterRepositoryInterface $characterRepository)
    {
    }

    public function execute(CommandInterface $command): AuthenticateCharacterCommandResponse
    {
        Assert::isInstanceOf($command, AuthenticateCharacterCommand::class);
        $character = $this->characterRepository->findOneByName($command->getCharacterName());
        if (null === $character || !$character->isPasswordValid($command->getPassword())) {
            return new AuthenticateCharacterCommandResponse(null);
        }

        return new AuthenticateCharacterCommandResponse($character);
    }
}
