<?php

declare(strict_types=1);

namespace PHPMud\Application\Movement\CommandExecutor;

use PHPMud\Application\CommandInterface;
use PHPMud\Application\ExecutorInterface;
use PHPMud\Application\Movement\Command\MoveCommand;
use PHPMud\Application\Movement\Command\MoveCommandResponse;
use PHPMud\Application\Movement\View\LocationViewFactory;
use PHPMud\Domain\Exception\NoExitInDirectionException;
use PHPMud\Domain\Repository\CharacterRepositoryInterface;
use PHPMud\Domain\Repository\LocationRepositoryInterface;
use PHPMud\Domain\Service\MovementPolicy;
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
        $this->characterRepository->add($character);

        return new MoveCommandResponse(
            true,
            $direction,
            $this->locationViewFactory->create($this->locationRepository->get($destination)),
        );
    }
}
