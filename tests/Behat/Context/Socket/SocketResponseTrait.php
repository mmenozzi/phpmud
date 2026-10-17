<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Socket;

use PHPMud\Infrastructure\Server\Telnet;
use PHPMud\Tests\Behat\Service\SharedStorage;
use Webmozart\Assert\Assert;

trait SocketResponseTrait
{
    private readonly SharedStorage $sharedStorage;

    private function getNextResponse(): string
    {
        $responses = $this->sharedStorage->has('responses') ? $this->sharedStorage->get('responses') : [];
        if (!empty($responses)) {
            $nextResponse = array_shift($responses);
            $this->sharedStorage->set('responses', $responses);

            return $nextResponse;
        }

        socket_set_option($this->sharedStorage->get('socket'), SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
        $buffer = '';
        while ($chunk = socket_read($this->sharedStorage->get('socket'), 1024)) {
            $buffer .= $this->stripTelnetNegotiation($chunk);
            if (!str_contains($buffer, PHP_EOL.PHP_EOL)) {
                continue;
            }
            $responses += array_filter(explode(PHP_EOL.PHP_EOL, $buffer));
            break;
        }
        Assert::notEmpty($responses);

        $nextResponse = array_shift($responses);
        $this->sharedStorage->set('responses', $responses);

        return $nextResponse;
    }

    private function stripTelnetNegotiation(string $chunk): string
    {
        $willEchoPosition = strrpos($chunk, Telnet::WILL_ECHO);
        $wontEchoPosition = strrpos($chunk, Telnet::WONT_ECHO);
        if (false !== $willEchoPosition || false !== $wontEchoPosition) {
            $this->sharedStorage->set('server_echo', false === $wontEchoPosition || $willEchoPosition > $wontEchoPosition);
        }

        return Telnet::stripNegotiation($chunk);
    }

    private function isInputHidden(): bool
    {
        return $this->sharedStorage->has('server_echo') && true === $this->sharedStorage->get('server_echo');
    }
}
