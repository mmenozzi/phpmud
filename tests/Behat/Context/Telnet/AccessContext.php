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
     * @Then /^I should be told that the character name is not allowed$/
     */
    public function iShouldBeToldThatTheCharacterNameIsNotAllowed(): void
    {
        Assert::eq($this->getNextResponse(), 'Nome non ammesso: solo lettere, da 3 a 15.');
    }

    /**
     * @Then /^I should be offered to create the character "([^"]*)"$/
     */
    public function iShouldBeOfferedToCreateTheCharacter(string $name): void
    {
        Assert::eq($this->getNextResponse(), sprintf('Nuovo personaggio. È davvero "%s" il nome che vuoi? (si/no)', $name));
    }

    /**
     * @When /^I confirm the new character name$/
     */
    public function iConfirmTheNewCharacterName(): void
    {
        $this->writeLine('si');
    }

    /**
     * @When /^I decline the new character name$/
     */
    public function iDeclineTheNewCharacterName(): void
    {
        $this->writeLine('no');
    }

    /**
     * @Then /^I should be asked for the sex of my character$/
     */
    public function iShouldBeAskedForTheSexOfMyCharacter(): void
    {
        Assert::regex($this->getNextResponse(), '/^\S+ è maschio o femmina\? \(m\/f\)$/u');
    }

    /**
     * @When /^I choose that my character is (male|female)$/
     */
    public function iChooseThatMyCharacterIs(string $sex): void
    {
        $this->writeLine('male' === $sex ? 'm' : 'f');
    }

    /**
     * @Then /^I should be prompted for a new password$/
     */
    public function iShouldBePromptedForANewPassword(): void
    {
        Assert::regex($this->getNextResponse(), '/^Scegli una password per \S+\.\.\.$/u');
        Assert::true($this->isInputHidden());
    }

    /**
     * @When /^I choose the password "([^"]*)"$/
     */
    public function iChooseThePassword(string $password): void
    {
        $this->iChooseThePasswordButRepeat($password, $password);
    }

    /**
     * @When /^I choose the password "([^"]*)" but repeat "([^"]*)"$/
     */
    public function iChooseThePasswordButRepeat(string $password, string $repeatedPassword, string $client = 'me'): void
    {
        $this->writeLine($password, $client);
        Assert::eq($this->getNextResponse($client), 'Reinserisci la password...');
        $this->writeLine($repeatedPassword, $client);
    }

    /**
     * @Then /^I should be told that the passwords do not match$/
     */
    public function iShouldBeToldThatThePasswordsDoNotMatch(): void
    {
        Assert::eq($this->getNextResponse(), 'Le password non coincidono.');
    }

    /**
     * @Then /^I should be told that the password is too short$/
     */
    public function iShouldBeToldThatThePasswordIsTooShort(): void
    {
        Assert::eq($this->getNextResponse(), 'La password è troppo corta: almeno 4 caratteri.');
    }

    /**
     * @Given /^I have created the character "([^"]*)" with password "([^"]*)"$/
     */
    public function iHaveCreatedTheCharacterWithPassword(string $name, string $password): void
    {
        $this->connect('creator');
        Assert::notEmpty($this->getNextResponse('creator')); // banner
        Assert::eq($this->getNextResponse('creator'), 'Inserisci il nome del tuo personaggio...');
        $this->writeLine($name, 'creator');
        Assert::notEmpty($this->getNextResponse('creator')); // name confirmation
        $this->writeLine('si', 'creator');
        Assert::notEmpty($this->getNextResponse('creator')); // sex
        $this->writeLine('m', 'creator');
        Assert::notEmpty($this->getNextResponse('creator')); // new password
        $this->iChooseThePasswordButRepeat($password, $password, 'creator');
        Assert::eq($this->getNextResponse('creator'), sprintf('Il tuo nome è "%s".', $name));
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
