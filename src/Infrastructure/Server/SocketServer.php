<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use Amp\Loop;
use Amp\Socket\ResourceSocket;
use Amp\Socket\Server;

use function Amp\asyncCoroutine;

final class SocketServer
{
    public function __construct(private readonly string $uri, private readonly ClientFactory $clientFactory)
    {
    }

    public function listen(): void
    {
        Loop::run(function () {
            $server = Server::listen($this->uri);
            echo 'Server listening on ' . $this->uri . PHP_EOL;
            $clientHandler = asyncCoroutine(function (ResourceSocket $socket) {
                $client = $this->clientFactory->create($socket);
                echo 'New client connected: ' . $socket->getRemoteAddress() . PHP_EOL;
                $buffer = '';
                while (null !== $chunk = yield $socket->read()) {
                    $buffer .= $chunk;
                    if (!str_contains($buffer, PHP_EOL)) {
                        continue;
                    }
                    $commands = explode(PHP_EOL, $buffer);
                    $buffer = array_pop($commands);
                    foreach ($commands as $command) {
                        echo $socket->getRemoteAddress() . ': ' . $command . PHP_EOL;
                        yield $client->handleCommand($command);
                    }
                }
            });

            while ($socket = yield $server->accept()) {
                $clientHandler($socket);
            }
        });
    }
}
