<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\WebSocket;

use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommandResponse;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommandResponse;
use PHPMud\World\Application\Character\WhoAmI\WhoAmICommandResponse;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\Help\Unknown\UnknownCommandResponse;
use PHPMud\World\Application\Movement\CharacterMoved\CharacterArrivedNotification;
use PHPMud\World\Application\Movement\CharacterMoved\CharacterLeftNotification;
use PHPMud\World\Application\Movement\LocationView;
use PHPMud\World\Application\Movement\Look\LookCommandResponse;
use PHPMud\World\Application\Movement\Move\MoveCommandResponse;
use PHPMud\World\Application\NotificationInterface;
use PHPMud\World\Application\Time\CurrentDayPhase\CurrentDayPhaseCommandResponse;
use PHPMud\World\Application\Time\DayPhaseStarted\DayPhaseStartedNotification;

final class MessageSerializer
{
    public function serialize(CommandResponseInterface $commandResponse): string
    {
        return $this->encode($this->normalize($commandResponse));
    }

    public function serializeNotification(NotificationInterface $notification): string
    {
        return $this->encode($this->normalizeNotification($notification));
    }

    public function serializeError(string $message): string
    {
        return $this->encode(['type' => 'error', 'message' => $message]);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalize(CommandResponseInterface $commandResponse): array
    {
        if ($commandResponse instanceof IdentifyCharacterCommandResponse) {
            return ['type' => 'identified', 'name' => $commandResponse->getCharacterName(), 'exists' => $commandResponse->exists()];
        }

        if ($commandResponse instanceof AuthenticateCharacterCommandResponse) {
            if ($commandResponse->isSuccessful()) {
                return ['type' => 'authenticated'];
            }

            return ['type' => $commandResponse->areAttemptsExhausted() ? 'too_many_attempts' : 'authentication_failed'];
        }

        if ($commandResponse instanceof WhoAmICommandResponse) {
            return ['type' => 'whoami', 'name' => $commandResponse->getCharacterName()];
        }

        if ($commandResponse instanceof LookCommandResponse) {
            return $this->normalizeLocation($commandResponse->getLocationView());
        }

        if ($commandResponse instanceof MoveCommandResponse) {
            if (!$commandResponse->isSuccessful()) {
                return ['type' => 'move_failed', 'direction' => $commandResponse->getDirection()->value];
            }

            return $this->normalizeLocation($commandResponse->getLocationView());
        }

        if ($commandResponse instanceof CurrentDayPhaseCommandResponse) {
            return ['type' => 'day_phase', 'day_phase' => $commandResponse->getDayPhase()->value];
        }

        if ($commandResponse instanceof UnknownCommandResponse) {
            return ['type' => 'unknown_command'];
        }

        throw new \InvalidArgumentException(sprintf('Command response of type "%s" is not supported.', get_class($commandResponse)));
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeNotification(NotificationInterface $notification): array
    {
        if ($notification instanceof DayPhaseStartedNotification) {
            return ['type' => 'day_phase_started', 'day_phase' => $notification->getDayPhase()->value];
        }

        if ($notification instanceof CharacterLeftNotification) {
            return ['type' => 'character_left', 'name' => $notification->getCharacterName(), 'via' => $notification->getVia()?->value];
        }

        if ($notification instanceof CharacterArrivedNotification) {
            return ['type' => 'character_arrived', 'name' => $notification->getCharacterName(), 'via' => $notification->getVia()?->value];
        }

        throw new \InvalidArgumentException(sprintf('Notification of type "%s" is not supported.', get_class($notification)));
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeLocation(LocationView $locationView): array
    {
        return [
            'type' => 'location',
            'id' => $locationView->getId(),
            'name' => $locationView->getName(),
            'description' => $locationView->getDescription(),
            'exits' => (object) $locationView->getExits(),
        ];
    }

    /**
     * @param array<string, mixed> $message
     */
    private function encode(array $message): string
    {
        return json_encode($message, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
