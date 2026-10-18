<?php

declare(strict_types=1);

namespace PHPMud\World\Application;

use PHPMud\World\Domain\DomainEvent;

final class DomainEventDispatcher
{
    /**
     * @var array<class-string<DomainEvent>, list<DomainEventSubscriberInterface>>
     */
    private array $eventClassToSubscribers = [];

    /**
     * @param class-string<DomainEvent> $eventClass
     */
    public function addSubscriber(string $eventClass, DomainEventSubscriberInterface $subscriber): void
    {
        $this->eventClassToSubscribers[$eventClass][] = $subscriber;
    }

    public function dispatch(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            foreach ($this->eventClassToSubscribers[$event::class] ?? [] as $subscriber) {
                $subscriber->handle($event);
            }
        }
    }
}
