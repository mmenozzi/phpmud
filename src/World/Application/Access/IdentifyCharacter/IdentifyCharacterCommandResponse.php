<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\IdentifyCharacter;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Domain\Character\CharacterName;

final readonly class IdentifyCharacterCommandResponse implements CommandResponseInterface
{
    private function __construct(
        private ?CharacterName $characterName,
        private bool $exists,
    ) {
    }

    public static function existing(CharacterName $characterName): self
    {
        return new self($characterName, true);
    }

    public static function unknown(CharacterName $characterName): self
    {
        return new self($characterName, false);
    }

    public static function notAllowed(): self
    {
        return new self(null, false);
    }

    public function getCharacterName(): ?CharacterName
    {
        return $this->characterName;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function isNameAllowed(): bool
    {
        return null !== $this->characterName;
    }
}
