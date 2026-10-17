<?php

declare(strict_types=1);

namespace PHPMud\Application\Access\Command;

use PHPMud\Application\CommandResponseInterface;
use PHPMud\Domain\Entity\Character;

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
