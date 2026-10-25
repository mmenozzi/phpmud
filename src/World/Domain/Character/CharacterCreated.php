<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Character;

use PHPMud\World\Domain\DomainEvent;
use PHPMud\World\Domain\Location\LocationId;

final readonly class CharacterCreated implements DomainEvent
{
    public function __construct(
        private CharacterId $characterId,
        private LocationId $locationId,
    ) {
    }

    public function getCharacterId(): CharacterId
    {
        return $this->characterId;
    }

    public function getLocationId(): LocationId
    {
        return $this->locationId;
    }
}
