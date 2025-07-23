<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use Amp\Socket\ResourceSocket;
use PHPMud\Domain\Direction;
use PHPMud\Domain\Entity\Character;
use Webmozart\Assert\Assert;

final class Client
{
    private ?Character $character = null;

    private ?string $authenticatingCharacterName = null;

    public function __construct(
        private readonly ResourceSocket $socket,
    ) {
    }

    public function getSocket(): ResourceSocket
    {
        return $this->socket;
    }

    public function getCharacter(): ?Character
    {
        return $this->character;
    }

    public function handleCommand(string $command): void
    {
        Assert::notNull($this->character);

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
        } elseif ('whoami' === $command) {
            $this->showWhoAmI();
        } elseif ('look' === $command) {
            $this->showCurrentLocation();
        } else {
            $this->socket->write('What???'.PHP_EOL);
        }

        $this->socket->write(PHP_EOL);
    }

    private function showCurrentLocation(): void
    {
        Assert::notNull($this->character);
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

    public function isAuthenticated(): bool
    {
        return null !== $this->character;
    }

    public function isAuthenticating(): bool
    {
        return null !== $this->authenticatingCharacterName;
    }

    public function beginAuthenticationWithCharacterName(string $characterName): void
    {
        $this->authenticatingCharacterName = $characterName;
    }

    public function getAuthenticatingCharacterName(): string
    {
        if (null === $this->authenticatingCharacterName) {
            throw new \LogicException('No character name is being authenticated.');
        }

        return $this->authenticatingCharacterName;
    }

    public function stopAuthentication(): void
    {
        $this->authenticatingCharacterName = null;
    }

    public function authenticate(Character $character): void
    {
        $this->character = $character;
        $this->stopAuthentication();
    }

    private function showWhoAmI(): void
    {
        Assert::notNull($this->character);
        $this->socket->write(sprintf('Your name is "%s".', $this->character->getName()).PHP_EOL);
    }
}
