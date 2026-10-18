<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\WebSocket;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\Help\Unknown\UnknownCommand;
use PHPMud\World\Application\Movement\Look\LookCommand;
use PHPMud\World\Application\Movement\Move\MoveCommand;
use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Domain\Location\Direction;

final class MessageParser
{
    /**
     * @param array<array-key, mixed> $message
     */
    public function parse(array $message, CharacterId $characterId): CommandInterface
    {
        return match ($message['type'] ?? null) {
            'look' => new LookCommand($characterId),
            'move' => $this->parseMove($message, $characterId),
            default => new UnknownCommand(),
        };
    }

    /**
     * @param array<array-key, mixed> $message
     */
    private function parseMove(array $message, CharacterId $characterId): CommandInterface
    {
        $direction = is_string($message['direction'] ?? null) ? Direction::tryFrom($message['direction']) : null;
        if (null === $direction) {
            return new UnknownCommand();
        }

        return new MoveCommand($characterId, $direction);
    }
}
