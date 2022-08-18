<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use PHPMud\Domain\Entity\Location;
use PHPMud\Domain\Repository\LocationRepositoryInterface;
use Webmozart\Assert\Assert;

final class LocationContext implements Context
{
    public function __construct(private readonly LocationRepositoryInterface $locationRepository)
    {
    }

    /**
     * @Transform /^location "([^"]*)"$/
     */
    public function transformLocationFromName(string $locationName): Location
    {
        $locations = $this->locationRepository->findByName($locationName);
        Assert::count($locations, 1);

        return $locations->first();
    }
}
