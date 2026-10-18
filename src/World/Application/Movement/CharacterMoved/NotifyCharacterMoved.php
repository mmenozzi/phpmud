<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement\CharacterMoved;

use PHPMud\World\Application\DomainEventSubscriberInterface;
use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Application\NotifierInterface;
use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Domain\Character\CharacterMoved;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use PHPMud\World\Domain\DomainEvent;
use PHPMud\World\Domain\Location\LocationId;
use Webmozart\Assert\Assert;

final readonly class NotifyCharacterMoved implements DomainEventSubscriberInterface
{
    public function __construct(
        private CharacterRepositoryInterface $characterRepository,
        private NotifierInterface $notifier,
    ) {
    }

    public function handle(DomainEvent $event): void
    {
        Assert::isInstanceOf($event, CharacterMoved::class);
        $characterName = $this->characterRepository->get($event->getCharacterId())->getName();
        $this->notifyCharactersIn(
            $event->getFrom(),
            $event->getCharacterId(),
            new CharacterLeftNotification($characterName, $event->getVia()),
        );
        $this->notifyCharactersIn(
            $event->getTo(),
            $event->getCharacterId(),
            new CharacterArrivedNotification($characterName, $event->getVia()),
        );
    }

    private function notifyCharactersIn(LocationId $locationId, CharacterId $mover, NotificationInterface $notification): void
    {
        foreach ($this->characterRepository->findByLocation($locationId) as $character) {
            if ($character->getId()->equals($mover)) {
                continue;
            }
            $this->notifier->notify($character->getId(), $notification);
        }
    }
}
