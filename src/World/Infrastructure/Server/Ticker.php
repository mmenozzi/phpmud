<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

use PHPMud\World\Application\CommandRouter;
use PHPMud\World\Application\Time\Tick\TickCommand;
use Revolt\EventLoop;

use function Amp\async;

final readonly class Ticker
{
    public function __construct(
        private float $tickInterval,
        private CommandRouter $commandRouter,
    ) {
    }

    public function start(): void
    {
        EventLoop::repeat($this->tickInterval, function (): void {
            async($this->tick(...));
        });
    }

    private function tick(): void
    {
        $command = new TickCommand();
        $this->commandRouter->resolve($command)->execute($command);
    }
}
