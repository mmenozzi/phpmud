@creation
Feature: Character creation
  In order to play the game
  As a new Player
  I want to create my character

  Background:
    Given there is the starting location "Inn"
    And there is the character "HarryPotter" with password "hogwarts" in the location "Inn"

  @domain @telnet @websocket
  Scenario: Creating a new character
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "Voldemort"
    Then I should be offered to create the character "Voldemort"
    When I confirm the new character name
    Then I should be asked for the sex of my character
    When I choose that my character is male
    Then I should be prompted for a new password
    When I choose the password "tomriddle"
    Then I should see that I am "Voldemort"
    And I should see that I am in the location "Inn"

  @domain @telnet @websocket
  Scenario: Logging in with a newly created character
    Given I have created the character "Voldemort" with password "tomriddle"
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "Voldemort"
    Then I should be prompted for my password
    When I enter the password "tomriddle"
    Then I should see that I am "Voldemort"

  @domain @telnet @websocket
  Scenario: Capitalizing the name of a new character
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "voldemort"
    Then I should be offered to create the character "Voldemort"

  @domain @telnet @websocket
  Scenario: Choosing a password that is too short
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "Voldemort"
    Then I should be offered to create the character "Voldemort"
    When I confirm the new character name
    Then I should be asked for the sex of my character
    When I choose that my character is female
    Then I should be prompted for a new password
    When I choose the password "tom"
    Then I should be told that the password is too short
    And I should be prompted for a new password
    When I choose the password "tomriddle"
    Then I should see that I am "Voldemort"

  @telnet
  Scenario: Declining the name of a new character
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "Voldemort"
    Then I should be offered to create the character "Voldemort"
    When I decline the new character name
    Then I should be prompted for my character name

  @telnet
  Scenario: Repeating a different password
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "Voldemort"
    Then I should be offered to create the character "Voldemort"
    When I confirm the new character name
    Then I should be asked for the sex of my character
    When I choose that my character is male
    Then I should be prompted for a new password
    When I choose the password "tomriddle" but repeat "riddle"
    Then I should be told that the passwords do not match
    And I should be prompted for a new password

  @domain @websocket
  Scenario: Creating a character whose name is taken
    When I connect to the game
    And I try to create the character "harrypotter" with password "muggle"
    Then I should be told that the character name is taken

  @domain @telnet @websocket
  Scenario: Seeing a new character appear
    Given there is the character "Hermione" in the location "Inn"
    And "Hermione" is connected to the game
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "Voldemort"
    Then I should be offered to create the character "Voldemort"
    When I confirm the new character name
    Then I should be asked for the sex of my character
    When I choose that my character is male
    Then I should be prompted for a new password
    When I choose the password "tomriddle"
    Then "Hermione" should see "Voldemort" appear as male
