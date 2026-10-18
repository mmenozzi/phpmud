<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Time\CurrentDayPhase;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\ExecutorInterface;
use PHPMud\World\Domain\Time\WorldClockRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class CurrentDayPhaseCommandExecutor implements ExecutorInterface
{
    public function __construct(private WorldClockRepositoryInterface $worldClockRepository)
    {
    }

    public function execute(CommandInterface $command): CommandResponseInterface
    {
        Assert::isInstanceOf($command, CurrentDayPhaseCommand::class);

        return new CurrentDayPhaseCommandResponse($this->worldClockRepository->get()->dayPhase());
    }
}
