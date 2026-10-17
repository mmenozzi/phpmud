<?php

declare(strict_types=1);

namespace PHPMud\Application\Access\Command;

use PHPMud\Application\CommandInterface;

final readonly class AuthenticateCharacterCommand implements CommandInterface
{
    public function __construct(
        private string $characterName,
        private string $password,
    ) {
    }

    public function getCharacterName(): string
    {
        return $this->characterName;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
