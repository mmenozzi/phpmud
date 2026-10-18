<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Domain\Character\CharacterId;

interface Client
{
    public function getCharacterId(): ?CharacterId;

    public function isAuthenticated(): bool;

    /**
     * @throws ClientDisconnectedException
     */
    public function send(CommandResponseInterface $commandResponse): void;

    /**
     * @throws ClientDisconnectedException
     */
    public function notify(NotificationInterface $notification): void;
}
