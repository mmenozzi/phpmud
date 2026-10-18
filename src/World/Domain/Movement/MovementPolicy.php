<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Movement;

use PHPMud\World\Domain\Character\Character;
use PHPMud\World\Domain\Location\Direction;
use PHPMud\World\Domain\Location\Location;
use PHPMud\World\Domain\Location\LocationId;
use Webmozart\Assert\Assert;

final readonly class MovementPolicy
{
    /**
     * @throws NoExitInDirectionException
     */
    public function move(Character $character, Location $from, Direction $direction): LocationId
    {
        Assert::true(
            $from->getId()->equals($character->getLocationId()),
            'The character is not in the given starting location.',
        );

        $exit = $from->getExit($direction);
        if (null === $exit) {
            throw new NoExitInDirectionException($direction);
        }
        $character->relocateTo($exit, $direction);

        return $exit;
    }
}
