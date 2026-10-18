@time
Feature: Passing of time
  In order to feel the world alive
  As Player
  I want to be told when a new phase of the day begins

  Background:
    Given there is the location "A"
    And my character is in the location "A"

  @domain @telnet @websocket
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

  @domain @telnet @websocket
  Scenario Outline: Being told the current day phase when connecting
    Given it is <hour> o'clock
    And I am connected to the game
    Then I should be told that it is <phase>

    Examples:
      | hour | phase  |
      | 1    | night  |
      | 7    | dawn   |
      | 13   | noon   |
      | 18   | sunset |
      | 22   | night  |

  @domain
  Scenario: Not being told anything when no day phase begins
    Given I am connected to the game
    And it is 7 o'clock
    When an hour passes
    Then I should not be told that a day phase begins
