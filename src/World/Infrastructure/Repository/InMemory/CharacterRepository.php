<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Repository\InMemory;

use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
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

    public function findOneByName(string $name): ?Character
    {
        return $this->findOneBy(['name' => $name]);
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
