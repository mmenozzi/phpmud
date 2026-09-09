<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use Amp\Socket\ResourceSocket;
use PHPMud\Domain\Entity\Character;

final class Client
{
    private ?Character $character = null;

    private ?string $authenticatingCharacterName = null;

    public function __construct(
        private readonly ResourceSocket $socket,
    ) {
    }

    public function getSocket(): ResourceSocket
    {
        return $this->socket;
    }

    public function getCharacter(): ?Character
    {
        return $this->character;
    }

    public function isAuthenticated(): bool
    {
        return null !== $this->character;
    }

    public function isAuthenticating(): bool
    {
        return null !== $this->authenticatingCharacterName;
    }

    public function beginAuthenticationWithCharacterName(string $characterName): void
    {
        $this->authenticatingCharacterName = $characterName;
    }

    public function getAuthenticatingCharacterName(): string
    {
        if (null === $this->authenticatingCharacterName) {
            throw new \LogicException('No character name is being authenticated.');
        }

        return $this->authenticatingCharacterName;
    }

    public function stopAuthentication(): void
    {
        $this->authenticatingCharacterName = null;
    }

    public function authenticate(Character $character): void
    {
        $this->character = $character;
        $this->stopAuthentication();
    }
}
