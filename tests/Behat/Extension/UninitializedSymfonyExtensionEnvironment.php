<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Extension;

use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Testwork\Environment\StaticEnvironment;
use Behat\Testwork\Suite\Suite;

final class UninitializedSymfonyExtensionEnvironment extends StaticEnvironment implements SymfonyExtensionEnvironment
{
    public function __construct(Suite $suite, private array $contexts, private ContextEnvironment $delegatedEnvironment)
    {
        parent::__construct($suite);
    }

    public function getServices(): array
    {
        return array_keys($this->contexts);
    }

    public function hasContexts(): bool
    {
        return count($this->contexts) > 0 || $this->delegatedEnvironment->hasContexts();
    }

    public function getContextClasses(): array
    {
        return array_merge(array_values($this->contexts), $this->delegatedEnvironment->getContextClasses());
    }

    public function hasContextClass($class): bool
    {
        return in_array($class, $this->contexts, true) || $this->delegatedEnvironment->hasContextClass($class);
    }

    public function getDelegatedEnvironment(): ContextEnvironment
    {
        return $this->delegatedEnvironment;
    }
}
