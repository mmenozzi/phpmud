<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use Amp\Socket\ResourceSocket;
use PHPMud\Application\Character\Command\WhoAmICommandResponse;
use PHPMud\Application\CommandResponseInterface;
use PHPMud\Application\Help\Command\UnknownCommandResponse;
use PHPMud\Application\Movement\Command\LookCommandResponse;
use PHPMud\Application\Movement\Command\MoveCommandResponse;
use PHPMud\Application\Movement\View\LocationView;

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

        if ($commandResponse instanceof WhoAmICommandResponse) {
            $this->handleWhoAmICommandResponse($client, $commandResponse);

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

        $this->writeLocation($socket, $commandResponse->getLocationView());
        $this->closeResponse($client);
    }

    private function handleUnknownCommandResponse(Client $client): void
    {
        $client->getSocket()->write('Cosa???'.PHP_EOL);
        $this->closeResponse($client);
    }

    private function handleLookCommandResponse(Client $client, LookCommandResponse $commandResponse): void
    {
        $this->writeLocation($client->getSocket(), $commandResponse->getLocationView());
        $this->closeResponse($client);
    }

    private function handleWhoAmICommandResponse(Client $client, WhoAmICommandResponse $commandResponse): void
    {
        $client->getSocket()->write(sprintf('Il tuo nome è "%s".', $commandResponse->getCharacter()->getName()).PHP_EOL);
        $this->closeResponse($client);
    }

    private function writeLocation(ResourceSocket $socket, LocationView $locationView): void
    {
        $location = $locationView->getLocation();
        $socket->write($location->getName().PHP_EOL);
        $socket->write($location->getDescription().PHP_EOL);
        $socket->write('Puoi andare:'.PHP_EOL);
        foreach ($locationView->getExits() as $direction => $neighbor) {
            $socket->write(
                sprintf('%s: %s', ucwords($direction), $neighbor->getName()).PHP_EOL
            );
        }
    }
}
