<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\CreateCharacter;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Domain\Character\CharacterId;

final readonly class CreateCharacterCommandResponse implements CommandResponseInterface
{
    private function __construct(
        private ?CharacterId $characterId,
        private ?CreationFailure $failure,
    ) {
    }

    public static function succeeded(CharacterId $characterId): self
    {
        return new self($characterId, null);
    }

    public static function failed(CreationFailure $failure): self
    {
        return new self(null, $failure);
    }

    public function isSuccessful(): bool
    {
        return null !== $this->characterId;
    }

    public function getCharacterId(): ?CharacterId
    {
        return $this->characterId;
    }

    public function getFailure(): ?CreationFailure
    {
        return $this->failure;
    }
}
