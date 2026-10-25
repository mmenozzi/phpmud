<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Telnet;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Domain\Character\Character;
use Webmozart\Assert\Assert;

final class AccessContext implements Context
{
    use TelnetClientTrait;

    public function __construct(SharedStorage $sharedStorage)
    {
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @When /^I connect to the game$/
     */
    public function iConnectToTheGame()
    {
        $this->connect();
        $this->sharedStorage->set('banner', $this->getNextResponse());
    }

    /**
     * @Then /^I should see the MUD logo$/
     */
    public function iShouldSeeTheMudLogo(): void
    {
        $banner = file_get_contents(__DIR__.'/../../../../src/World/Infrastructure/Delivery/Telnet/Resources/banner.txt');
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
        $this->writeLine($name);
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
        $this->writeLine($password);
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
     * @Then /^I should be told that the character name is not allowed$/
     */
    public function iShouldBeToldThatTheCharacterNameIsNotAllowed(): void
    {
        Assert::eq($this->getNextResponse(), 'Nome non ammesso: solo lettere, da 3 a 15.');
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
        Assert::isEmpty(socket_read($this->getSocket(), 1024));
    }

    /**
     * @Given /^I am connected to the game$/
     */
    public function iAmConnectedToTheGame(): void
    {
        /** @var Character $character */
        $character = $this->sharedStorage->get('character');
        $this->logIn($character, 'me');
    }

    /**
     * @Given /^"([^"]*)" is connected to the game$/
     */
    public function characterIsConnectedToTheGame(string $name): void
    {
        /** @var array<string, Character> $characters */
        $characters = $this->sharedStorage->get('characters');
        $this->logIn($characters[$name], $name);
    }

    private function logIn(Character $character, string $client): void
    {
        $this->connect($client);
        Assert::notEmpty($this->getNextResponse($client)); // banner
        Assert::eq($this->getNextResponse($client), 'Inserisci il nome del tuo personaggio...');
        $this->writeLine($character->getName()->toString(), $client);
        Assert::eq($this->getNextResponse($client), 'Inserisci la password...');
        $this->writeLine('p4ssw0rd', $client);
        Assert::eq($this->getNextResponse($client), sprintf('Il tuo nome è "%s".', $character->getName()->toString()));
        Assert::notEmpty($this->getNextResponse($client)); // current location description
        $this->sharedStorage->set(sprintf('current_day_phase.%s', $client), $this->getNextResponse($client));
    }
}
