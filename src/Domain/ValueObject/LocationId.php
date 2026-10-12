<?php

declare(strict_types=1);

namespace PHPMud\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

final readonly class LocationId
{
    private function __construct(private Uuid $uuid)
    {
    }

    public static function generate(): self
    {
        return new self(Uuid::v6());
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->uuid);
    }

    public function toString(): string
    {
        return $this->uuid->toRfc4122();
    }
}
