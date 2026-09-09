<?php

declare(strict_types=1);

namespace PHPMud\Application\Character\Command;

use PHPMud\Application\CommandResponseInterface;
use PHPMud\Domain\Entity\Character;

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
