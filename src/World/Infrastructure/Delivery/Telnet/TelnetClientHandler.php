<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use Amp\ByteStream\StreamException;
use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommand;
use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommandResponse;
use PHPMud\World\Application\Access\CreateCharacter\CreateCharacterCommand;
use PHPMud\World\Application\Access\CreateCharacter\CreateCharacterCommandExecutor;
use PHPMud\World\Application\Access\CreateCharacter\CreateCharacterCommandResponse;
use PHPMud\World\Application\Access\CreateCharacter\CreationFailure;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommand;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommandResponse;
use PHPMud\World\Application\Character\WhoAmI\WhoAmICommand;
use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\CommandRouter;
use PHPMud\World\Application\Movement\Look\LookCommand;
use PHPMud\World\Application\Time\CurrentDayPhase\CurrentDayPhaseCommand;
use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Domain\Character\Sex;
use PHPMud\World\Infrastructure\Server\ClientDisconnectedException;
use PHPMud\World\Infrastructure\Server\UnexpectedErrorLogger;
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
        try {
            $this->dispatch($client, $commandString);
        } catch (ClientDisconnectedException|StreamException $exception) {
            throw $exception;
        } catch (\Throwable $error) {
            UnexpectedErrorLogger::log($client->getSocket()->getRemoteAddress()->toString(), $error);
            $this->write($client, 'Si è verificato un errore imprevisto.');
            if (!$client->isAuthenticated()) {
                $this->restartLogin($client);
            }
        }
    }

    private function dispatch(TelnetClient $client, string $commandString): void
    {
        $characterId = $client->getCharacterId();
        if (null !== $characterId) {
            $client->send($this->execute($this->commandResolver->resolve($characterId, $commandString)));

            return;
        }

        // Like DikuMUD's nanny, echo is turned back on after every password line and off again by the next password
        // prompt: clients such as Mudlet keep the masked line in the input box until the server stops hiding it.
        if ($client->getLoginState()->expectsPassword()) {
            $client->getSocket()->write(Telnet::WONT_ECHO);
        }

        match ($client->getLoginState()) {
            LoginState::AwaitingName => $this->handleCharacterName($client, trim($commandString)),
            LoginState::AwaitingPassword => $this->handlePassword($client, $commandString),
            LoginState::ConfirmingNewName => $this->handleNewNameConfirmation($client, trim($commandString)),
            LoginState::AwaitingSex => $this->handleSex($client, trim($commandString)),
            LoginState::AwaitingNewPassword => $this->handleNewPassword($client, $commandString),
            LoginState::RepeatingNewPassword => $this->handleRepeatedNewPassword($client, $commandString),
        };
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
            $client->beginCreationWithCharacterName($identifiedName);
            $this->write($client, sprintf('Nuovo personaggio. È davvero "%s" il nome che vuoi? (si/no)', $identifiedName->toString()));

            return;
        }

        $client->beginAuthenticationWithCharacterName($identifiedName);
        $this->promptPassword($client);
    }

    private function handlePassword(TelnetClient $client, string $password): void
    {
        $response = $this->execute(new AuthenticateCharacterCommand(
            $client->getCharacterName(),
            $password,
            $client->getFailedPasswordAttempts(),
        ));
        Assert::isInstanceOf($response, AuthenticateCharacterCommandResponse::class);
        $characterId = $response->getCharacterId();
        if (null === $characterId) {
            $client->registerFailedPasswordAttempt();
            if ($response->areAttemptsExhausted()) {
                $this->write($client, 'Troppi tentativi falliti. Addio!');
                $client->getSocket()->close();

                return;
            }
            $this->write($client, 'Password errata.');
            $this->promptPassword($client);

            return;
        }

        $this->enterTheGame($client, $characterId);
    }

    private function handleNewNameConfirmation(TelnetClient $client, string $answer): void
    {
        match (mb_strtolower($answer)) {
            'si', 's', 'sì' => $this->confirmNewName($client),
            'no', 'n' => $this->restartLogin($client),
            default => $this->write($client, 'Per favore, rispondi si o no.'),
        };
    }

    private function confirmNewName(TelnetClient $client): void
    {
        $client->confirmNewCharacterName();
        $this->promptSex($client);
    }

    private function handleSex(TelnetClient $client, string $answer): void
    {
        $sex = match (mb_strtolower($answer)) {
            'm', 'maschio' => Sex::Male,
            'f', 'femmina' => Sex::Female,
            default => null,
        };
        if (null === $sex) {
            $this->promptSex($client);

            return;
        }

        $client->chooseSex($sex);
        $this->promptNewPassword($client);
    }

    private function handleNewPassword(TelnetClient $client, string $password): void
    {
        $client->chooseNewPassword($password);
        $client->getSocket()->write(Telnet::WILL_ECHO);
        $this->write($client, 'Reinserisci la password...');
    }

    private function handleRepeatedNewPassword(TelnetClient $client, string $password): void
    {
        if ($password !== $client->getNewPassword()) {
            $client->chooseNewPasswordAgain();
            $this->write($client, 'Le password non coincidono.');
            $this->promptNewPassword($client);

            return;
        }

        $response = $this->execute(new CreateCharacterCommand(
            $client->getCharacterName()->toString(),
            $password,
            $client->getSex(),
        ));
        Assert::isInstanceOf($response, CreateCharacterCommandResponse::class);
        $characterId = $response->getCharacterId();
        if (null !== $characterId) {
            $this->enterTheGame($client, $characterId);

            return;
        }

        if (CreationFailure::PasswordTooShort === $response->getFailure()) {
            $client->chooseNewPasswordAgain();
            $this->write($client, sprintf('La password è troppo corta: almeno %d caratteri.', CreateCharacterCommandExecutor::MIN_PASSWORD_LENGTH));
            $this->promptNewPassword($client);

            return;
        }

        $this->write($client, sprintf('Non è più possibile creare il personaggio "%s".', $client->getCharacterName()->toString()));
        $this->restartLogin($client);
    }

    private function enterTheGame(TelnetClient $client, CharacterId $characterId): void
    {
        $client->authenticate($characterId);
        $client->send($this->execute(new WhoAmICommand($characterId)));
        $client->send($this->execute(new LookCommand($characterId)));
        $client->send($this->execute(new CurrentDayPhaseCommand()));
    }

    private function restartLogin(TelnetClient $client): void
    {
        $client->restartLogin();
        $this->promptCharacterName($client);
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

    private function promptSex(TelnetClient $client): void
    {
        $this->write($client, sprintf('%s è maschio o femmina? (m/f)', $client->getCharacterName()->toString()));
    }

    private function promptNewPassword(TelnetClient $client): void
    {
        $client->getSocket()->write(Telnet::WILL_ECHO);
        $this->write($client, sprintf('Scegli una password per %s...', $client->getCharacterName()->toString()));
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
