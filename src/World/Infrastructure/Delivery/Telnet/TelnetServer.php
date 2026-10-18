<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use Amp\Socket;
use PHPMud\World\Infrastructure\Server\ClientRegistry;
use PHPMud\World\Infrastructure\Server\Ticker;

use function Amp\async;

final class TelnetServer
{
    public function __construct(
        private readonly string $uri,
        private readonly TelnetClientHandler $clientHandler,
        private readonly ClientRegistry $clientRegistry,
        private readonly Ticker $ticker,
    ) {
    }

    public function listen(): void
    {
        $server = Socket\listen($this->uri);
        echo 'Server listening on '.$this->uri.PHP_EOL;
        $this->ticker->start();

        while ($socket = $server->accept()) {
            async(function () use ($socket) {
                $client = new TelnetClient($socket);
                $this->clientRegistry->add($client);
                echo 'New client connected: '.$socket->getRemoteAddress().PHP_EOL;
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
                        echo $socket->getRemoteAddress().': '.($client->isAuthenticating() ? '[password]' : $command).PHP_EOL;
                        $this->clientHandler->handleCommand($client, $command);
                    }
                }
                $this->clientRegistry->remove($client);
            });
        }
    }
}
