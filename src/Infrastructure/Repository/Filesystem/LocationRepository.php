<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Repository\Filesystem;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPMud\Domain\Entity\Location;
use PHPMud\Domain\Repository\LocationRepositoryInterface;
use Webmozart\Assert\Assert;

final class LocationRepository implements LocationRepositoryInterface
{
    private ?Collection $locations = null;

    public function __construct(private readonly string $filepath)
    {
        if (!file_exists($this->filepath)) {
            touch($this->filepath);
            $this->flush(new ArrayCollection([]));
        }
    }

    public function add(Location $location): void
    {
        $locations = $this->load();
        $locations->set($location->getId()->toRfc4122(), $location);
        $this->flush($locations);
    }

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
        $locations->remove($location->getId()->toRfc4122());
        $this->flush($locations);
    }

    private function load(): Collection
    {
        if ($this->locations === null) {
            $this->locations = unserialize(file_get_contents($this->filepath));
            Assert::isInstanceOf($this->locations, Collection::class);
        }

        return $this->locations;
    }

    private function flush(Collection $locations): void
    {
        file_put_contents($this->filepath, serialize($locations));
    }
}
