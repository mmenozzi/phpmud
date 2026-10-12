<?php

declare(strict_types=1);

namespace PHPMud\Domain\Service;

use PHPMud\Domain\Direction;
use PHPMud\Domain\Entity\Character;
use PHPMud\Domain\Entity\Location;
use PHPMud\Domain\Exception\NoExitInDirectionException;
use PHPMud\Domain\ValueObject\LocationId;
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
        $character->relocateTo($exit);

        return $exit;
    }
}
