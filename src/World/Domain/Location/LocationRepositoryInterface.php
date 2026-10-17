<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Location;

use Doctrine\Common\Collections\Collection;

interface LocationRepositoryInterface
{
    public function save(Location $location): void;

    public function get(LocationId $id): Location;

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
