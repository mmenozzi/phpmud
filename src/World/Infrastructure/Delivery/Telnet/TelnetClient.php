<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

use Amp\ByteStream\StreamException;
use Amp\Socket\ResourceSocket;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Domain\Character\CharacterName;
use PHPMud\World\Domain\Character\Sex;
use PHPMud\World\Infrastructure\Server\Client;
use PHPMud\World\Infrastructure\Server\ClientDisconnectedException;

final class TelnetClient implements Client
{
    private ?CharacterId $characterId = null;

    private LoginState $loginState = LoginState::AwaitingName;

    private ?CharacterName $characterName = null;

    private ?Sex $sex = null;

    private ?string $newPassword = null;

    private int $failedPasswordAttempts = 0;

    public function __construct(
        private readonly ResourceSocket $socket,
        private readonly CommandResponseRenderer $commandResponseRenderer,
    ) {
    }

    public function getSocket(): ResourceSocket
    {
        return $this->socket;
    }

    public function send(CommandResponseInterface $commandResponse): void
    {
        $this->write($this->commandResponseRenderer->render($commandResponse));
    }

    public function notify(NotificationInterface $notification): void
    {
        $this->write($this->commandResponseRenderer->renderNotification($notification));
    }

    private function write(string $text): void
    {
        try {
            $this->socket->write($text);
        } catch (StreamException $exception) {
            throw new ClientDisconnectedException($exception->getMessage(), previous: $exception);
        }
    }

    public function getCharacterId(): ?CharacterId
    {
        return $this->characterId;
    }

    public function isAuthenticated(): bool
    {
        return null !== $this->characterId;
    }

    public function getLoginState(): LoginState
    {
        return $this->loginState;
    }

    public function beginAuthenticationWithCharacterName(CharacterName $characterName): void
    {
        $this->characterName = $characterName;
        $this->loginState = LoginState::AwaitingPassword;
    }

    public function beginCreationWithCharacterName(CharacterName $characterName): void
    {
        $this->characterName = $characterName;
        $this->loginState = LoginState::ConfirmingNewName;
    }

    public function getCharacterName(): CharacterName
    {
        if (null === $this->characterName) {
            throw new \LogicException('No character name has been entered.');
        }

        return $this->characterName;
    }

    public function getFailedPasswordAttempts(): int
    {
        return $this->failedPasswordAttempts;
    }

    public function registerFailedPasswordAttempt(): void
    {
        ++$this->failedPasswordAttempts;
    }

    public function confirmNewCharacterName(): void
    {
        $this->loginState = LoginState::AwaitingSex;
    }

    public function chooseSex(Sex $sex): void
    {
        $this->sex = $sex;
        $this->loginState = LoginState::AwaitingNewPassword;
    }

    public function getSex(): Sex
    {
        if (null === $this->sex) {
            throw new \LogicException('No sex has been chosen.');
        }

        return $this->sex;
    }

    public function chooseNewPassword(string $password): void
    {
        $this->newPassword = $password;
        $this->loginState = LoginState::RepeatingNewPassword;
    }

    public function getNewPassword(): string
    {
        if (null === $this->newPassword) {
            throw new \LogicException('No new password has been chosen.');
        }

        return $this->newPassword;
    }

    public function chooseNewPasswordAgain(): void
    {
        $this->newPassword = null;
        $this->loginState = LoginState::AwaitingNewPassword;
    }

    public function restartLogin(): void
    {
        $this->characterName = null;
        $this->sex = null;
        $this->newPassword = null;
        $this->loginState = LoginState::AwaitingName;
    }

    public function authenticate(CharacterId $characterId): void
    {
        $this->characterId = $characterId;
        $this->restartLogin();
        $this->failedPasswordAttempts = 0;
    }
}
