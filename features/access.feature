@access
Feature: Access
  In order to play the game
  As Player
  I want to access the world with my character

  Background:
    Given there is the location "A"
    And there is the character "HarryPotter" with password "hogwarts" in the location "A"

  @socket
  Scenario: Accessing the game with correct password
    When I connect to the game
    Then I should be prompted for my character name
    When I enter the character name "HarryPotter"
    Then I should be prompted for my password
    When I enter the password "hogwarts"
    Then I should see that I am "HarryPotter"
    And I should see that I am in the location "A"
