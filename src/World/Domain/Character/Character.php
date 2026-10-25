<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Character;

use PHPMud\World\Domain\Location\Direction;
use PHPMud\World\Domain\Location\LocationId;
use PHPMud\World\Domain\RecordsDomainEvents;

final class Character
{
    use RecordsDomainEvents;

    private readonly CharacterId $id;

    public function __construct(
        private readonly CharacterName $name,
        private readonly Sex $sex,
        private LocationId $locationId,
        private string $passwordHash,
    ) {
        $this->id = CharacterId::generate();
    }

    public static function create(CharacterName $name, Sex $sex, LocationId $locationId, string $passwordHash): self
    {
        $character = new self($name, $sex, $locationId, $passwordHash);
        $character->recordThat(new CharacterCreated($character->id, $locationId));

        return $character;
    }

    public function getId(): CharacterId
    {
        return $this->id;
    }

    public function getName(): CharacterName
    {
        return $this->name;
    }

    public function getSex(): Sex
    {
        return $this->sex;
    }

    public function getLocationId(): LocationId
    {
        return $this->locationId;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function isPasswordValid(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function relocateTo(LocationId $locationId, ?Direction $via = null): void
    {
        $from = $this->locationId;
        $this->locationId = $locationId;
        $this->recordThat(new CharacterMoved($this->id, $from, $locationId, $via));
    }
}
