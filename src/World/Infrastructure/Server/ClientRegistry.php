<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

use PHPMud\World\Domain\Character\CharacterId;

final class ClientRegistry
{
    /**
     * @var \SplObjectStorage<Client, null>
     */
    private \SplObjectStorage $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function add(Client $client): void
    {
        $this->clients->attach($client);
    }

    public function remove(Client $client): void
    {
        $this->clients->detach($client);
    }

    public function getAuthenticatedClient(CharacterId $characterId): ?Client
    {
        foreach ($this->getAuthenticatedClients() as $client) {
            if (true === $client->getCharacterId()?->equals($characterId)) {
                return $client;
            }
        }

        return null;
    }

    /**
     * @return list<Client>
     */
    public function getAuthenticatedClients(): array
    {
        return array_values(array_filter(iterator_to_array($this->clients, false), static fn (Client $client): bool => $client->isAuthenticated()));
    }
}
