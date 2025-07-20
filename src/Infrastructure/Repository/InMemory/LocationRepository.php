<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Repository\InMemory;

use Doctrine\Common\Collections\Collection;
use PHPMud\Domain\Entity\Location;
use PHPMud\Domain\Repository\LocationRepositoryInterface;
use Webgriffe\InMemoryRepository\ObjectRepository;

/**
 * @extends ObjectRepository<array-key, Location>
 */
final class LocationRepository extends ObjectRepository implements LocationRepositoryInterface
{
    public function add(Location $location): void
    {
        $this->objectCollection->add($location);
    }

    public function getClassName(): string
    {
        return Location::class;
    }

    public function findByName(string $name): Collection
    {
        return $this->findBy(['name' => $name]);
    }

    public function remove(Location $location): void
    {
        $this->objectCollection->removeElement($location);
    }

    protected function getIdProperty(): string
    {
        return 'id';
    }
}
