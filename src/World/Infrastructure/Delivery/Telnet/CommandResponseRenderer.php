<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use PHPMud\World\Application\Character\WhoAmI\WhoAmICommandResponse;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\Help\Unknown\UnknownCommandResponse;
use PHPMud\World\Application\Movement\LocationView;
use PHPMud\World\Application\Movement\Look\LookCommandResponse;
use PHPMud\World\Application\Movement\Move\MoveCommandResponse;
use PHPMud\World\Application\Time\Tick\TickCommandResponse;
use PHPMud\World\Domain\Location\Direction;

final class CommandResponseRenderer
{
    public function render(CommandResponseInterface $commandResponse): ?string
    {
        $lines = $this->renderLines($commandResponse);
        if (null === $lines) {
            return null;
        }

        return implode('', array_map(static fn (string $line): string => $line.PHP_EOL, $lines)).PHP_EOL;
    }

    /**
     * @return list<string>|null
     */
    private function renderLines(CommandResponseInterface $commandResponse): ?array
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
            return [sprintf('Il tuo nome è "%s".', $commandResponse->getCharacter()->getName())];
        }

        if ($commandResponse instanceof TickCommandResponse) {
            $startedDayPhase = $commandResponse->getStartedDayPhase();
            if (null === $startedDayPhase) {
                return null;
            }

            return [DayPhaseMessage::of($startedDayPhase)];
        }

        throw new \InvalidArgumentException(sprintf('Command response of type "%s" is not supported.', get_class($commandResponse)));
    }

    /**
     * @return list<string>
     */
    private function renderLocation(LocationView $locationView): array
    {
        $location = $locationView->getLocation();
        $lines = [$location->getName(), $location->getDescription(), 'Puoi andare:'];
        foreach ($locationView->getExits() as $direction => $neighbor) {
            $lines[] = sprintf('%s: %s', DirectionLabel::of(Direction::from($direction)), $neighbor->getName());
        }

        return $lines;
    }
}
