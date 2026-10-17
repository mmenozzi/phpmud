<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Socket;

use Behat\Behat\Context\Context;
use PHPMud\Domain\Character\Character;
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
        $this->sharedStorage->set('banner', $this->getNextResponse());
    }

    /**
     * @Then /^I should see the MUD logo$/
     */
    public function iShouldSeeTheMudLogo(): void
    {
        $banner = file_get_contents(__DIR__.'/../../../../src/Infrastructure/Resources/banner.txt');
        Assert::string($banner);
        Assert::eq($this->sharedStorage->get('banner'), rtrim($banner));
    }

    /**
     * @Then /^I should be prompted for my character name$/
     */
    public function iShouldBePromptedForMyCharacterName()
    {
        Assert::eq($this->getNextResponse(), 'Inserisci il nome del tuo personaggio...');
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
        Assert::eq($this->getNextResponse(), 'Inserisci la password...');
        Assert::true($this->isInputHidden());
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
        Assert::eq($this->getNextResponse(), sprintf('Il tuo nome è "%s".', $name));
        Assert::false($this->isInputHidden());
    }

    /**
     * @Then /^I should be told that the character "([^"]*)" does not exist$/
     */
    public function iShouldBeToldThatTheCharacterDoesNotExist(string $name): void
    {
        Assert::eq($this->getNextResponse(), sprintf('Il personaggio "%s" non esiste.', $name));
    }

    /**
     * @Then /^I should be told that the password is wrong$/
     */
    public function iShouldBeToldThatThePasswordIsWrong(): void
    {
        Assert::eq($this->getNextResponse(), 'Password errata.');
    }

    /**
     * @Then /^I should be disconnected$/
     */
    public function iShouldBeDisconnected(): void
    {
        Assert::eq($this->getNextResponse(), 'Troppi tentativi falliti. Addio!');
        Assert::isEmpty(socket_read($this->sharedStorage->get('socket'), 1024));
    }

    /**
     * @Given /^I am connected to the game$/
     */
    public function iAmConnectedToTheGame()
    {
        /** @var Character $character */
        $character = $this->sharedStorage->get('character');
        $this->iConnectToTheGame();
        Assert::eq($this->getNextResponse(), 'Inserisci il nome del tuo personaggio...');
        $this->iEnterTheCharacterName($character->getName());
        Assert::eq($this->getNextResponse(), 'Inserisci la password...');
        $this->iEnterThePassword('p4ssw0rd');
        Assert::eq($this->getNextResponse(), sprintf('Il tuo nome è "%s".', $character->getName()));
        Assert::notEmpty($this->getNextResponse()); // This should be the current location description
    }
}
