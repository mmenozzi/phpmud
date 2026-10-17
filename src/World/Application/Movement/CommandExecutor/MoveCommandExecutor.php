<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement\CommandExecutor;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\ExecutorInterface;
use PHPMud\World\Application\Movement\Command\MoveCommand;
use PHPMud\World\Application\Movement\Command\MoveCommandResponse;
use PHPMud\World\Application\Movement\View\LocationViewFactory;
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
    ) {
    }

    public function execute(CommandInterface $command): MoveCommandResponse
    {
        Assert::isInstanceOf($command, MoveCommand::class);
        $character = $command->getCharacter();
        $direction = $command->getDirection();
        $current = $this->locationRepository->get($character->getLocationId());
        try {
            $destination = $this->movementPolicy->move($character, $current, $direction);
        } catch (NoExitInDirectionException) {
            return new MoveCommandResponse(false, $direction, $this->locationViewFactory->create($current));
        }

        // Add method also flushes the changes to the repository
        $this->characterRepository->save($character);

        return new MoveCommandResponse(
            true,
            $direction,
            $this->locationViewFactory->create($this->locationRepository->get($destination)),
        );
    }
}
