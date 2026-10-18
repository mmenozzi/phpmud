<?php

declare(strict_types=1);

namespace PHPMud\World\Domain;

trait RecordsDomainEvents
{
    /**
     * @var list<DomainEvent>
     */
    private array $recordedEvents = [];

    /**
     * @return list<DomainEvent>
     */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    private function recordThat(DomainEvent $event): void
    {
        $this->recordedEvents[] = $event;
    }
}
