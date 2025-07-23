<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Repository\InMemory;

use PHPMud\Domain\Entity\Character;
use PHPMud\Domain\Repository\CharacterRepositoryInterface;
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

    public function add(Character $character): void
    {
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
