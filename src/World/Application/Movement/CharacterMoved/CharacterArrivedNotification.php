<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Movement\CharacterMoved;

use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Domain\Location\Direction;

final readonly class CharacterArrivedNotification implements NotificationInterface
{
    public function __construct(private string $characterName, private ?Direction $via)
    {
    }

    public function getCharacterName(): string
    {
        return $this->characterName;
    }

    public function getVia(): ?Direction
    {
        return $this->via;
    }
}
