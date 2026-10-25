<?php

declare(strict_types=1);

namespace PHPMud\Tests\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use PHPMud\Tests\Behat\Service\SharedStorage;
use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommand;
use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommandExecutor;
use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommandResponse;
use PHPMud\World\Application\Access\CreateCharacter\CreateCharacterCommand;
use PHPMud\World\Application\Access\CreateCharacter\CreateCharacterCommandExecutor;
use PHPMud\World\Application\Access\CreateCharacter\CreateCharacterCommandResponse;
use PHPMud\World\Application\Access\CreateCharacter\CreationFailure;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommand;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommandExecutor;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommandResponse;
use PHPMud\World\Application\Character\WhoAmI\WhoAmICommand;
use PHPMud\World\Application\Character\WhoAmI\WhoAmICommandExecutor;
use PHPMud\World\Application\Character\WhoAmI\WhoAmICommandResponse;
use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Domain\Character\CharacterRepositoryInterface;
use PHPMud\World\Domain\Character\Sex;
use Webmozart\Assert\Assert;

final class AccessContext implements Context
{
    public function __construct(
        private readonly SharedStorage $sharedStorage,
        private readonly IdentifyCharacterCommandExecutor $identifyCharacterCommandExecutor,
        private readonly AuthenticateCharacterCommandExecutor $authenticateCharacterCommandExecutor,
        private readonly WhoAmICommandExecutor $whoAmICommandExecutor,
        private readonly CreateCharacterCommandExecutor $createCharacterCommandExecutor,
        private readonly CharacterRepositoryInterface $characterRepository,
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
     * @Given /^"([^"]*)" is connected to the game$/
     */
    public function characterIsConnectedToTheGame(string $name): void
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
        Assert::eq($response->getCharacterName()?->toString(), $name);
    }

    /**
     * @Then /^I should be told that the character name is not allowed$/
     */
    public function iShouldBeToldThatTheCharacterNameIsNotAllowed(): void
    {
        Assert::false($this->getIdentifyResponse()->isNameAllowed());
    }

    /**
     * @Then /^I should be offered to create the character "([^"]*)"$/
     */
    public function iShouldBeOfferedToCreateTheCharacter(string $name): void
    {
        $response = $this->getIdentifyResponse();
        Assert::false($response->exists());
        Assert::eq($response->getCharacterName()?->toString(), $name);
    }

    /**
     * The confirmation of the name is a dialogue of the telnet adapter, not a step of the use case.
     *
     * @When /^I confirm the new character name$/
     *
     * @Then /^I should be asked for the sex of my character$/
     */
    public function nothingToDoInTheDomain(): void
    {
    }

    /**
     * @When /^I choose that my character is (male|female)$/
     */
    public function iChooseThatMyCharacterIs(string $sex): void
    {
        $this->sharedStorage->set('sex', Sex::from($sex));
    }

    /**
     * @Then /^I should be prompted for a new password$/
     */
    public function iShouldBePromptedForANewPassword(): void
    {
        if ($this->sharedStorage->has('create_response')) {
            Assert::false($this->getCreateResponse()->isSuccessful());
        }
    }

    /**
     * @When /^I choose the password "([^"]*)"$/
     */
    public function iChooseThePassword(string $password): void
    {
        $characterName = $this->getIdentifyResponse()->getCharacterName();
        Assert::notNull($characterName);
        $sex = $this->sharedStorage->get('sex');
        Assert::isInstanceOf($sex, Sex::class);
        $this->createCharacter($characterName->toString(), $password, $sex);
    }

    /**
     * @Given /^I have created the character "([^"]*)" with password "([^"]*)"$/
     */
    public function iHaveCreatedTheCharacterWithPassword(string $name, string $password): void
    {
        $this->createCharacter($name, $password, Sex::Male);
        Assert::true($this->getCreateResponse()->isSuccessful());
    }

    /**
     * @Then /^I should be told that the password is too short$/
     */
    public function iShouldBeToldThatThePasswordIsTooShort(): void
    {
        Assert::same($this->getCreateResponse()->getFailure(), CreationFailure::PasswordTooShort);
    }

    /**
     * @Then /^I should be prompted for my password$/
     */
    public function iShouldBePromptedForMyPassword(): void
    {
        Assert::true($this->getIdentifyResponse()->exists());
        if ($this->sharedStorage->has('authenticate_response')) {
            Assert::false($this->getAuthenticateResponse()->isSuccessful());
            Assert::false($this->getAuthenticateResponse()->areAttemptsExhausted());
        }
    }

    /**
     * @When /^I enter the password "([^"]*)"$/
     */
    public function iEnterThePassword(string $password): void
    {
        $failedAttempts = $this->sharedStorage->has('failed_password_attempts') ? $this->sharedStorage->get('failed_password_attempts') : 0;
        $characterName = $this->getIdentifyResponse()->getCharacterName();
        Assert::notNull($characterName);
        $response = $this->authenticateCharacterCommandExecutor->execute(
            new AuthenticateCharacterCommand($characterName, $password, $failedAttempts),
        );
        if (!$response->isSuccessful()) {
            $this->sharedStorage->set('failed_password_attempts', $failedAttempts + 1);
        }
        $this->sharedStorage->set('authenticate_response', $response);
        $this->sharedStorage->set('logged_in_character_id', $response->getCharacterId());
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
        $characterId = $this->sharedStorage->get('logged_in_character_id');
        Assert::isInstanceOf($characterId, CharacterId::class);
        $response = $this->whoAmICommandExecutor->execute(new WhoAmICommand($characterId));
        Assert::isInstanceOf($response, WhoAmICommandResponse::class);
        Assert::eq($response->getCharacterName()->toString(), $name);
    }

    /**
     * @Then /^I should be disconnected$/
     */
    public function iShouldBeDisconnected(): void
    {
        Assert::false($this->getAuthenticateResponse()->isSuccessful());
        Assert::true($this->getAuthenticateResponse()->areAttemptsExhausted());
    }

    private function createCharacter(string $name, string $password, Sex $sex): void
    {
        $response = $this->createCharacterCommandExecutor->execute(new CreateCharacterCommand($name, $password, $sex));
        $this->sharedStorage->set('create_response', $response);
        $characterId = $response->getCharacterId();
        if (null === $characterId) {
            return;
        }
        $this->sharedStorage->set('logged_in_character_id', $characterId);
        $this->sharedStorage->set('character', $this->characterRepository->get($characterId));
    }

    private function getCreateResponse(): CreateCharacterCommandResponse
    {
        $response = $this->sharedStorage->get('create_response');
        Assert::isInstanceOf($response, CreateCharacterCommandResponse::class);

        return $response;
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
