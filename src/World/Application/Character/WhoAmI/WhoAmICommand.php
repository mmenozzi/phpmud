<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Character\WhoAmI;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Domain\Character\Character;

final readonly class WhoAmICommand implements CommandInterface
{
    public function __construct(private Character $character)
    {
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }
}
