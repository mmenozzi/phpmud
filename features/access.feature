@access
Feature: Access
  In order to play the game
  As Player
  I want to access the world with my character

  Background:
    Given there is the location "A"
    And there is the character "HarryPotter" with password "hogwarts" in the location "A"

  @socket
  Scenario: Seeing the MUD logo when connecting
    When I connect to the game
    Then I should see the MUD logo
    And I should be prompted for my character name

  @domain @socket
  Scenario: Accessing the game with correct password
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "HarryPotter"
    Then I should be prompted for my password
    When I enter the password "hogwarts"
    Then I should see that I am "HarryPotter"
    And I should see that I am in the location "A"

  @domain @socket
  Scenario: Entering the name of a character that does not exist
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "Voldemort"
    Then I should be told that the character "Voldemort" does not exist
    And I should be prompted for my character name

  @domain @socket
  Scenario: Retrying after a wrong password
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "HarryPotter"
    Then I should be prompted for my password
    When I enter the password "muggle"
    Then I should be told that the password is wrong
    And I should be prompted for my password
    When I enter the password "hogwarts"
    Then I should see that I am "HarryPotter"

  @socket
  Scenario: Being disconnected after three wrong passwords
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "HarryPotter"
    Then I should be prompted for my password
    When I enter the password "muggle"
    Then I should be told that the password is wrong
    And I should be prompted for my password
    When I enter the password "squib"
    Then I should be told that the password is wrong
    And I should be prompted for my password
    When I enter the password "dementor"
    Then I should be disconnected
