<?php

declare(strict_types=1);

namespace PHPMud\World\Application;

use PHPMud\World\Domain\DomainEvent;

interface DomainEventSubscriberInterface
{
    public function handle(DomainEvent $event): void;
}
