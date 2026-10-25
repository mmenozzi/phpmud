<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\CreateCharacter;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\DomainEventDispatcher;
use PHPMud\World\Application\ExecutorInterface;
use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Character\CharacterName;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use PHPMud\World\Domain\Character\InvalidCharacterNameException;
use PHPMud\World\Domain\Location\LocationRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class CreateCharacterCommandExecutor implements ExecutorInterface
{
    public const int MIN_PASSWORD_LENGTH = 4;

    public function __construct(
        private CharacterRepositoryInterface $characterRepository,
        private LocationRepositoryInterface $locationRepository,
        private DomainEventDispatcher $domainEventDispatcher,
    ) {
    }

    public function execute(CommandInterface $command): CreateCharacterCommandResponse
    {
        Assert::isInstanceOf($command, CreateCharacterCommand::class);
        try {
            $characterName = CharacterName::fromString($command->getCharacterName());
        } catch (InvalidCharacterNameException) {
            return CreateCharacterCommandResponse::failed(CreationFailure::NameNotAllowed);
        }
        // Uniqueness spans every Character aggregate, so the use case enforces it; executors run one at a time on the event loop.
        if (null !== $this->characterRepository->findOneByName($characterName)) {
            return CreateCharacterCommandResponse::failed(CreationFailure::NameTaken);
        }
        if (mb_strlen($command->getPassword()) < self::MIN_PASSWORD_LENGTH) {
            return CreateCharacterCommandResponse::failed(CreationFailure::PasswordTooShort);
        }

        $character = Character::create(
            $characterName,
            $command->getSex(),
            $this->locationRepository->getStartingLocation()->getId(),
            password_hash($command->getPassword(), PASSWORD_DEFAULT),
        );
        $events = $character->releaseEvents();
        $this->characterRepository->save($character);
        $this->domainEventDispatcher->dispatch(...$events);

        return CreateCharacterCommandResponse::succeeded($character->getId());
    }
}
