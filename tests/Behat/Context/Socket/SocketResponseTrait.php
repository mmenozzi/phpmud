<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Socket;

use PHPMud\Domain\DayPhase;
use PHPMud\Infrastructure\Server\DayPhaseMessage;
use PHPMud\Infrastructure\Server\Telnet;
use PHPMud\Tests\Behat\Service\SharedStorage;
use Webmozart\Assert\Assert;

trait SocketResponseTrait
{
    private readonly SharedStorage $sharedStorage;

    private function getNextResponse(): string
    {
        $this->readUntil(fn (): bool => [] !== $this->getQueue('responses'));
        $responses = $this->getQueue('responses');
        Assert::notEmpty($responses);
        $nextResponse = array_shift($responses);
        $this->sharedStorage->set('responses', $responses);

        return $nextResponse;
    }

    private function waitForBroadcast(string $message): void
    {
        $this->readUntil(fn (): bool => in_array($message, $this->getQueue('broadcasts'), true));
        $broadcasts = $this->getQueue('broadcasts');
        $index = array_search($message, $broadcasts, true);
        Assert::integer($index, sprintf('Broadcast "%s" not received.', $message));
        $this->sharedStorage->set('broadcasts', array_slice($broadcasts, $index + 1));
    }

    private function readUntil(callable $condition): void
    {
        $socket = $this->sharedStorage->get('socket');
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);
        $buffer = $this->sharedStorage->has('buffer') ? $this->sharedStorage->get('buffer') : '';
        $deadline = microtime(true) + 5;
        while (!$condition() && microtime(true) < $deadline) {
            $chunk = socket_read($socket, 1024);
            if ('' === $chunk) {
                break;
            }
            if (false === $chunk) {
                continue;
            }
            $blocks = explode(PHP_EOL.PHP_EOL, $buffer.$this->stripTelnetNegotiation($chunk));
            $buffer = array_pop($blocks);
            foreach (array_filter($blocks) as $block) {
                $queue = $this->isBroadcast($block) ? 'broadcasts' : 'responses';
                $this->sharedStorage->set($queue, [...$this->getQueue($queue), $block]);
            }
        }
        $this->sharedStorage->set('buffer', $buffer);
    }

    /**
     * @return list<string>
     */
    private function getQueue(string $name): array
    {
        return $this->sharedStorage->has($name) ? $this->sharedStorage->get($name) : [];
    }

    private function isBroadcast(string $block): bool
    {
        return in_array($block, array_map(DayPhaseMessage::of(...), DayPhase::cases()), true);
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
