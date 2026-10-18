<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use Amp\Socket\ResourceSocket;
use PHPMud\World\Application\Character\WhoAmI\WhoAmICommandResponse;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\Help\Unknown\UnknownCommandResponse;
use PHPMud\World\Application\Movement\LocationView;
use PHPMud\World\Application\Movement\Look\LookCommandResponse;
use PHPMud\World\Application\Movement\Move\MoveCommandResponse;
use PHPMud\World\Application\Time\Tick\TickCommandResponse;
use PHPMud\World\Domain\Location\Direction;

final class CommandResponseHandler
{
    public function handle(TelnetClient $client, CommandResponseInterface $commandResponse): void
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

        if ($commandResponse instanceof TickCommandResponse) {
            $this->handleTickCommandResponse($client, $commandResponse);

            return;
        }

        throw new \InvalidArgumentException(sprintf('Command response of type "%s" is not supported.', get_class($commandResponse)));
    }

    private function closeResponse(TelnetClient $client): void
    {
        $client->getSocket()->write(PHP_EOL);
    }

    private function handleMoveCommandResponse(TelnetClient $client, MoveCommandResponse $commandResponse): void
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

    private function handleUnknownCommandResponse(TelnetClient $client): void
    {
        $client->getSocket()->write('Cosa???'.PHP_EOL);
        $this->closeResponse($client);
    }

    private function handleLookCommandResponse(TelnetClient $client, LookCommandResponse $commandResponse): void
    {
        $this->writeLocation($client->getSocket(), $commandResponse->getLocationView());
        $this->closeResponse($client);
    }

    private function handleWhoAmICommandResponse(TelnetClient $client, WhoAmICommandResponse $commandResponse): void
    {
        $client->getSocket()->write(sprintf('Il tuo nome è "%s".', $commandResponse->getCharacter()->getName()).PHP_EOL);
        $this->closeResponse($client);
    }

    private function handleTickCommandResponse(TelnetClient $client, TickCommandResponse $commandResponse): void
    {
        $startedDayPhase = $commandResponse->getStartedDayPhase();
        if (null === $startedDayPhase) {
            return;
        }

        $client->getSocket()->write(DayPhaseMessage::of($startedDayPhase).PHP_EOL);
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
                sprintf('%s: %s', DirectionLabel::of(Direction::from($direction)), $neighbor->getName()).PHP_EOL
            );
        }
    }
}
