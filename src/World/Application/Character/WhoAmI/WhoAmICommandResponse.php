<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Character\WhoAmI;

use PHPMud\World\Application\CommandResponseInterface;

final readonly class WhoAmICommandResponse implements CommandResponseInterface
{
    public function __construct(private string $characterName)
    {
    }

    public function getCharacterName(): string
    {
        return $this->characterName;
    }
}
