<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

use Revolt\EventLoop;

final readonly class GameServer
{
    /**
     * @param iterable<Listener> $listeners
     */
    public function __construct(
        private Ticker $ticker,
        private iterable $listeners,
    ) {
    }

    public function run(): void
    {
        EventLoop::setErrorHandler(static function (\Throwable $error): void {
            UnexpectedErrorLogger::log('Event loop', $error);
        });
        $this->ticker->start();
        foreach ($this->listeners as $listener) {
            $listener->start();
        }
        EventLoop::run();
    }
}
