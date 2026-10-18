<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\AuthenticateCharacter;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Domain\Character\CharacterId;

final readonly class AuthenticateCharacterCommandResponse implements CommandResponseInterface
{
    public function __construct(private ?CharacterId $characterId)
    {
    }

    public function isSuccessful(): bool
    {
        return null !== $this->characterId;
    }

    public function getCharacterId(): ?CharacterId
    {
        return $this->characterId;
    }
}
