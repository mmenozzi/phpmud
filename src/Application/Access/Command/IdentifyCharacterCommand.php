<?php

declare(strict_types=1);

namespace PHPMud\Application\Access\Command;

use PHPMud\Application\CommandInterface;

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
