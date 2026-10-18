<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Service;

use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Application\NotifierInterface;
use PHPMud\World\Domain\Character\CharacterId;

final class RecordingNotifier implements NotifierInterface
{
    /**
     * @var list<array{?CharacterId, NotificationInterface}>
     */
    private array $notifications = [];

    public function notify(CharacterId $recipient, NotificationInterface $notification): void
    {
        $this->notifications[] = [$recipient, $notification];
    }

    public function notifyEveryone(NotificationInterface $notification): void
    {
        $this->notifications[] = [null, $notification];
    }

    /**
     * @return list<NotificationInterface>
     */
    public function getNotificationsReceivedBy(CharacterId $characterId): array
    {
        $received = [];
        foreach ($this->notifications as [$recipient, $notification]) {
            if (null === $recipient || $recipient->equals($characterId)) {
                $received[] = $notification;
            }
        }

        return $received;
    }
}
