@time
Feature: Passing of time
  In order to feel the world alive
  As Player
  I want to be told when a new phase of the day begins

  Background:
    Given there is the location "A"
    And my character is in the location "A"

  @domain @socket
  Scenario Outline: Being told that a day phase begins
    Given I am connected to the game
    And it is <hour> o'clock
    When an hour passes
    Then I should be told that <phase> begins

    Examples:
      | hour | phase  |
      | 5    | dawn   |
      | 11   | noon   |
      | 17   | sunset |
      | 20   | night  |

  @domain
  Scenario: Not being told anything when no day phase begins
    Given I am connected to the game
    And it is 7 o'clock
    When an hour passes
    Then I should not be told that a day phase begins
