<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Socket;

use Behat\Behat\Context\Context;
use PHPMud\Domain\Entity\Character;
use PHPMud\Tests\Behat\Service\SharedStorage;
use Webmozart\Assert\Assert;

final class AccessContext implements Context
{
    use SocketResponseTrait;

    public function __construct(SharedStorage $sharedStorage)
    {
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @When /^I connect to the game$/
     */
    public function iConnectToTheGame()
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($socket, '127.0.0.1', 10666);
        $this->sharedStorage->set('socket', $socket);
    }

    /**
     * @Then /^I should be prompted for my character name$/
     */
    public function iShouldBePromptedForMyCharacterName()
    {
        Assert::eq($this->getNextResponse(), 'Please enter your character name...');
    }

    /**
     * @When /^I enter the character name "([^"]*)"$/
     */
    public function iEnterTheCharacterName(string $name)
    {
        socket_write($this->sharedStorage->get('socket'), $name.PHP_EOL);
    }

    /**
     * @Then /^I should be prompted for my password$/
     */
    public function iShouldBePromptedForMyPassword()
    {
        Assert::eq($this->getNextResponse(), 'Please enter your password...');
    }

    /**
     * @When /^I enter the password "([^"]*)"$/
     */
    public function iEnterThePassword(string $password)
    {
        socket_write($this->sharedStorage->get('socket'), $password.PHP_EOL);
    }

    /**
     * @Then /^I should see that I am "([^"]*)"$/
     */
    public function iShouldSeeThatIAm(string $name)
    {
        Assert::eq($this->getNextResponse(), sprintf('Your name is "%s".', $name));
    }

    /**
     * @Given /^I am connected to the game$/
     */
    public function iAmConnectedToTheGame()
    {
        /** @var Character $character */
        $character = $this->sharedStorage->get('character');
        $this->iConnectToTheGame();
        Assert::eq($this->getNextResponse(), 'Please enter your character name...');
        $this->iEnterTheCharacterName($character->getName());
        Assert::eq($this->getNextResponse(), 'Please enter your password...');
        $this->iEnterThePassword('p4ssw0rd');
        Assert::eq($this->getNextResponse(), sprintf('Your name is "%s".', $character->getName()));
        Assert::notEmpty($this->getNextResponse()); // This should be the current location description
    }
}
