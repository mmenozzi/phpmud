<?php

declare(strict_types=1);

namespace PHPMud\Domain\Repository;

use Doctrine\Common\Collections\Collection;
use PHPMud\Domain\Entity\Character;

interface CharacterRepositoryInterface
{
    public function add(Character $character): void;

    public function findOneByName(string $name): ?Character;

    /**
     * @return Collection<array-key, Character>
     */
    public function findAll(): Collection;

    public function remove(Character $character): void;
}
