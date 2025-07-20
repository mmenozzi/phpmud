<?php

declare(strict_types=1);

namespace PHPMud\Domain\Repository;

use Doctrine\Common\Collections\Collection;
use PHPMud\Domain\Entity\Location;

interface LocationRepositoryInterface
{
    public function add(Location $location): void;

    /**
     * @return Collection<array-key, Location>
     */
    public function findByName(string $name): Collection;

    /**
     * @return Collection<array-key, Location>
     */
    public function findAll(): Collection;

    public function remove(Location $location): void;
}
