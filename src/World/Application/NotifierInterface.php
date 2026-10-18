<?php

declare(strict_types=1);

namespace PHPMud\World\Application;

use PHPMud\World\Domain\Character\CharacterId;

interface NotifierInterface
{
    public function notify(CharacterId $recipient, NotificationInterface $notification): void;

    public function notifyEveryone(NotificationInterface $notification): void;
}
