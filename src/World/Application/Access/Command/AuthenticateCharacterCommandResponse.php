<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\Command;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Domain\Character\Character;

final readonly class AuthenticateCharacterCommandResponse implements CommandResponseInterface
{
    public function __construct(private ?Character $character)
    {
    }

    public function isSuccessful(): bool
    {
        return null !== $this->character;
    }

    public function getCharacter(): ?Character
    {
        return $this->character;
    }
}
