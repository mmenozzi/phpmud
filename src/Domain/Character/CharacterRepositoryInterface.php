<?php

declare(strict_types=1);

namespace PHPMud\Domain\Character;

use Doctrine\Common\Collections\Collection;

interface CharacterRepositoryInterface
{
    public function save(Character $character): void;

    public function findOneByName(string $name): ?Character;

    /**
     * @return Collection<array-key, Character>
     */
    public function findAll(): Collection;

    public function remove(Character $character): void;
}
