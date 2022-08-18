<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Extension;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use PHPMud\Infrastructure\Kernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class KernelOrchestrator implements EventSubscriberInterface
{
    public function __construct(
        private readonly Kernel $symfonyKernel,
        private readonly ContainerInterface $behatContainer
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScenarioTested::BEFORE => ['setUp', 15],
            ExampleTested::BEFORE => ['setUp', 15],
            ScenarioTested::AFTER => ['tearDown', -15],
            ExampleTested::AFTER => ['tearDown', -15],
        ];
    }

    public function setUp(): void
    {
        /** @psalm-suppress InvalidArgument Psalm complains that ContainerInterface does not match object|null */
        $this->symfonyKernel->getContainer()->set('behat.service_container', $this->behatContainer);
    }

    public function tearDown(): void
    {
        $this->symfonyKernel->getContainer()->set('behat.service_container', null);
        $this->symfonyKernel->shutdown();
        $this->symfonyKernel->boot();
    }
}
