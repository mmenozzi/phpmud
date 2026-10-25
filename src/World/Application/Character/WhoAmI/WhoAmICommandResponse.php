<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Character\WhoAmI;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Domain\Character\CharacterName;

final readonly class WhoAmICommandResponse implements CommandResponseInterface
{
    public function __construct(private CharacterName $characterName)
    {
    }

    public function getCharacterName(): CharacterName
    {
        return $this->characterName;
    }
}
