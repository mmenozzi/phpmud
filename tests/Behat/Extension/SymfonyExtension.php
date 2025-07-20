<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Extension;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use PHPMud\Infrastructure\Kernel;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class SymfonyExtension implements Extension
{
    /**
     * Kernel used inside Behat contexts or to create services injected to them.
     * Container is rebuilt before every scenario.
     */
    public const KERNEL_ID = 'phpmud_symfony.kernel';

    /**
     * Kernel used by Symfony driver to isolate app container from contexts' container.
     * Container is rebuilt before every request.
     */
    public const DRIVER_KERNEL_ID = 'phpmud_symfony.driver_kernel';

    public function process(ContainerBuilder $container)
    {
        $this->processEnvironmentHandler($container);
    }

    public function getConfigKey()
    {
        return 'phpmud_symfony';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->setupTestEnvironment('test');

        $this->loadKernel($container);
        $this->loadDriverKernel($container);

        $this->loadKernelRebooter($container);

        $this->loadEnvironmentHandler($container);
    }

    private function setupTestEnvironment(string $fallback): void
    {
        // If there's no defined server / environment variable with an environment, default to configured fallback
        if (($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) === null) {
            putenv('APP_ENV='.$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $fallback);
        }
    }

    private function loadKernel(ContainerBuilder $container): void
    {
        $definition = new Definition(Kernel::class, [$_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'test']);
        $definition->addMethodCall('boot');
        $definition->setPublic(true);
        $container->setDefinition(self::KERNEL_ID, $definition);
    }

    private function loadDriverKernel(ContainerBuilder $container): void
    {
        $container->setDefinition(self::DRIVER_KERNEL_ID, $container->findDefinition(self::KERNEL_ID));
    }

    private function loadKernelRebooter(ContainerBuilder $container): void
    {
        $definition = new Definition(KernelOrchestrator::class, [new Reference(self::KERNEL_ID), $container]);
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);

        $container->setDefinition('phpmud_symfony.kernel_orchestrator', $definition);
    }

    private function loadEnvironmentHandler(ContainerBuilder $container): void
    {
        $definition = new Definition(ContextServiceEnvironmentHandler::class, [
            new Reference(self::KERNEL_ID),
            new Reference('environment.handler.context'),
        ]);
        $definition->addTag(EnvironmentExtension::HANDLER_TAG, ['priority' => 128]);

        $container->setDefinition('phpmud_symfony.environment_handler.context_service', $definition);
    }

    private function processEnvironmentHandler(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition('phpmud_symfony.environment_handler.context_service');
        foreach ($container->findTaggedServiceIds(ContextExtension::INITIALIZER_TAG) as $serviceId => $tags) {
            $definition->addMethodCall('registerContextInitializer', [new Reference($serviceId)]);
        }
    }
}
