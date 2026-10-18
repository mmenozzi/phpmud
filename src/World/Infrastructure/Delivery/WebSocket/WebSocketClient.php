<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\WebSocket;

use Amp\Websocket\WebsocketClient as AmpWebsocketClient;
use Amp\Websocket\WebsocketClosedException;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Infrastructure\Server\Client;
use PHPMud\World\Infrastructure\Server\ClientDisconnectedException;

final class WebSocketClient implements Client
{
    private ?CharacterId $characterId = null;

    private ?string $identifiedCharacterName = null;

    private int $failedPasswordAttempts = 0;

    public function __construct(
        private readonly AmpWebsocketClient $websocketClient,
        private readonly MessageSerializer $messageSerializer,
    ) {
    }

    public function getCharacterId(): ?CharacterId
    {
        return $this->characterId;
    }

    public function isAuthenticated(): bool
    {
        return null !== $this->characterId;
    }

    public function send(CommandResponseInterface $commandResponse): void
    {
        $this->write($this->messageSerializer->serialize($commandResponse));
    }

    public function notify(NotificationInterface $notification): void
    {
        $this->write($this->messageSerializer->serializeNotification($notification));
    }

    public function sendError(string $message): void
    {
        $this->write($this->messageSerializer->serializeError($message));
    }

    public function close(): void
    {
        $this->websocketClient->close();
    }

    public function getRemoteAddress(): string
    {
        return $this->websocketClient->getRemoteAddress()->toString();
    }

    public function identify(?string $characterName): void
    {
        $this->identifiedCharacterName = $characterName;
    }

    public function getIdentifiedCharacterName(): ?string
    {
        return $this->identifiedCharacterName;
    }

    public function getFailedPasswordAttempts(): int
    {
        return $this->failedPasswordAttempts;
    }

    public function registerFailedPasswordAttempt(): void
    {
        ++$this->failedPasswordAttempts;
    }

    public function authenticate(CharacterId $characterId): void
    {
        $this->characterId = $characterId;
        $this->identifiedCharacterName = null;
        $this->failedPasswordAttempts = 0;
    }

    private function write(string $message): void
    {
        try {
            $this->websocketClient->sendText($message);
        } catch (WebsocketClosedException $exception) {
            throw new ClientDisconnectedException($exception->getMessage(), previous: $exception);
        }
    }
}
