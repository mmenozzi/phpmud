<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\WebSocket;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Domain\Character\Character;
use Webmozart\Assert\Assert;

final class AccessContext implements Context
{
    use WebSocketClientTrait;

    public function __construct(SharedStorage $sharedStorage)
    {
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @When /^I connect to the game$/
     */
    public function iConnectToTheGame(): void
    {
        $this->connect();
    }

    /**
     * @Then /^I should be prompted for my character name$/
     */
    public function iShouldBePromptedForMyCharacterName(): void
    {
        if ($this->sharedStorage->has('identify_response')) {
            Assert::false($this->getIdentifyResponse()['exists']);
        }
    }

    /**
     * @When /^I enter the character name "([^"]*)"$/
     */
    public function iEnterTheCharacterName(string $name): void
    {
        $this->sendMessage(['type' => 'identify', 'name' => $name]);
        $this->sharedStorage->set('identify_response', $this->getNextResponse());
    }

    /**
     * @Then /^I should be told that the character "([^"]*)" does not exist$/
     */
    public function iShouldBeToldThatTheCharacterDoesNotExist(string $name): void
    {
        Assert::eq($this->getIdentifyResponse(), ['type' => 'identified', 'name' => $name, 'exists' => false]);
    }

    /**
     * @Then /^I should be prompted for my password$/
     */
    public function iShouldBePromptedForMyPassword(): void
    {
        Assert::true($this->getIdentifyResponse()['exists']);
        if ($this->sharedStorage->has('authenticate_response')) {
            Assert::eq($this->getAuthenticateResponse()['type'], 'authentication_failed');
        }
    }

    /**
     * @When /^I enter the password "([^"]*)"$/
     */
    public function iEnterThePassword(string $password): void
    {
        $this->sendMessage(['type' => 'authenticate', 'password' => $password]);
        $this->sharedStorage->set('authenticate_response', $this->getNextResponse());
    }

    /**
     * @Then /^I should be told that the password is wrong$/
     */
    public function iShouldBeToldThatThePasswordIsWrong(): void
    {
        Assert::eq($this->getAuthenticateResponse()['type'], 'authentication_failed');
    }

    /**
     * @Then /^I should see that I am "([^"]*)"$/
     */
    public function iShouldSeeThatIAm(string $name): void
    {
        Assert::eq($this->getAuthenticateResponse()['type'], 'authenticated');
        Assert::eq($this->getNextResponse(), ['type' => 'whoami', 'name' => $name]);
    }

    /**
     * @Then /^I should be disconnected$/
     */
    public function iShouldBeDisconnected(): void
    {
        Assert::eq($this->getAuthenticateResponse()['type'], 'too_many_attempts');
        Assert::true($this->isClosed());
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
        $this->sendMessage(['type' => 'identify', 'name' => $character->getName()], $client);
        Assert::eq($this->getNextResponse($client), ['type' => 'identified', 'name' => $character->getName(), 'exists' => true]);
        $this->sendMessage(['type' => 'authenticate', 'password' => 'p4ssw0rd'], $client);
        Assert::eq($this->getNextResponse($client), ['type' => 'authenticated']);
        Assert::eq($this->getNextResponse($client), ['type' => 'whoami', 'name' => $character->getName()]);
        Assert::eq($this->getNextResponse($client)['type'], 'location');
    }

    /**
     * @return array<string, mixed>
     */
    private function getIdentifyResponse(): array
    {
        $response = $this->sharedStorage->get('identify_response');
        Assert::isArray($response);
        Assert::eq($response['type'] ?? null, 'identified');

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function getAuthenticateResponse(): array
    {
        $response = $this->sharedStorage->get('authenticate_response');
        Assert::isArray($response);

        return $response;
    }
}
