<?php

declare(strict_types=1);

namespace PHPMud\World\Application\Access\CreateCharacter;

enum CreationFailure: string
{
    case NameNotAllowed = 'name_not_allowed';
    case NameTaken = 'name_taken';
    case PasswordTooShort = 'password_too_short';
}
