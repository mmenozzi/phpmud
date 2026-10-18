<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Repository\InMemory;

use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use PHPMud\World\Domain\Location\LocationId;
use Webgriffe\InMemoryRepository\ObjectRepository;

/**
 * @extends ObjectRepository<array-key, Character>
 */
final class CharacterRepository extends ObjectRepository implements CharacterRepositoryInterface
{
    public function getClassName(): string
    {
        return Character::class;
    }

    public function save(Character $character): void
    {
        if ($this->objectCollection->contains($character)) {
            return;
        }
        $this->objectCollection->add($character);
    }

    public function get(CharacterId $id): Character
    {
        $character = $this->objectCollection->findFirst(
            static fn (int|string $key, Character $character): bool => $character->getId()->equals($id),
        );
        if (null === $character) {
            throw new \OutOfBoundsException(sprintf('Character "%s" not found.', $id->toString()));
        }

        return $character;
    }

    public function findOneByName(string $name): ?Character
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findByLocation(LocationId $locationId): array
    {
        return array_values($this->objectCollection->filter(
            static fn (Character $character): bool => $character->getLocationId()->equals($locationId),
        )->toArray());
    }

    public function remove(Character $character): void
    {
        $this->objectCollection->removeElement($character);
    }

    protected function getIdProperty(): string
    {
        return 'id';
    }
}
