<?php

declare(strict_types=1);

namespace PHPMud\Domain\Exception;

use PHPMud\Domain\Direction;

final class NoExitInDirectionException extends \DomainException
{
    public function __construct(public readonly Direction $direction)
    {
        parent::__construct(sprintf('There is no exit towards "%s".', $direction->value));
    }
}
