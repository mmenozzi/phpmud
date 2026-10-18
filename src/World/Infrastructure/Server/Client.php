<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Domain\Character\Character;

interface Client
{
    public function getCharacter(): ?Character;

    public function isAuthenticated(): bool;

    /**
     * @throws ClientDisconnectedException
     */
    public function send(CommandResponseInterface $commandResponse): void;
}
