<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Server;

final class UnexpectedErrorLogger
{
    // No stack trace: its arguments may hold what a player typed, passwords included.
    public static function log(string $context, \Throwable $error): void
    {
        error_log(sprintf(
            '%s: %s: %s in %s:%d',
            $context,
            $error::class,
            $error->getMessage(),
            $error->getFile(),
            $error->getLine(),
        ));
    }
}
