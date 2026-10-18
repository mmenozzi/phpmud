<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use Amp\ByteStream\StreamException;
use Amp\Socket\ResourceSocket;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Infrastructure\Server\Client;
use PHPMud\World\Infrastructure\Server\ClientDisconnectedException;

final class TelnetClient implements Client
{
    private ?CharacterId $characterId = null;

    private ?string $authenticatingCharacterName = null;

    private int $failedPasswordAttempts = 0;

    public function __construct(
        private readonly ResourceSocket $socket,
        private readonly CommandResponseRenderer $commandResponseRenderer,
    ) {
    }

    public function getSocket(): ResourceSocket
    {
        return $this->socket;
    }

    public function send(CommandResponseInterface $commandResponse): void
    {
        $text = $this->commandResponseRenderer->render($commandResponse);
        if (null === $text) {
            return;
        }

        try {
            $this->socket->write($text);
        } catch (StreamException $exception) {
            throw new ClientDisconnectedException($exception->getMessage(), previous: $exception);
        }
    }

    public function getCharacterId(): ?CharacterId
    {
        return $this->characterId;
    }

    public function isAuthenticated(): bool
    {
        return null !== $this->characterId;
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

    public function registerFailedPasswordAttempt(): int
    {
        return ++$this->failedPasswordAttempts;
    }

    public function stopAuthentication(): void
    {
        $this->authenticatingCharacterName = null;
        $this->failedPasswordAttempts = 0;
    }

    public function authenticate(CharacterId $characterId): void
    {
        $this->characterId = $characterId;
        $this->stopAuthentication();
    }
}
