<?php

declare(strict_types=1);

namespace PHPMud\Application\Time\CommandExecutor;

use PHPMud\Application\CommandInterface;
use PHPMud\Application\CommandResponseInterface;
use PHPMud\Application\ExecutorInterface;
use PHPMud\Application\Time\Command\TickCommand;
use PHPMud\Application\Time\Command\TickCommandResponse;
use PHPMud\Domain\Repository\WorldClockRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class TickCommandExecutor implements ExecutorInterface
{
    public function __construct(private WorldClockRepositoryInterface $worldClockRepository)
    {
    }

    public function execute(CommandInterface $command): CommandResponseInterface
    {
        Assert::isInstanceOf($command, TickCommand::class);
        $worldClock = $this->worldClockRepository->get();
        $startedDayPhase = $worldClock->tick();
        $this->worldClockRepository->save($worldClock);

        return new TickCommandResponse($startedDayPhase);
    }
}
