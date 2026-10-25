<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Character;

final readonly class CharacterName
{
    private const string PATTERN = '/^\p{L}{3,15}$/u';

    private function __construct(private string $name)
    {
    }

    public static function fromString(string $name): self
    {
        if (1 !== preg_match(self::PATTERN, $name)) {
            throw new InvalidCharacterNameException($name);
        }

        return new self(mb_ucfirst($name));
    }

    public function equals(self $other): bool
    {
        return mb_strtolower($this->name) === mb_strtolower($other->name);
    }

    public function toString(): string
    {
        return $this->name;
    }
}
