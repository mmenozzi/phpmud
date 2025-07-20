<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

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

    public function handleCommand(string $command): void
    {
        if ($command === 'north') {
            $this->character->moveTo(Direction::North);
            $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
            return;
        }
        if ($command === 'east') {
            $this->character->moveTo(Direction::East);
            $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
            return;
        }
        if ($command === 'south') {
            $this->character->moveTo(Direction::South);
            $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
            return;
        }
        if ($command === 'west') {
            $this->character->moveTo(Direction::West);
            $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
            return;
        }
        if ($command === 'up') {
            $this->character->moveTo(Direction::Up);
            $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
            return;
        }
        if ($command === 'down') {
            $this->character->moveTo(Direction::Down);
            $this->socket->write($this->character->getLocation()->getName() . PHP_EOL . PHP_EOL);
            return;
        }

        $this->socket->write('What???' . PHP_EOL . PHP_EOL);
    }
}
