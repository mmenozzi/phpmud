<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\IdentifyCharacter;

use PHPMud\World\Application\CommandResponseInterface;

final readonly class IdentifyCharacterCommandResponse implements CommandResponseInterface
{
    public function __construct(
        private string $characterName,
        private bool $exists,
    ) {
    }

    public function getCharacterName(): string
    {
        return $this->characterName;
    }

    public function exists(): bool
    {
        return $this->exists;
    }
}
