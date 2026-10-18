<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\Help\Unknown\UnknownCommand;
use PHPMud\World\Application\Movement\Look\LookCommand;
use PHPMud\World\Application\Movement\Move\MoveCommand;
use PHPMud\World\Domain\Location\Direction;
use Webmozart\Assert\Assert;

final class CommandResolver
{
    public function resolve(TelnetClient $client, string $commandString): CommandInterface
    {
        $tokens = explode(' ', strtolower(trim($commandString)));
        if ($command = $this->resolveMoveCommand($tokens, $client)) {
            return $command;
        }
        if ($command = $this->resolveLookCommand($tokens, $client)) {
            return $command;
        }

        return new UnknownCommand();
    }

    /**
     * @param array<array-key, string> $tokens
     */
    private function resolveMoveCommand(array $tokens, TelnetClient $client): ?MoveCommand
    {
        Assert::notNull($client->getCharacter());

        $moveVerbs = ['move', 'go', 'walk'];

        if (1 === count($tokens)) {
            $direction = $this->resolveDirection($tokens[0]);
            if (null !== $direction) {
                return new MoveCommand($client->getCharacter(), $direction);
            }
        }
        if (2 === count($tokens) && in_array($tokens[0], $moveVerbs, true)) {
            $direction = $this->resolveDirection($tokens[1]);
            if (null !== $direction) {
                return new MoveCommand($client->getCharacter(), $direction);
            }
        }

        return null;
    }

    private function resolveDirection(string $input): ?Direction
    {
        $directions = [
            Direction::North->value => ['n'],
            Direction::South->value => ['s'],
            Direction::East->value => ['e'],
            Direction::West->value => ['w'],
            Direction::Up->value => ['u'],
            Direction::Down->value => ['d'],
        ];

        $resolvedDirection = '';
        if (array_key_exists($input, $directions)) {
            $resolvedDirection = $input;
        }

        foreach ($directions as $direction => $aliases) {
            if (in_array($input, $aliases)) {
                $resolvedDirection = $direction;
            }
        }

        return Direction::tryFrom($resolvedDirection);
    }

    /**
     * @param array<array-key, string> $tokens
     */
    private function resolveLookCommand(array $tokens, TelnetClient $client): ?LookCommand
    {
        Assert::notNull($client->getCharacter());
        if (1 === count($tokens) && 'look' === $tokens[0]) {
            return new LookCommand($client->getCharacter());
        }

        return null;
    }
}
