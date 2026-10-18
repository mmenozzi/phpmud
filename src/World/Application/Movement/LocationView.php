<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement;

final readonly class LocationView
{
    /**
     * @param array<string, array{id: string, name: string}> $exits neighbour locations keyed by Direction value
     */
    public function __construct(
        private string $id,
        private string $name,
        private string $description,
        private array $exits,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
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
     * @return array<string, array{id: string, name: string}>
     */
    public function getExits(): array
    {
        return $this->exits;
    }
}
