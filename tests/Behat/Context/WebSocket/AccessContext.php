<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\WebSocket;

use Amp\Websocket\Client\WebsocketConnectException;
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
     * @When /^I connect to the game from a web page on "([^"]*)"$/
     */
    public function iConnectToTheGameFromAWebPageOn(string $origin): void
    {
        try {
            $this->connect(origin: $origin);
        } catch (WebsocketConnectException $exception) {
            $this->sharedStorage->set('connect_exception', $exception);
        }
    }

    /**
     * @Then /^my connection should be refused$/
     */
    public function myConnectionShouldBeRefused(): void
    {
        $exception = $this->sharedStorage->get('connect_exception');
        Assert::isInstanceOf($exception, WebsocketConnectException::class);
        Assert::same($exception->getResponse()->getStatus(), 403);
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
     * @Then /^I should be told that the character name is not allowed$/
     */
    public function iShouldBeToldThatTheCharacterNameIsNotAllowed(): void
    {
        Assert::eq($this->getIdentifyResponse(), ['type' => 'identified', 'name' => null, 'exists' => false, 'allowed' => false]);
    }

    /**
     * @Then /^I should be offered to create the character "([^"]*)"$/
     */
    public function iShouldBeOfferedToCreateTheCharacter(string $name): void
    {
        Assert::eq($this->getIdentifyResponse(), ['type' => 'identified', 'name' => $name, 'exists' => false, 'allowed' => true]);
    }

    /**
     * Confirming the name and repeating the password happen in the browser client, not in the protocol.
     *
     * @When /^I confirm the new character name$/
     *
     * @Then /^I should be asked for the sex of my character$/
     */
    public function nothingToDoInTheProtocol(): void
    {
    }

    /**
     * @When /^I choose that my character is (male|female)$/
     */
    public function iChooseThatMyCharacterIs(string $sex): void
    {
        $this->sharedStorage->set('sex', $sex);
    }

    /**
     * @Then /^I should be prompted for a new password$/
     */
    public function iShouldBePromptedForANewPassword(): void
    {
        if ($this->sharedStorage->has('create_response')) {
            Assert::eq($this->sharedStorage->get('create_response')['type'], 'creation_failed');
        }
    }

    /**
     * @When /^I choose the password "([^"]*)"$/
     */
    public function iChooseThePassword(string $password): void
    {
        $this->sendMessage([
            'type' => 'create',
            'name' => $this->getIdentifyResponse()['name'],
            'password' => $password,
            'sex' => $this->sharedStorage->get('sex'),
        ]);
        $response = $this->getNextResponse();
        $this->sharedStorage->set('create_response', $response);
        $this->sharedStorage->set('login_response', $response);
    }

    /**
     * @Then /^I should be told that the password is too short$/
     */
    public function iShouldBeToldThatThePasswordIsTooShort(): void
    {
        Assert::eq($this->sharedStorage->get('create_response'), ['type' => 'creation_failed', 'reason' => 'password_too_short']);
    }

    /**
     * @When /^I try to create the character "([^"]*)" with password "([^"]*)"$/
     */
    public function iTryToCreateTheCharacterWithPassword(string $name, string $password): void
    {
        $this->sendMessage(['type' => 'create', 'name' => $name, 'password' => $password, 'sex' => 'male']);
        $this->sharedStorage->set('create_response', $this->getNextResponse());
    }

    /**
     * @Then /^I should be told that the character name is taken$/
     */
    public function iShouldBeToldThatTheCharacterNameIsTaken(): void
    {
        Assert::eq($this->sharedStorage->get('create_response'), ['type' => 'creation_failed', 'reason' => 'name_taken']);
    }

    /**
     * @Given /^I have created the character "([^"]*)" with password "([^"]*)"$/
     */
    public function iHaveCreatedTheCharacterWithPassword(string $name, string $password): void
    {
        $this->connect('creator');
        $this->sendMessage(['type' => 'create', 'name' => $name, 'password' => $password, 'sex' => 'male'], 'creator');
        Assert::eq($this->getNextResponse('creator'), ['type' => 'character_created']);
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
        $response = $this->getNextResponse();
        $this->sharedStorage->set('authenticate_response', $response);
        $this->sharedStorage->set('login_response', $response);
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
        $loginResponse = $this->sharedStorage->get('login_response');
        Assert::isArray($loginResponse);
        Assert::inArray($loginResponse['type'] ?? null, ['authenticated', 'character_created']);
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
        $this->sendMessage(['type' => 'identify', 'name' => $character->getName()->toString()], $client);
        Assert::eq($this->getNextResponse($client), ['type' => 'identified', 'name' => $character->getName()->toString(), 'exists' => true, 'allowed' => true]);
        $this->sendMessage(['type' => 'authenticate', 'password' => 'p4ssw0rd'], $client);
        Assert::eq($this->getNextResponse($client), ['type' => 'authenticated']);
        Assert::eq($this->getNextResponse($client), ['type' => 'whoami', 'name' => $character->getName()->toString()]);
        Assert::eq($this->getNextResponse($client)['type'], 'location');
        $this->sharedStorage->set(sprintf('current_day_phase.%s', $client), $this->getNextResponse($client));
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
