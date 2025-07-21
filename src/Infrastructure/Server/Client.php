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
            $this->showCurrentLocation();
        } elseif ('east' === $command) {
            $this->character->moveTo(Direction::East);
            $this->showCurrentLocation();
        } elseif ('south' === $command) {
            $this->character->moveTo(Direction::South);
            $this->showCurrentLocation();
        } elseif ('west' === $command) {
            $this->character->moveTo(Direction::West);
            $this->showCurrentLocation();
        } elseif ('up' === $command) {
            $this->character->moveTo(Direction::Up);
            $this->showCurrentLocation();
        } elseif ('down' === $command) {
            $this->character->moveTo(Direction::Down);
            $this->showCurrentLocation();
        } else {
            $this->socket->write('What???'.PHP_EOL);
        }

        $this->socket->write(PHP_EOL);
    }

    private function showCurrentLocation(): void
    {
        $this->socket->write($this->character->getLocation()->getName().PHP_EOL);
        $this->socket->write($this->character->getLocation()->getDescription().PHP_EOL);
        $this->socket->write('You can go:'.PHP_EOL);
        foreach (Direction::cases() as $direction) {
            $neighbor = $this->character->getLocation()->getNeighbor($direction);
            if (null === $neighbor) {
                continue;
            }
            $this->socket->write(sprintf('%s: %s', ucwords($direction->value), $neighbor->getName()).PHP_EOL);
        }
    }
}
