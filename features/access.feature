@access
Feature: Access
  In order to play the game
  As Player
  I want to access the world with my character

  Background:
    Given there is the location "A"
    And there is the character "HarryPotter" with password "hogwarts" in the location "A"

  @telnet
  Scenario: Seeing the MUD logo when connecting
    When I connect to the game
    Then I should see the MUD logo
    And I should be prompted for my character name

  @domain @telnet @websocket
  Scenario: Accessing the game with correct password
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "HarryPotter"
    Then I should be prompted for my password
    When I enter the password "hogwarts"
    Then I should see that I am "HarryPotter"
    And I should see that I am in the location "A"

  @domain @telnet @websocket
  Scenario: Entering the name of a character that does not exist
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "Voldemort"
    Then I should be told that the character "Voldemort" does not exist
    And I should be prompted for my character name

  @domain @telnet @websocket
  Scenario: Entering a character name that is not allowed
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "V0ldemort"
    Then I should be told that the character name is not allowed
    And I should be prompted for my character name

  @domain @telnet @websocket
  Scenario: Entering the character name in a different case
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "harrypotter"
    Then I should be prompted for my password
    When I enter the password "hogwarts"
    Then I should see that I am "HarryPotter"

  @domain @telnet @websocket
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

  @domain @telnet @websocket
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

  @websocket
  Scenario: Identifying again does not reset the password attempts
    When I connect to the game
    And I enter the character name "HarryPotter"
    And I enter the password "muggle"
    And I enter the password "squib"
    And I enter the character name "HarryPotter"
    And I enter the password "dementor"
    Then I should be disconnected

  @websocket
  Scenario: Being refused when connecting from a foreign web page
    When I connect to the game from a web page on "http://evil.example"
    Then my connection should be refused
