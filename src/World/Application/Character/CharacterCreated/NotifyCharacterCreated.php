<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Character\CharacterCreated;

use PHPMud\World\Application\DomainEventSubscriberInterface;
use PHPMud\World\Application\NotifierInterface;
use PHPMud\World\Domain\Character\CharacterCreated;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use PHPMud\World\Domain\DomainEvent;
use Webmozart\Assert\Assert;

final readonly class NotifyCharacterCreated implements DomainEventSubscriberInterface
{
    public function __construct(
        private CharacterRepositoryInterface $characterRepository,
        private NotifierInterface $notifier,
    ) {
    }

    public function handle(DomainEvent $event): void
    {
        Assert::isInstanceOf($event, CharacterCreated::class);
        $newcomer = $this->characterRepository->get($event->getCharacterId());
        $notification = new CharacterAppearedNotification($newcomer->getName(), $newcomer->getSex());
        foreach ($this->characterRepository->findByLocation($event->getLocationId()) as $character) {
            if ($character->getId()->equals($newcomer->getId())) {
                continue;
            }
            $this->notifier->notify($character->getId(), $notification);
        }
    }
}
