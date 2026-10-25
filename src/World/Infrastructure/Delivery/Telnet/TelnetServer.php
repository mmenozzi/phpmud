<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use Amp\ByteStream\StreamException;
use Amp\Socket;
use Amp\Socket\ResourceSocket;
use PHPMud\World\Infrastructure\Server\ClientDisconnectedException;
use PHPMud\World\Infrastructure\Server\ClientRegistry;
use PHPMud\World\Infrastructure\Server\Listener;

use function Amp\async;

final class TelnetServer implements Listener
{
    public function __construct(
        private readonly string $uri,
        private readonly TelnetClientHandler $clientHandler,
        private readonly ClientRegistry $clientRegistry,
        private readonly CommandResponseRenderer $commandResponseRenderer,
    ) {
    }

    public function start(): void
    {
        $server = Socket\listen($this->uri);
        echo 'Telnet server listening on '.$this->uri.PHP_EOL;
        async(function () use ($server): void {
            while ($socket = $server->accept()) {
                async(fn () => $this->handleConnection($socket));
            }
        });
    }

    private function handleConnection(ResourceSocket $socket): void
    {
        $client = new TelnetClient($socket, $this->commandResponseRenderer);
        $this->clientRegistry->add($client);
        echo 'New client connected: '.$socket->getRemoteAddress().PHP_EOL;
        try {
            $this->clientHandler->handleConnection($client);
            $buffer = '';
            while (null !== $chunk = $socket->read()) {
                $buffer .= $chunk;
                if (!str_contains($buffer, PHP_EOL)) {
                    continue;
                }
                $commands = explode(PHP_EOL, $buffer);
                $buffer = array_pop($commands);
                foreach ($commands as $command) {
                    $command = rtrim(Telnet::stripNegotiation($command), "\r");
                    echo $socket->getRemoteAddress().': '.($client->getLoginState()->expectsPassword() ? '[password]' : $command).PHP_EOL;
                    $this->clientHandler->handleCommand($client, $command);
                }
            }
        } catch (ClientDisconnectedException|StreamException) {
        } finally {
            $this->clientRegistry->remove($client);
        }
    }
}
