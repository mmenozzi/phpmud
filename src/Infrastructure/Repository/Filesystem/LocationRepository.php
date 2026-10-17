<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Repository\Filesystem;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPMud\Domain\Location\Location;
use PHPMud\Domain\Location\LocationId;
use PHPMud\Domain\Location\LocationRepositoryInterface;
use Webmozart\Assert\Assert;

final class LocationRepository implements LocationRepositoryInterface
{
    public function __construct(private readonly string $filepath)
    {
        if (!file_exists($this->filepath)) {
            touch($this->filepath);
            $this->flush(new ArrayCollection([]));
        }
    }

    public function save(Location $location): void
    {
        $locations = $this->load();
        $locations->set($location->getId()->toString(), $location);
        $this->flush($locations);
    }

    public function get(LocationId $id): Location
    {
        $location = $this->load()->get($id->toString());
        if (null === $location) {
            throw new \OutOfBoundsException(sprintf('Location "%s" not found.', $id->toString()));
        }

        return $location;
    }

    /**
     * @return Collection<array-key, Location>
     */
    public function findByName(string $name): Collection
    {
        return $this->load()->filter(function (Location $location) use ($name) {
            return $location->getName() === $name;
        });
    }

    public function findAll(): Collection
    {
        return $this->load();
    }

    public function remove(Location $location): void
    {
        $locations = $this->load();
        $locations->remove($location->getId()->toString());
        $this->flush($locations);
    }

    /**
     * @return Collection<array-key, Location>
     */
    private function load(): Collection
    {
        $data = file_get_contents($this->filepath);
        Assert::string($data);
        $locations = unserialize($data);
        Assert::isInstanceOf($locations, Collection::class);

        return $locations;
    }

    /**
     * @param Collection<array-key, Location> $locations
     */
    private function flush(Collection $locations): void
    {
        file_put_contents($this->filepath, serialize($locations));
    }
}
