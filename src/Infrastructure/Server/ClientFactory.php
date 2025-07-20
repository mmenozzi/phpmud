<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use Amp\Socket\ResourceSocket;
use PHPMud\Domain\Repository\CharacterRepositoryInterface;
use Webmozart\Assert\Assert;

final class ClientFactory
{
    public function __construct(private readonly CharacterRepositoryInterface $characterRepository)
    {
    }

    public function create(ResourceSocket $socket): Client
    {
        $character = $this->characterRepository->findAll()->first();
        Assert::notFalse($character);

        return new Client($socket, $character);
    }
}
