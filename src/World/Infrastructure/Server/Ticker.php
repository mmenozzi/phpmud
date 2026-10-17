<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

use Amp\ByteStream\StreamException;
use PHPMud\World\Application\CommandRouter;
use PHPMud\World\Application\Time\Tick\TickCommand;
use PHPMud\World\Application\Time\Tick\TickCommandResponse;
use Revolt\EventLoop;
use Webmozart\Assert\Assert;

use function Amp\async;

final readonly class Ticker
{
    public function __construct(
        private float $tickInterval,
        private CommandRouter $commandRouter,
        private CommandResponseHandler $commandResponseHandler,
        private ClientRegistry $clientRegistry,
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
        $response = $this->commandRouter->resolve($command)->execute($command);
        Assert::isInstanceOf($response, TickCommandResponse::class);
        if (null === $response->getStartedDayPhase()) {
            return;
        }

        foreach ($this->clientRegistry->getAuthenticatedClients() as $client) {
            try {
                $this->commandResponseHandler->handle($client, $response);
            } catch (StreamException) {
                $this->clientRegistry->remove($client);
            }
        }
    }
}
