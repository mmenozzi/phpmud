<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\AuthenticateCharacter;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Domain\Character\CharacterId;

final readonly class AuthenticateCharacterCommandResponse implements CommandResponseInterface
{
    private function __construct(
        private ?CharacterId $characterId,
        private bool $attemptsExhausted,
    ) {
    }

    public static function succeeded(CharacterId $characterId): self
    {
        return new self($characterId, false);
    }

    public static function failed(bool $attemptsExhausted): self
    {
        return new self(null, $attemptsExhausted);
    }

    public function isSuccessful(): bool
    {
        return null !== $this->characterId;
    }

    public function getCharacterId(): ?CharacterId
    {
        return $this->characterId;
    }

    public function areAttemptsExhausted(): bool
    {
        return $this->attemptsExhausted;
    }
}
