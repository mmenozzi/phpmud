<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommand;
use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommandResponse;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommand;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommandResponse;
use PHPMud\World\Application\Character\WhoAmI\WhoAmICommand;
use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\CommandRouter;
use PHPMud\World\Application\Movement\Look\LookCommand;
use PHPMud\World\Application\Time\CurrentDayPhase\CurrentDayPhaseCommand;
use Webmozart\Assert\Assert;

final readonly class TelnetClientHandler
{
    private const string BANNER_PATH = __DIR__.'/Resources/banner.txt';

    public function __construct(
        private CommandResolver $commandResolver,
        private CommandRouter $commandRouter,
    ) {
    }

    public function handleConnection(TelnetClient $client): void
    {
        Assert::null($client->getCharacterId());
        $banner = file_get_contents(self::BANNER_PATH);
        Assert::string($banner);
        $this->write($client, rtrim($banner));
        $this->promptCharacterName($client);
    }

    public function handleCommand(TelnetClient $client, string $commandString): void
    {
        $characterId = $client->getCharacterId();
        if (null !== $characterId) {
            $client->send($this->execute($this->commandResolver->resolve($characterId, $commandString)));

            return;
        }

        if ($client->isAuthenticating()) {
            $this->handlePassword($client, $commandString);

            return;
        }

        $this->handleCharacterName($client, trim($commandString));
    }

    private function handleCharacterName(TelnetClient $client, string $characterName): void
    {
        $response = $this->execute(new IdentifyCharacterCommand($characterName));
        Assert::isInstanceOf($response, IdentifyCharacterCommandResponse::class);
        $identifiedName = $response->getCharacterName();
        if (null === $identifiedName) {
            $this->write($client, 'Nome non ammesso: solo lettere, da 3 a 15.');
            $this->promptCharacterName($client);

            return;
        }
        if (!$response->exists()) {
            $this->write($client, sprintf('Il personaggio "%s" non esiste.', $identifiedName->toString()));
            $this->promptCharacterName($client);

            return;
        }

        $client->beginAuthenticationWithCharacterName($identifiedName);
        $this->promptPassword($client);
    }

    private function handlePassword(TelnetClient $client, string $password): void
    {
        $response = $this->execute(new AuthenticateCharacterCommand(
            $client->getAuthenticatingCharacterName(),
            $password,
            $client->getFailedPasswordAttempts(),
        ));
        Assert::isInstanceOf($response, AuthenticateCharacterCommandResponse::class);
        $characterId = $response->getCharacterId();
        if (null === $characterId) {
            $client->registerFailedPasswordAttempt();
            if ($response->areAttemptsExhausted()) {
                $client->getSocket()->write(Telnet::WONT_ECHO);
                $this->write($client, 'Troppi tentativi falliti. Addio!');
                $client->getSocket()->close();

                return;
            }
            $this->write($client, 'Password errata.');
            $this->promptPassword($client);

            return;
        }

        $client->authenticate($characterId);
        $client->getSocket()->write(Telnet::WONT_ECHO);
        $client->send($this->execute(new WhoAmICommand($characterId)));
        $client->send($this->execute(new LookCommand($characterId)));
        $client->send($this->execute(new CurrentDayPhaseCommand()));
    }

    private function promptCharacterName(TelnetClient $client): void
    {
        $this->write($client, 'Inserisci il nome del tuo personaggio...');
    }

    private function promptPassword(TelnetClient $client): void
    {
        $client->getSocket()->write(Telnet::WILL_ECHO);
        $this->write($client, 'Inserisci la password...');
    }

    private function write(TelnetClient $client, string $message): void
    {
        $client->getSocket()->write($message.PHP_EOL.PHP_EOL);
    }

    private function execute(CommandInterface $command): CommandResponseInterface
    {
        return $this->commandRouter->resolve($command)->execute($command);
    }
}
