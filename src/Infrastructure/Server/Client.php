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
        if ('north' === $command) {
            $this->character->moveTo(Direction::North);
            $this->socket->write($this->character->getLocation()->getName().PHP_EOL);
            $this->socket->write($this->character->getLocation()->getDescription().PHP_EOL);
            $this->socket->write(PHP_EOL);

            return;
        }
        if ('east' === $command) {
            $this->character->moveTo(Direction::East);
            $this->socket->write($this->character->getLocation()->getName().PHP_EOL);
            $this->socket->write($this->character->getLocation()->getDescription().PHP_EOL);
            $this->socket->write(PHP_EOL);

            return;
        }
        if ('south' === $command) {
            $this->character->moveTo(Direction::South);
            $this->socket->write($this->character->getLocation()->getName().PHP_EOL);
            $this->socket->write($this->character->getLocation()->getDescription().PHP_EOL);
            $this->socket->write(PHP_EOL);

            return;
        }
        if ('west' === $command) {
            $this->character->moveTo(Direction::West);
            $this->socket->write($this->character->getLocation()->getName().PHP_EOL);
            $this->socket->write($this->character->getLocation()->getDescription().PHP_EOL);
            $this->socket->write(PHP_EOL);

            return;
        }
        if ('up' === $command) {
            $this->character->moveTo(Direction::Up);
            $this->socket->write($this->character->getLocation()->getName().PHP_EOL);
            $this->socket->write($this->character->getLocation()->getDescription().PHP_EOL);
            $this->socket->write(PHP_EOL);

            return;
        }
        if ('down' === $command) {
            $this->character->moveTo(Direction::Down);
            $this->socket->write($this->character->getLocation()->getName().PHP_EOL);
            $this->socket->write($this->character->getLocation()->getDescription().PHP_EOL);
            $this->socket->write(PHP_EOL);

            return;
        }

        $this->socket->write('What???'.PHP_EOL.PHP_EOL);
    }
}
