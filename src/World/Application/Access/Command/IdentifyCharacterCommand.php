<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\Command;

use PHPMud\World\Application\CommandInterface;

final readonly class IdentifyCharacterCommand implements CommandInterface
{
    public function __construct(private string $characterName)
    {
    }

    public function getCharacterName(): string
    {
        return $this->characterName;
    }
}
