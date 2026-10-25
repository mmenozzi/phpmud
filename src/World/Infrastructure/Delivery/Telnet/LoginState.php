<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

enum LoginState
{
    case AwaitingName;
    case AwaitingPassword;
    case ConfirmingNewName;
    case AwaitingSex;
    case AwaitingNewPassword;
    case RepeatingNewPassword;

    public function expectsPassword(): bool
    {
        return match ($this) {
            self::AwaitingPassword, self::AwaitingNewPassword, self::RepeatingNewPassword => true,
            default => false,
        };
    }
}
