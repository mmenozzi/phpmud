<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\WebSocket;

use Amp\CancelledException;
use Amp\TimeoutCancellation;
use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\Client\WebsocketHandshake;
use PHPMud\Tests\Behat\Service\SharedStorage;
use Webmozart\Assert\Assert;

use function Amp\Websocket\Client\connect;

trait WebSocketClientTrait
{
    private readonly SharedStorage $sharedStorage;

    private function connect(string $client = 'me', string $origin = 'http://127.0.0.1:10667'): void
    {
        $handshake = (new WebsocketHandshake('ws://127.0.0.1:10667/ws'))->withHeader('Origin', $origin);
        $this->sharedStorage->set($this->clientKey('connection', $client), connect($handshake));
    }

    /**
     * @param array<string, mixed> $message
     */
    private function sendMessage(array $message, string $client = 'me'): void
    {
        $this->getConnection($client)->sendText(json_encode($message, JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<string, mixed>
     */
    private function getNextResponse(string $client = 'me'): array
    {
        $this->readUntil(fn (): bool => [] !== $this->getQueue('responses', $client), $client);
        $responses = $this->getQueue('responses', $client);
        Assert::notEmpty($responses, 'No response received.');
        $nextResponse = array_shift($responses);
        $this->sharedStorage->set($this->clientKey('responses', $client), $responses);

        return $nextResponse;
    }

    /**
     * @param array<string, mixed> $expected
     */
    private function waitForBroadcast(array $expected, string $client = 'me'): void
    {
        $this->readUntil(fn (): bool => in_array($expected, $this->getQueue('broadcasts', $client), true), $client);
        $broadcasts = $this->getQueue('broadcasts', $client);
        $index = array_search($expected, $broadcasts, true);
        Assert::integer($index, sprintf('Broadcast %s not received.', json_encode($expected)));
        $this->sharedStorage->set($this->clientKey('broadcasts', $client), array_slice($broadcasts, $index + 1));
    }

    private function isClosed(string $client = 'me'): bool
    {
        $this->readUntil(fn (): bool => $this->sharedStorage->has($this->clientKey('closed', $client)), $client);

        return $this->sharedStorage->has($this->clientKey('closed', $client));
    }

    private function readUntil(callable $condition, string $client): void
    {
        $connection = $this->getConnection($client);
        $deadline = microtime(true) + 5;
        while (!$condition() && ($remaining = $deadline - microtime(true)) > 0) {
            try {
                $message = $connection->receive(new TimeoutCancellation($remaining));
            } catch (CancelledException) {
                break;
            }
            if (null === $message) {
                $this->sharedStorage->set($this->clientKey('closed', $client), true);

                break;
            }
            $data = json_decode($message->buffer(), true, flags: JSON_THROW_ON_ERROR);
            Assert::isArray($data);
            Assert::string($data['type'] ?? null);
            $queue = $this->isBroadcast($data) ? 'broadcasts' : 'responses';
            $this->sharedStorage->set($this->clientKey($queue, $client), [...$this->getQueue($queue, $client), $data]);
        }
    }

    /**
     * @param array<string, mixed> $message
     */
    private function isBroadcast(array $message): bool
    {
        return in_array($message['type'], ['day_phase_started', 'character_left', 'character_arrived'], true);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getQueue(string $name, string $client): array
    {
        $key = $this->clientKey($name, $client);

        return $this->sharedStorage->has($key) ? $this->sharedStorage->get($key) : [];
    }

    private function getConnection(string $client): WebsocketConnection
    {
        $connection = $this->sharedStorage->get($this->clientKey('connection', $client));
        Assert::isInstanceOf($connection, WebsocketConnection::class);

        return $connection;
    }

    private function clientKey(string $name, string $client): string
    {
        return sprintf('websocket.%s.%s', $client, $name);
    }
}
