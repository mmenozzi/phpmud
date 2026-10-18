<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\WebSocket;

use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\SocketHttpServer;
use Amp\Websocket\Server\Rfc6455Acceptor;
use Amp\Websocket\Server\Websocket;
use PHPMud\World\Infrastructure\Server\Listener;
use Psr\Log\NullLogger;

final readonly class WebSocketServer implements Listener
{
    private const int CONNECTION_LIMIT_PER_IP = 1000;

    public function __construct(
        private string $uri,
        private WebSocketClientHandler $clientHandler,
    ) {
    }

    public function start(): void
    {
        $logger = new NullLogger();
        $httpServer = SocketHttpServer::createForDirectAccess($logger, connectionLimitPerIp: self::CONNECTION_LIMIT_PER_IP);
        $httpServer->expose($this->uri);
        $websocket = new Websocket($httpServer, $logger, new Rfc6455Acceptor(), $this->clientHandler);
        $httpServer->start($websocket, new DefaultErrorHandler());
        echo 'WebSocket server listening on ws://'.$this->uri.PHP_EOL;
    }
}
