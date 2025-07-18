<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use Amp\Promise;
use Amp\Socket\ResourceSocket;
use PHPMud\Domain\Direction;
use PHPMud\Domain\Entity\Character;

final class Client
{
    public function __construct(
        private readonly ResourceSocket $socket,
        private readonly Character $character,
    ) {
    }

    public function handleCommand(string $command): Promise
    {
        if ($command === 'north') {
            $this->character->moveTo(Direction::North);
            return $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
        }
        if ($command === 'east') {
            $this->character->moveTo(Direction::East);
            return $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
        }
        if ($command === 'south') {
            $this->character->moveTo(Direction::South);
            return $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
        }
        if ($command === 'west') {
            $this->character->moveTo(Direction::West);
            return $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
        }
        if ($command === 'up') {
            $this->character->moveTo(Direction::Up);
            return $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
        }
        if ($command === 'down') {
            $this->character->moveTo(Direction::Down);
            return $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
        }

        return $this->socket->write('What???' . PHP_EOL . PHP_EOL);
    }
}
