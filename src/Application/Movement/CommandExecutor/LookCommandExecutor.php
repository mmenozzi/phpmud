<?php

declare(strict_types=1);

namespace PHPMud\Application\Movement\CommandExecutor;

use PHPMud\Application\CommandInterface;
use PHPMud\Application\CommandResponseInterface;
use PHPMud\Application\ExecutorInterface;
use PHPMud\Application\Movement\Command\LookCommand;
use PHPMud\Application\Movement\Command\LookCommandResponse;
use PHPMud\Application\Movement\View\LocationViewFactory;
use PHPMud\Domain\Repository\LocationRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class LookCommandExecutor implements ExecutorInterface
{
    public function __construct(
        private LocationRepositoryInterface $locationRepository,
        private LocationViewFactory $locationViewFactory,
    ) {
    }

    public function execute(CommandInterface $command): CommandResponseInterface
    {
        Assert::isInstanceOf($command, LookCommand::class);
        $location = $this->locationRepository->get($command->getCharacter()->getLocationId());

        return new LookCommandResponse($this->locationViewFactory->create($location));
    }
}
