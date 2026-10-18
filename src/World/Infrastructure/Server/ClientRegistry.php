<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

use PHPMud\World\Infrastructure\Delivery\Telnet\TelnetClient;

final class ClientRegistry
{
    /**
     * @var \SplObjectStorage<TelnetClient, null>
     */
    private \SplObjectStorage $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function add(TelnetClient $client): void
    {
        $this->clients->attach($client);
    }

    public function remove(TelnetClient $client): void
    {
        $this->clients->detach($client);
    }

    /**
     * @return list<TelnetClient>
     */
    public function getAuthenticatedClients(): array
    {
        return array_values(array_filter(iterator_to_array($this->clients, false), static fn (TelnetClient $client): bool => $client->isAuthenticated()));
    }
}
