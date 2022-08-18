<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure;

use LogicException;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class Kernel
{
    private ?Container $container = null;

    public function __construct(private readonly string $environment = 'dev')
    {
    }

    public function boot(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new XmlFileLoader($this->container, new FileLocator(__DIR__ . '/Resources/config'));
        $loader->load('services.xml');
        try {
            $loader->load("services_{$this->environment}.xml");
        } catch (FileLocatorFileNotFoundException $e) {
            // Do not throw
        }
    }

    public function shutdown(): void
    {
        $this->container = null;
    }

    public function getContainer(): Container
    {
        if ($this->container === null) {
            throw new LogicException('Cannot retrieve the container from a non-booted kernel.');
        }

        return $this->container;
    }
}
