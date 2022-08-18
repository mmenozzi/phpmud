<?php

declare(strict_types=1);

namespace PHPMud\Domain\Entity;

use Symfony\Component\Uid\Uuid;

trait IdTrait
{
    private readonly Uuid $id;

    private function createId(): void
    {
        $this->id = Uuid::v6();
    }
}
