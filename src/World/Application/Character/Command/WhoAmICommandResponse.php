<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Character\Command;

use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Domain\Character\Character;

final readonly class WhoAmICommandResponse implements CommandResponseInterface
{
    public function __construct(private Character $character)
    {
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }
}
