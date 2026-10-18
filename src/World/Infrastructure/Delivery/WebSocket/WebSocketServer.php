<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\WebSocket;

use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Router;
use Amp\Http\Server\SocketHttpServer;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Websocket\Server\AllowOriginAcceptor;
use Amp\Websocket\Server\Websocket;
use PHPMud\World\Infrastructure\Server\Listener;
use Psr\Log\NullLogger;

final readonly class WebSocketServer implements Listener
{
    private const int CONNECTION_LIMIT_PER_IP = 1000;

    private const string WEBSOCKET_PATH = '/ws';

    /**
     * @param list<string> $allowedOrigins
     */
    public function __construct(
        private string $uri,
        private array $allowedOrigins,
        private string $clientDirectory,
        private WebSocketClientHandler $clientHandler,
    ) {
    }

    public function start(): void
    {
        $logger = new NullLogger();
        $errorHandler = new DefaultErrorHandler();
        $httpServer = SocketHttpServer::createForDirectAccess($logger, connectionLimitPerIp: self::CONNECTION_LIMIT_PER_IP);
        $httpServer->expose($this->uri);
        $router = new Router($httpServer, $logger, $errorHandler);
        $router->addRoute('GET', self::WEBSOCKET_PATH, new Websocket($httpServer, $logger, new AllowOriginAcceptor($this->allowedOrigins), $this->clientHandler));
        $router->setFallback(new DocumentRoot($httpServer, $errorHandler, $this->clientDirectory));
        $httpServer->start($router, $errorHandler);
        echo 'WebSocket server listening on ws://'.$this->uri.self::WEBSOCKET_PATH.', browser client on http://'.$this->uri.'/'.PHP_EOL;
    }
}
