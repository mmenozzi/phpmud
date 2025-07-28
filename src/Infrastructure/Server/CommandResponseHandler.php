<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use PHPMud\Application\CommandResponseInterface;
use PHPMud\Application\Help\Command\UnknownCommandResponse;
use PHPMud\Application\Movement\Command\MoveCommandResponse;
use PHPMud\Domain\Direction;

final class CommandResponseHandler
{
    public function handle(Client $client, CommandResponseInterface $commandResponse): void
    {
        if ($commandResponse instanceof MoveCommandResponse) {
            $this->handleMoveCommandResponse($client, $commandResponse);

            return;
        }

        if ($commandResponse instanceof UnknownCommandResponse) {
            $this->handleUnknownCommandResponse($client);

            return;
        }

        throw new \InvalidArgumentException(sprintf('Command response of type "%s" is not supported.', get_class($commandResponse)));
    }

    private function closeResponse(Client $client): void
    {
        $client->getSocket()->write(PHP_EOL);
    }

    private function handleMoveCommandResponse(Client $client, MoveCommandResponse $commandResponse): void
    {
        if (!$commandResponse->isSuccessful()) {
            $client->getSocket()->write('Non puoi muoverti in quella direzione!'.PHP_EOL);
            $this->closeResponse($client);

            return;
        }

        $client->getSocket()->write('Ti muovi verso '.$commandResponse->getDirection()->name.PHP_EOL);
        $client->getSocket()->write($commandResponse->getLocation()->getName().PHP_EOL);
        $client->getSocket()->write($commandResponse->getLocation()->getDescription().PHP_EOL);
        $client->getSocket()->write('You can go:'.PHP_EOL);
        foreach (Direction::cases() as $direction) {
            $neighbor = $commandResponse->getLocation()->getNeighbor($direction);
            if (null === $neighbor) {
                continue;
            }
            $client->getSocket()->write(
                sprintf('%s: %s', ucwords($direction->value), $neighbor->getName()).PHP_EOL
            );
        }
        $this->closeResponse($client);
    }

    private function handleUnknownCommandResponse(Client $client): void
    {
        $client->getSocket()->write('Cosa???'.PHP_EOL);
        $this->closeResponse($client);
    }
}
