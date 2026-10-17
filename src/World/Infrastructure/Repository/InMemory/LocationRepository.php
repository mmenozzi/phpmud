<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Repository\InMemory;

use Doctrine\Common\Collections\Collection;
use PHPMud\World\Domain\Location\Location;
use PHPMud\World\Domain\Location\LocationId;
use PHPMud\World\Domain\Location\LocationRepositoryInterface;
use Webgriffe\InMemoryRepository\ObjectRepository;

/**
 * @extends ObjectRepository<array-key, Location>
 */
final class LocationRepository extends ObjectRepository implements LocationRepositoryInterface
{
    public function save(Location $location): void
    {
        if ($this->objectCollection->contains($location)) {
            return;
        }
        $this->objectCollection->add($location);
    }

    public function get(LocationId $id): Location
    {
        $location = $this->objectCollection->findFirst(
            static fn (int|string $key, Location $location): bool => $location->getId()->equals($id),
        );
        if (null === $location) {
            throw new \OutOfBoundsException(sprintf('Location "%s" not found.', $id->toString()));
        }

        return $location;
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
