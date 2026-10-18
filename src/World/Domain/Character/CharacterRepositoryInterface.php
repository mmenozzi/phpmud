<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Character;

use Doctrine\Common\Collections\Collection;
use PHPMud\World\Domain\Location\LocationId;

interface CharacterRepositoryInterface
{
    public function save(Character $character): void;

    public function get(CharacterId $id): Character;

    public function findOneByName(string $name): ?Character;

    /**
     * @return Collection<array-key, Character>
     */
    public function findAll(): Collection;

    /**
     * @return list<Character>
     */
    public function findByLocation(LocationId $locationId): array;

    public function remove(Character $character): void;
}
