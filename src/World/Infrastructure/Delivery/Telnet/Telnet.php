<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\Telnet;

final class Telnet
{
    public const string WILL_ECHO = "\xFF\xFB\x01";

    public const string WONT_ECHO = "\xFF\xFC\x01";

    public static function stripNegotiation(string $data): string
    {
        return preg_replace('/\xFF\xFA.*?\xFF\xF0|\xFF[\xFB-\xFE].|\xFF[\xF0-\xF9]/s', '', $data) ?? $data;
    }
}
