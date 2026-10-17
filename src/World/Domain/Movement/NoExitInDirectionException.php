<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Movement;

use PHPMud\World\Domain\Location\Direction;

final class NoExitInDirectionException extends \DomainException
{
    public function __construct(public readonly Direction $direction)
    {
        parent::__construct(sprintf('There is no exit towards "%s".', $direction->value));
    }
}
