<?php

declare(strict_types=1);

namespace PHPMud\Application\Movement\Command;

use PHPMud\Application\CommandInterface;
use PHPMud\Domain\Character\Character;

final readonly class LookCommand implements CommandInterface
{
    public function __construct(private Character $character)
    {
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }
}
