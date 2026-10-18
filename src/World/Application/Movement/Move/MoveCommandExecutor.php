<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement\Move;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\DomainEventDispatcher;
use PHPMud\World\Application\ExecutorInterface;
use PHPMud\World\Application\Movement\LocationViewFactory;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use PHPMud\World\Domain\Location\LocationRepositoryInterface;
use PHPMud\World\Domain\Movement\MovementPolicy;
use PHPMud\World\Domain\Movement\NoExitInDirectionException;
use Webmozart\Assert\Assert;

final readonly class MoveCommandExecutor implements ExecutorInterface
{
    public function __construct(
        private CharacterRepositoryInterface $characterRepository,
        private LocationRepositoryInterface $locationRepository,
        private MovementPolicy $movementPolicy,
        private LocationViewFactory $locationViewFactory,
        private DomainEventDispatcher $domainEventDispatcher,
    ) {
    }

    public function execute(CommandInterface $command): MoveCommandResponse
    {
        Assert::isInstanceOf($command, MoveCommand::class);
        $character = $this->characterRepository->get($command->getCharacterId());
        $direction = $command->getDirection();
        $current = $this->locationRepository->get($character->getLocationId());
        try {
            $destination = $this->movementPolicy->move($character, $current, $direction);
        } catch (NoExitInDirectionException) {
            return new MoveCommandResponse(false, $direction, $this->locationViewFactory->create($current, $character->getId()));
        }

        $events = $character->releaseEvents();
        $this->characterRepository->save($character);
        $this->domainEventDispatcher->dispatch(...$events);

        return new MoveCommandResponse(
            true,
            $direction,
            $this->locationViewFactory->create($this->locationRepository->get($destination), $character->getId()),
        );
    }
}
