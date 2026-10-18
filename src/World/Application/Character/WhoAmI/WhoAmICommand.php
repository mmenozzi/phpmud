<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Character\WhoAmI;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Domain\Character\CharacterId;

final readonly class WhoAmICommand implements CommandInterface
{
    public function __construct(private CharacterId $characterId)
    {
    }

    public function getCharacterId(): CharacterId
    {
        return $this->characterId;
    }
}
