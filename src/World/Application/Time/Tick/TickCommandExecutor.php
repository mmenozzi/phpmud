<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Time\Tick;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\DomainEventDispatcher;
use PHPMud\World\Application\ExecutorInterface;
use PHPMud\World\Domain\Time\WorldClockRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class TickCommandExecutor implements ExecutorInterface
{
    public function __construct(
        private WorldClockRepositoryInterface $worldClockRepository,
        private DomainEventDispatcher $domainEventDispatcher,
    ) {
    }

    public function execute(CommandInterface $command): CommandResponseInterface
    {
        Assert::isInstanceOf($command, TickCommand::class);
        $worldClock = $this->worldClockRepository->get();
        $worldClock->tick();
        $events = $worldClock->releaseEvents();
        $this->worldClockRepository->save($worldClock);
        $this->domainEventDispatcher->dispatch(...$events);

        return new TickCommandResponse();
    }
}
