<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\AuthenticateCharacter;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\ExecutorInterface;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class AuthenticateCharacterCommandExecutor implements ExecutorInterface
{
    private const int MAX_FAILED_ATTEMPTS = 3;

    public function __construct(private CharacterRepositoryInterface $characterRepository)
    {
    }

    public function execute(CommandInterface $command): AuthenticateCharacterCommandResponse
    {
        Assert::isInstanceOf($command, AuthenticateCharacterCommand::class);
        $character = $this->characterRepository->findOneByName($command->getCharacterName());
        if (null === $character || !$character->isPasswordValid($command->getPassword())) {
            return AuthenticateCharacterCommandResponse::failed($command->getFailedAttempts() + 1 >= self::MAX_FAILED_ATTEMPTS);
        }

        return AuthenticateCharacterCommandResponse::succeeded($character->getId());
    }
}
