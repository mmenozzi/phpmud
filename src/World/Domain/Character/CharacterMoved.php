<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Character;

use PHPMud\World\Domain\DomainEvent;
use PHPMud\World\Domain\Location\Direction;
use PHPMud\World\Domain\Location\LocationId;

final readonly class CharacterMoved implements DomainEvent
{
    public function __construct(
        private CharacterId $characterId,
        private LocationId $from,
        private LocationId $to,
        private ?Direction $via,
    ) {
    }

    public function getCharacterId(): CharacterId
    {
        return $this->characterId;
    }

    public function getFrom(): LocationId
    {
        return $this->from;
    }

    public function getTo(): LocationId
    {
        return $this->to;
    }

    public function getVia(): ?Direction
    {
        return $this->via;
    }
}
