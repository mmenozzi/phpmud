<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement\Command;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Domain\Character\Character;

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
