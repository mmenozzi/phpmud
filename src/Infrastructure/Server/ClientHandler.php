<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use PHPMud\Domain\Repository\CharacterRepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class ClientHandler
{
    public function __construct(private CharacterRepositoryInterface $characterRepository)
    {
    }

    public function handleConnection(Client $client): void
    {
        Assert::null($client->getCharacter());
        $client->getSocket()->write('Please enter your character name...'.PHP_EOL);
        $client->getSocket()->write(PHP_EOL);
    }

    public function handleCommand(Client $client, string $command): void
    {
        if (!$client->isAuthenticated()) {
            if (!$client->isAuthenticating()) {
                $characterName = $command;
                $client->beginAuthenticationWithCharacterName($characterName);
                $client->getSocket()->write('Please enter your password...'.PHP_EOL);
                $client->getSocket()->write(PHP_EOL);

                return;
            }

            $authenticatingCharacterName = $client->getAuthenticatingCharacterName();
            $character = $this->characterRepository->findOneByName($authenticatingCharacterName);
            if (null === $character) {
                $client->getSocket()->write('Character name or password not valid!'.PHP_EOL);
                $client->stopAuthentication();
                $this->handleConnection($client);

                return;
            }
            $password = $command;
            $characterPasswordHash = $character->getPasswordHash();
            if (!password_verify($password, $characterPasswordHash)) {
                $client->getSocket()->write('Character name or password not valid!'.PHP_EOL);
                $client->stopAuthentication();
                $this->handleConnection($client);

                return;
            }

            $client->authenticate($character);
            $client->handleCommand('whoami');
            $client->handleCommand('look');

            return;
        }

        $client->handleCommand($command);
    }
}
