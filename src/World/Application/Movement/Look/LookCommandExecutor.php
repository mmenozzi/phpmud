<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement\Look;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\ExecutorInterface;
use PHPMud\World\Application\Movement\LocationViewFactory;
use PHPMud\World\Domain\Location\LocationRepositoryInterface;
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
