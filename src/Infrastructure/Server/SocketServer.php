<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use Amp\Socket;

use function Amp\async;

final class SocketServer
{
    public function __construct(private readonly string $uri, private readonly ClientFactory $clientFactory)
    {
    }

    public function listen(): void
    {
        $server = Socket\listen($this->uri);
        echo 'Server listening on '.$this->uri.PHP_EOL;

        while ($socket = $server->accept()) {
            async(function () use ($socket) {
                $client = $this->clientFactory->create($socket);
                echo 'New client connected: '.$socket->getRemoteAddress().PHP_EOL;
                $buffer = '';
                while (null !== $chunk = $socket->read()) {
                    $buffer .= $chunk;
                    if (!str_contains($buffer, PHP_EOL)) {
                        continue;
                    }
                    $commands = explode(PHP_EOL, $buffer);
                    $buffer = array_pop($commands);
                    foreach ($commands as $command) {
                        echo $socket->getRemoteAddress().': '.$command.PHP_EOL;
                        $client->handleCommand($command);
                    }
                }
            });
        }
    }
}
