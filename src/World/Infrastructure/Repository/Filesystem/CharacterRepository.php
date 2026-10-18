<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Repository\Filesystem;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use PHPMud\World\Domain\Location\LocationId;
use Webmozart\Assert\Assert;

final class CharacterRepository implements CharacterRepositoryInterface
{
    public function __construct(private readonly string $filepath)
    {
        if (!file_exists($this->filepath)) {
            touch($this->filepath);
            $this->flush(new ArrayCollection([]));
        }
    }

    public function save(Character $character): void
    {
        $characters = $this->load();
        $characters->set($character->getId()->toString(), $character);
        $this->flush($characters);
    }

    public function get(CharacterId $id): Character
    {
        $character = $this->load()->get($id->toString());
        if (null === $character) {
            throw new \OutOfBoundsException(sprintf('Character "%s" not found.', $id->toString()));
        }

        return $character;
    }

    public function findOneByName(string $name): ?Character
    {
        $result = $this->load()->filter(function (Character $character) use ($name) {
            return $character->getName() === $name;
        });
        if ($result->isEmpty()) {
            return null;
        }

        return $result->first() ?: null;
    }

    public function findAll(): Collection
    {
        return $this->load();
    }

    public function findByLocation(LocationId $locationId): array
    {
        return array_values($this->load()->filter(
            static fn (Character $character): bool => $character->getLocationId()->equals($locationId),
        )->toArray());
    }

    public function remove(Character $character): void
    {
        $characters = $this->load();
        $characters->remove($character->getId()->toString());
        $this->flush($characters);
    }

    /**
     * @return Collection<array-key, Character>
     */
    private function load(): Collection
    {
        $data = file_get_contents($this->filepath);
        Assert::string($data);
        $characters = unserialize($data);
        Assert::isInstanceOf($characters, Collection::class);

        return $characters;
    }

    /**
     * @param Collection<array-key, Character> $characters
     */
    private function flush(Collection $characters): void
    {
        file_put_contents($this->filepath, serialize($characters));
    }
}
