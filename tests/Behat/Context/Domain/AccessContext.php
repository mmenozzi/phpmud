<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommand;
use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommandExecutor;
use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommandResponse;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommand;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommandExecutor;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommandResponse;
use Webmozart\Assert\Assert;

final class AccessContext implements Context
{
    public function __construct(
        private readonly SharedStorage $sharedStorage,
        private readonly IdentifyCharacterCommandExecutor $identifyCharacterCommandExecutor,
        private readonly AuthenticateCharacterCommandExecutor $authenticateCharacterCommandExecutor,
    ) {
    }

    /**
     * @Given /^I am connected to the game$/
     *
     * @When /^I connect to the game$/
     */
    public function iConnectToTheGame(): void
    {
    }

    /**
     * @Then /^I should be prompted for my character name$/
     */
    public function iShouldBePromptedForMyCharacterName(): void
    {
        if ($this->sharedStorage->has('identify_response')) {
            Assert::false($this->getIdentifyResponse()->exists());
        }
    }

    /**
     * @When /^I enter the character name "([^"]*)"$/
     */
    public function iEnterTheCharacterName(string $name): void
    {
        $this->sharedStorage->set(
            'identify_response',
            $this->identifyCharacterCommandExecutor->execute(new IdentifyCharacterCommand($name)),
        );
    }

    /**
     * @Then /^I should be told that the character "([^"]*)" does not exist$/
     */
    public function iShouldBeToldThatTheCharacterDoesNotExist(string $name): void
    {
        $response = $this->getIdentifyResponse();
        Assert::false($response->exists());
        Assert::eq($response->getCharacterName(), $name);
    }

    /**
     * @Then /^I should be prompted for my password$/
     */
    public function iShouldBePromptedForMyPassword(): void
    {
        Assert::true($this->getIdentifyResponse()->exists());
        if ($this->sharedStorage->has('authenticate_response')) {
            Assert::false($this->getAuthenticateResponse()->isSuccessful());
        }
    }

    /**
     * @When /^I enter the password "([^"]*)"$/
     */
    public function iEnterThePassword(string $password): void
    {
        $response = $this->authenticateCharacterCommandExecutor->execute(
            new AuthenticateCharacterCommand($this->getIdentifyResponse()->getCharacterName(), $password),
        );
        $this->sharedStorage->set('authenticate_response', $response);
        if (null !== $response->getCharacter()) {
            $this->sharedStorage->set('character', $response->getCharacter());
        }
    }

    /**
     * @Then /^I should be told that the password is wrong$/
     */
    public function iShouldBeToldThatThePasswordIsWrong(): void
    {
        Assert::false($this->getAuthenticateResponse()->isSuccessful());
    }

    /**
     * @Then /^I should see that I am "([^"]*)"$/
     */
    public function iShouldSeeThatIAm(string $name): void
    {
        $character = $this->getAuthenticateResponse()->getCharacter();
        Assert::notNull($character);
        Assert::eq($character->getName(), $name);
    }

    private function getIdentifyResponse(): IdentifyCharacterCommandResponse
    {
        $response = $this->sharedStorage->get('identify_response');
        Assert::isInstanceOf($response, IdentifyCharacterCommandResponse::class);

        return $response;
    }

    private function getAuthenticateResponse(): AuthenticateCharacterCommandResponse
    {
        $response = $this->sharedStorage->get('authenticate_response');
        Assert::isInstanceOf($response, AuthenticateCharacterCommandResponse::class);

        return $response;
    }
}
