<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

use PHPMud\World\Infrastructure\Delivery\Telnet\TelnetServer;

final readonly class GameServer
{
    public function __construct(
        private Ticker $ticker,
        private TelnetServer $telnetServer,
    ) {
    }

    public function run(): void
    {
        $this->ticker->start();
        $this->telnetServer->listen();
    }
}
