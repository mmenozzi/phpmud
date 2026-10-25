<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\CreateCharacter;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Domain\Character\Sex;

final readonly class CreateCharacterCommand implements CommandInterface
{
    public function __construct(
        private string $characterName,
        private string $password,
        private Sex $sex,
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

    public function getSex(): Sex
    {
        return $this->sex;
    }
}
