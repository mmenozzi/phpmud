<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Socket;

use Behat\Behat\Context\Context;
use PHPMud\Domain\Direction;
use PHPMud\Domain\Entity\Location;
use PHPMud\Tests\Behat\Service\SharedStorage;
use Webmozart\Assert\Assert;

final class MovementContext implements Context
{
    private ?\Socket $socket = null;

    public function __construct(private readonly SharedStorage $sharedStorage)
    {
    }

    /**
     * @When /^I move to (north|east|south|west|up|down)$/
     */
    public function iMoveTo(Direction $direction): void
    {
        if ($this->socket === null) {
            $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_connect($this->socket, '127.0.0.1', 10666);
        }
        socket_write($this->socket, $direction->value.PHP_EOL);
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 50, 'usec' => 0]);
        $buffer = '';
        $responses = [];
        while ($chunk = socket_read($this->socket, 1024)) {
            $buffer .= $chunk;
            if (!str_contains($buffer, PHP_EOL.PHP_EOL)) {
                continue;
            }
            $responses = array_filter(explode(PHP_EOL.PHP_EOL, $buffer));
            break;
        }
        Assert::count($responses, 1);
        $this->sharedStorage->set('last_response', $responses[0]);
    }

    /**
     * @Then /^I should see that I am in the (location "[^"]*")$/
     */
    public function iShouldSeeThatIAmInTheLocation(Location $location)
    {
        /** @var string $lastResponse */
        $lastResponse = $this->sharedStorage->get('last_response');
        Assert::contains($lastResponse, $location->getName());
        Assert::contains($lastResponse, $location->getDescription());
    }

    /**
     * @Then /^I should see that there is a (location "[^"]*") (north|east|south|west|up|down) from here$/
     */
    public function iShouldSeeThatThereIsALocationSouthFromLocation(Location $location, Direction $direction)
    {
        /** @var string $lastResponse */
        $lastResponse = $this->sharedStorage->get('last_response');
        $expected = sprintf('%s: %s', ucwords($direction->value), $location->getName());
        Assert::contains($lastResponse, $expected);
    }
}
