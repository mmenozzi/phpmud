<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\AuthenticateCharacter;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Domain\Character\CharacterName;

final readonly class AuthenticateCharacterCommand implements CommandInterface
{
    public function __construct(
        private CharacterName $characterName,
        private string $password,
        private int $failedAttempts,
    ) {
    }

    public function getCharacterName(): CharacterName
    {
        return $this->characterName;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFailedAttempts(): int
    {
        return $this->failedAttempts;
    }
}
