<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Application\NotifierInterface;
use PHPMud\World\Domain\Character\CharacterId;

final readonly class ClientNotifier implements NotifierInterface
{
    public function __construct(private ClientRegistry $clientRegistry)
    {
    }

    public function notify(CharacterId $recipient, NotificationInterface $notification): void
    {
        $client = $this->clientRegistry->getAuthenticatedClient($recipient);
        if (null === $client) {
            return;
        }

        $this->deliver($client, $notification);
    }

    public function notifyEveryone(NotificationInterface $notification): void
    {
        foreach ($this->clientRegistry->getAuthenticatedClients() as $client) {
            $this->deliver($client, $notification);
        }
    }

    private function deliver(Client $client, NotificationInterface $notification): void
    {
        try {
            $client->notify($notification);
        } catch (ClientDisconnectedException) {
            $this->clientRegistry->remove($client);
        }
    }
}
