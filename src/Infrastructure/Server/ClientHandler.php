<?php

declare(strict_types=1);

namespace PHPMud\Infrastructure\Server;

use PHPMud\Application\Access\Command\AuthenticateCharacterCommand;
use PHPMud\Application\Access\Command\AuthenticateCharacterCommandResponse;
use PHPMud\Application\Access\Command\IdentifyCharacterCommand;
use PHPMud\Application\Access\Command\IdentifyCharacterCommandResponse;
use PHPMud\Application\Character\Command\WhoAmICommand;
use PHPMud\Application\CommandInterface;
use PHPMud\Application\CommandResponseInterface;
use PHPMud\Application\CommandRouter;
use PHPMud\Application\Movement\Command\LookCommand;
use Webmozart\Assert\Assert;

final readonly class ClientHandler
{
    private const int MAX_PASSWORD_ATTEMPTS = 3;

    private const string BANNER_PATH = __DIR__.'/../Resources/banner.txt';

    public function __construct(
        private CommandResolver $commandResolver,
        private CommandRouter $commandRouter,
        private CommandResponseHandler $commandResponseHandler,
    ) {
    }

    public function handleConnection(Client $client): void
    {
        Assert::null($client->getCharacter());
        $banner = file_get_contents(self::BANNER_PATH);
        Assert::string($banner);
        $this->write($client, rtrim($banner));
        $this->promptCharacterName($client);
    }

    public function handleCommand(Client $client, string $commandString): void
    {
        if ($client->isAuthenticated()) {
            $this->commandResponseHandler->handle($client, $this->execute($this->commandResolver->resolve($client, $commandString)));

            return;
        }

        if ($client->isAuthenticating()) {
            $this->handlePassword($client, $commandString);

            return;
        }

        $this->handleCharacterName($client, trim($commandString));
    }

    private function handleCharacterName(Client $client, string $characterName): void
    {
        $response = $this->execute(new IdentifyCharacterCommand($characterName));
        Assert::isInstanceOf($response, IdentifyCharacterCommandResponse::class);
        if (!$response->exists()) {
            $this->write($client, sprintf('Il personaggio "%s" non esiste.', $characterName));
            $this->promptCharacterName($client);

            return;
        }

        $client->beginAuthenticationWithCharacterName($response->getCharacterName());
        $this->promptPassword($client);
    }

    private function handlePassword(Client $client, string $password): void
    {
        $response = $this->execute(new AuthenticateCharacterCommand($client->getAuthenticatingCharacterName(), $password));
        Assert::isInstanceOf($response, AuthenticateCharacterCommandResponse::class);
        $character = $response->getCharacter();
        if (null === $character) {
            if ($client->registerFailedPasswordAttempt() >= self::MAX_PASSWORD_ATTEMPTS) {
                $client->getSocket()->write(Telnet::WONT_ECHO);
                $this->write($client, 'Troppi tentativi falliti. Addio!');
                $client->getSocket()->close();

                return;
            }
            $this->write($client, 'Password errata.');
            $this->promptPassword($client);

            return;
        }

        $client->authenticate($character);
        $client->getSocket()->write(Telnet::WONT_ECHO);
        $this->commandResponseHandler->handle($client, $this->execute(new WhoAmICommand($character)));
        $this->commandResponseHandler->handle($client, $this->execute(new LookCommand($character)));
    }

    private function promptCharacterName(Client $client): void
    {
        $this->write($client, 'Inserisci il nome del tuo personaggio...');
    }

    private function promptPassword(Client $client): void
    {
        $client->getSocket()->write(Telnet::WILL_ECHO);
        $this->write($client, 'Inserisci la password...');
    }

    private function write(Client $client, string $message): void
    {
        $client->getSocket()->write($message.PHP_EOL.PHP_EOL);
    }

    private function execute(CommandInterface $command): CommandResponseInterface
    {
        return $this->commandRouter->resolve($command)->execute($command);
    }
}
