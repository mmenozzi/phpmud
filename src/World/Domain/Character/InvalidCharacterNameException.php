<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Character;

final class InvalidCharacterNameException extends \DomainException
{
    public function __construct(public readonly string $name)
    {
        parent::__construct(sprintf('"%s" is not a valid character name: only 3 to 15 letters are allowed.', $name));
    }
}
