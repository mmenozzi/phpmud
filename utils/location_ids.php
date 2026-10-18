<?php

declare(strict_types=1);

use PHPMud\World\Domain\Location\LocationId;
use Symfony\Component\Uid\Uuid;

const LOCATION_ID_NAMESPACE = '63728cd9-a09d-4e07-8e8d-e5271cecb7ce';

function locationIdFromCsvId(int $csvId): LocationId
{
    return LocationId::fromString(Uuid::v5(Uuid::fromString(LOCATION_ID_NAMESPACE), (string) $csvId)->toRfc4122());
}
