<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Character;

use PHPMud\World\Domain\Location\LocationId;

final class Character
{
    private readonly CharacterId $id;

    public function __construct(
        private readonly string $name,
        private LocationId $locationId,
        private string $passwordHash,
    ) {
        $this->id = CharacterId::generate();
    }

    public function getId(): CharacterId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
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

    public function relocateTo(LocationId $locationId): void
    {
        $this->locationId = $locationId;
    }
}
