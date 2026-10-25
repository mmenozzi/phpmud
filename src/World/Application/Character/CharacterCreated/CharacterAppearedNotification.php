<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Character\CharacterCreated;

use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Domain\Character\CharacterName;
use PHPMud\World\Domain\Character\Sex;

final readonly class CharacterAppearedNotification implements NotificationInterface
{
    public function __construct(private CharacterName $characterName, private Sex $sex)
    {
    }

    public function getCharacterName(): CharacterName
    {
        return $this->characterName;
    }

    public function getSex(): Sex
    {
        return $this->sex;
    }
}
