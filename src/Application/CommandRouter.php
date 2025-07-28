<?php

declare(strict_types=1);

namespace PHPMud\Application;

final class CommandRouter
{
    /**
     * @var array<class-string<CommandInterface>, ExecutorInterface>
     */
    private array $commandClassToExecutor = [];

    public function resolve(CommandInterface $command): ExecutorInterface
    {
        if (!isset($this->commandClassToExecutor[get_class($command)])) {
            throw new \InvalidArgumentException(sprintf('No executor found for command "%s". Available executors are: "%s"', get_class($command), json_encode($this->commandClassToExecutor)));
        }

        return $this->commandClassToExecutor[get_class($command)];
    }

    /**
     * @param class-string<CommandInterface> $commandClass
     */
    public function addExecutorForCommand(string $commandClass, ExecutorInterface $executor): void
    {
        $this->commandClassToExecutor[$commandClass] = $executor;
    }
}
