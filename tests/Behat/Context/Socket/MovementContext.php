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
    private \Socket $socket;

    public function __construct(private readonly SharedStorage $sharedStorage)
    {
    }

    /**
     * @When /^I move to (north|east|south|west|up|down)$/
     */
    public function iMoveTo(Direction $direction): void
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->socket, '127.0.0.1', 10666);
        socket_write($this->socket, $direction->value.PHP_EOL);
    }

    /**
     * @Then /^I should see that I am in the (location "([^"]*)")$/
     */
    public function iShouldSeeThatIAmInTheLocation(Location $location)
    {
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
        $buffer = '';
        $responses = [];
        while ($chunk = socket_read($this->socket, 1024)) {
            $buffer .= $chunk;
            if (!str_contains($buffer, PHP_EOL.PHP_EOL)) {
                continue;
            }
            $responses = explode(PHP_EOL.PHP_EOL, $buffer);
            break;
        }
        Assert::notEmpty($responses);
        Assert::eq($responses[0], $location->getName());
    }
}
