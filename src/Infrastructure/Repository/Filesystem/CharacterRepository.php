<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Repository\Filesystem;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPMud\Domain\Entity\Character;
use PHPMud\Domain\Repository\CharacterRepositoryInterface;
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

    public function add(Character $character): void
    {
        $characters = $this->load();
        $characters->set($character->getId()->toRfc4122(), $character);
        $this->flush($characters);
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

    public function remove(Character $character): void
    {
        $characters = $this->load();
        $characters->remove($character->getId()->toRfc4122());
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
