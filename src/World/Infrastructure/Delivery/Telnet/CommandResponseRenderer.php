<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use PHPMud\World\Application\Character\WhoAmI\WhoAmICommandResponse;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\Help\Unknown\UnknownCommandResponse;
use PHPMud\World\Application\Movement\CharacterMoved\CharacterArrivedNotification;
use PHPMud\World\Application\Movement\CharacterMoved\CharacterLeftNotification;
use PHPMud\World\Application\Movement\LocationView;
use PHPMud\World\Application\Movement\Look\LookCommandResponse;
use PHPMud\World\Application\Movement\Move\MoveCommandResponse;
use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Application\Time\DayPhaseStarted\DayPhaseStartedNotification;
use PHPMud\World\Domain\Location\Direction;

final class CommandResponseRenderer
{
    public function render(CommandResponseInterface $commandResponse): string
    {
        return $this->toBlock($this->renderLines($commandResponse));
    }

    public function renderNotification(NotificationInterface $notification): string
    {
        if ($notification instanceof DayPhaseStartedNotification) {
            return $this->toBlock([DayPhaseMessage::of($notification->getDayPhase())]);
        }

        if ($notification instanceof CharacterLeftNotification) {
            return $this->toBlock([MovementMessage::left($notification->getCharacterName(), $notification->getVia())]);
        }

        if ($notification instanceof CharacterArrivedNotification) {
            return $this->toBlock([MovementMessage::arrived($notification->getCharacterName(), $notification->getVia())]);
        }

        throw new \InvalidArgumentException(sprintf('Notification of type "%s" is not supported.', get_class($notification)));
    }

    /**
     * @return list<string>
     */
    private function renderLines(CommandResponseInterface $commandResponse): array
    {
        if ($commandResponse instanceof MoveCommandResponse) {
            if (!$commandResponse->isSuccessful()) {
                return ['Non puoi muoverti in quella direzione!'];
            }

            return $this->renderLocation($commandResponse->getLocationView());
        }

        if ($commandResponse instanceof LookCommandResponse) {
            return $this->renderLocation($commandResponse->getLocationView());
        }

        if ($commandResponse instanceof UnknownCommandResponse) {
            return ['Cosa???'];
        }

        if ($commandResponse instanceof WhoAmICommandResponse) {
            return [sprintf('Il tuo nome è "%s".', $commandResponse->getCharacterName())];
        }

        throw new \InvalidArgumentException(sprintf('Command response of type "%s" is not supported.', get_class($commandResponse)));
    }

    /**
     * @return list<string>
     */
    private function renderLocation(LocationView $locationView): array
    {
        $lines = [$locationView->getName(), $locationView->getDescription(), 'Puoi andare:'];
        foreach ($locationView->getExits() as $direction => $neighborName) {
            $lines[] = sprintf('%s: %s', DirectionLabel::of(Direction::from($direction)), $neighborName);
        }

        return $lines;
    }

    /**
     * @param list<string> $lines
     */
    private function toBlock(array $lines): string
    {
        return implode('', array_map(static fn (string $line): string => $line.PHP_EOL, $lines)).PHP_EOL;
    }
}
