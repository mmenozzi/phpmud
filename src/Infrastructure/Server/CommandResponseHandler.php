<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use Amp\Socket\ResourceSocket;
use PHPMud\Application\CommandResponseInterface;
use PHPMud\Application\Help\Command\UnknownCommandResponse;
use PHPMud\Application\Movement\Command\LookCommandResponse;
use PHPMud\Application\Movement\Command\MoveCommandResponse;
use PHPMud\Domain\Direction;
use PHPMud\Domain\Entity\Location;

final class CommandResponseHandler
{
    public function handle(Client $client, CommandResponseInterface $commandResponse): void
    {
        if ($commandResponse instanceof MoveCommandResponse) {
            $this->handleMoveCommandResponse($client, $commandResponse);

            return;
        }

        if ($commandResponse instanceof LookCommandResponse) {
            $this->handleLookCommandResponse($client, $commandResponse);

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
        $socket = $client->getSocket();
        if (!$commandResponse->isSuccessful()) {
            $socket->write('Non puoi muoverti in quella direzione!'.PHP_EOL);
            $this->closeResponse($client);

            return;
        }

        $this->writeLocation($socket, $commandResponse->getLocation());
        $this->closeResponse($client);
    }

    private function handleUnknownCommandResponse(Client $client): void
    {
        $client->getSocket()->write('Cosa???'.PHP_EOL);
        $this->closeResponse($client);
    }

    private function handleLookCommandResponse(Client $client, LookCommandResponse $commandResponse): void
    {
        $this->writeLocation($client->getSocket(), $commandResponse->getLocation());
        $this->closeResponse($client);
    }

    private function writeLocation(ResourceSocket $socket, Location $location): void
    {
        $socket->write($location->getName().PHP_EOL);
        $socket->write($location->getDescription().PHP_EOL);
        $socket->write('You can go:'.PHP_EOL);
        foreach (Direction::cases() as $direction) {
            $neighbor = $location->getNeighbor($direction);
            if (null === $neighbor) {
                continue;
            }
            $socket->write(
                sprintf('%s: %s', ucwords($direction->value), $neighbor->getName()).PHP_EOL
            );
        }
    }
}
