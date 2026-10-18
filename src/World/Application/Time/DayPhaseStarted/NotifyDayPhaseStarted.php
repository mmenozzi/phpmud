<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Time\DayPhaseStarted;

use PHPMud\World\Application\DomainEventSubscriberInterface;
use PHPMud\World\Application\NotifierInterface;
use PHPMud\World\Domain\DomainEvent;
use PHPMud\World\Domain\Time\DayPhaseStarted;
use Webmozart\Assert\Assert;

final readonly class NotifyDayPhaseStarted implements DomainEventSubscriberInterface
{
    public function __construct(private NotifierInterface $notifier)
    {
    }

    public function handle(DomainEvent $event): void
    {
        Assert::isInstanceOf($event, DayPhaseStarted::class);
        $this->notifier->notifyEveryone(new DayPhaseStartedNotification($event->getDayPhase()));
    }
}
