<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Socket;

use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Domain\Time\DayPhase;
use PHPMud\World\Infrastructure\Delivery\Telnet\DayPhaseMessage;
use PHPMud\World\Infrastructure\Delivery\Telnet\Telnet;
use Webmozart\Assert\Assert;

trait SocketResponseTrait
{
    private readonly SharedStorage $sharedStorage;

    private function connect(string $client = 'me'): void
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($socket, '127.0.0.1', 10666);
        $this->sharedStorage->set($this->clientKey('socket', $client), $socket);
    }

    private function writeLine(string $line, string $client = 'me'): void
    {
        socket_write($this->getSocket($client), $line.PHP_EOL);
    }

    private function getSocket(string $client = 'me'): \Socket
    {
        $socket = $this->sharedStorage->get($this->clientKey('socket', $client));
        Assert::isInstanceOf($socket, \Socket::class);

        return $socket;
    }

    private function getNextResponse(string $client = 'me'): string
    {
        $this->readUntil(fn (): bool => [] !== $this->getQueue('responses', $client), $client);
        $responses = $this->getQueue('responses', $client);
        Assert::notEmpty($responses);
        $nextResponse = array_shift($responses);
        $this->sharedStorage->set($this->clientKey('responses', $client), $responses);

        return $nextResponse;
    }

    private function waitForBroadcast(string $message, string $client = 'me'): void
    {
        $this->readUntil(fn (): bool => in_array($message, $this->getQueue('broadcasts', $client), true), $client);
        $broadcasts = $this->getQueue('broadcasts', $client);
        $index = array_search($message, $broadcasts, true);
        Assert::integer($index, sprintf('Broadcast "%s" not received.', $message));
        $this->sharedStorage->set($this->clientKey('broadcasts', $client), array_slice($broadcasts, $index + 1));
    }

    private function readUntil(callable $condition, string $client): void
    {
        $socket = $this->getSocket($client);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);
        $bufferKey = $this->clientKey('buffer', $client);
        $buffer = $this->sharedStorage->has($bufferKey) ? $this->sharedStorage->get($bufferKey) : '';
        $deadline = microtime(true) + 5;
        while (!$condition() && microtime(true) < $deadline) {
            $chunk = socket_read($socket, 1024);
            if ('' === $chunk) {
                break;
            }
            if (false === $chunk) {
                continue;
            }
            $blocks = explode(PHP_EOL.PHP_EOL, $buffer.$this->stripTelnetNegotiation($chunk, $client));
            $buffer = array_pop($blocks);
            foreach (array_filter($blocks) as $block) {
                $queue = $this->isBroadcast($block) ? 'broadcasts' : 'responses';
                $this->sharedStorage->set($this->clientKey($queue, $client), [...$this->getQueue($queue, $client), $block]);
            }
        }
        $this->sharedStorage->set($bufferKey, $buffer);
    }

    /**
     * @return list<string>
     */
    private function getQueue(string $name, string $client): array
    {
        $key = $this->clientKey($name, $client);

        return $this->sharedStorage->has($key) ? $this->sharedStorage->get($key) : [];
    }

    private function isBroadcast(string $block): bool
    {
        return in_array($block, array_map(DayPhaseMessage::of(...), DayPhase::cases()), true)
            || 1 === preg_match('/^\S+ (va verso .+|arriva( d.+)?|se ne va)\.$/u', $block);
    }

    private function stripTelnetNegotiation(string $chunk, string $client): string
    {
        $willEchoPosition = strrpos($chunk, Telnet::WILL_ECHO);
        $wontEchoPosition = strrpos($chunk, Telnet::WONT_ECHO);
        if (false !== $willEchoPosition || false !== $wontEchoPosition) {
            $this->sharedStorage->set(
                $this->clientKey('server_echo', $client),
                false === $wontEchoPosition || $willEchoPosition > $wontEchoPosition,
            );
        }

        return Telnet::stripNegotiation($chunk);
    }

    private function isInputHidden(string $client = 'me'): bool
    {
        $key = $this->clientKey('server_echo', $client);

        return $this->sharedStorage->has($key) && true === $this->sharedStorage->get($key);
    }

    private function clientKey(string $name, string $client): string
    {
        return sprintf('client.%s.%s', $client, $name);
    }
}
