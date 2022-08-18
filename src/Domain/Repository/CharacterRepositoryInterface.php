<?php

declare(strict_types=1);

namespace PHPMud\Domain\Repository;

use Doctrine\Common\Collections\Collection;
use PHPMud\Domain\Entity\Character;

interface CharacterRepositoryInterface
{
    public function add(Character $character): void;

    /**
     * @return Collection<array-key, Character>
     */
    public function findByFirstNameAndLastName(string $firstName, string $lastName): Collection;
}
