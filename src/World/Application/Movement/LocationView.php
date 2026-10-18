<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement;

final readonly class LocationView
{
    /**
     * @param array<string, string> $exits neighbour location names keyed by Direction value
     */
    public function __construct(private string $name, private string $description, private array $exits)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array<string, string>
     */
    public function getExits(): array
    {
        return $this->exits;
    }
}
