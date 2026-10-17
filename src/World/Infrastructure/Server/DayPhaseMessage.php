<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

use PHPMud\World\Domain\Time\DayPhase;

final class DayPhaseMessage
{
    public static function of(DayPhase $dayPhase): string
    {
        return match ($dayPhase) {
            DayPhase::Dawn => 'Il sole sorge a est: un nuovo giorno ha inizio.',
            DayPhase::Noon => 'Il sole è alto nel cielo: è mezzogiorno.',
            DayPhase::Sunset => 'Il sole tramonta a ovest, tingendo il cielo di rosso.',
            DayPhase::Night => 'Cala la notte.',
        };
    }
}
