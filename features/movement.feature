@movement
Feature: Movement
  In order to move around the world
  As Player
  I want to move my character between locations

  Background:
    Given there is the location "A"
    And there is the location "B" north from location "A"
    And there is the location "C" east from location "A"
    And there is the location "D" south from location "A"
    And there is the location "E" west from location "A"
    And there is the location "F" up from location "A"
    And there is the location "G" down from location "A"
    And there is the location "Far away" north from location "B"
    And my character is in the location "A"
    And I am connected to the game

  @domain @telnet @websocket
  Scenario: Moving north
    When I move to north
    Then I should see that I am in the location "B"
    And I should see that there is a location "A" south from here

  @domain @telnet @websocket
  Scenario: Moving east
    When I move to east
    Then I should see that I am in the location "C"
    And I should see that there is a location "A" west from here

  @domain @telnet @websocket
  Scenario: Moving south
    When I move to south
    Then I should see that I am in the location "D"
    And I should see that there is a location "A" north from here

  @domain @telnet @websocket
  Scenario: Moving west
    When I move to west
    Then I should see that I am in the location "E"
    And I should see that there is a location "A" east from here

  @domain @telnet @websocket
  Scenario: Moving up
    When I move to up
    Then I should see that I am in the location "F"
    And I should see that there is a location "A" down from here

  @domain @telnet @websocket
  Scenario: Moving down
    When I move to down
    Then I should see that I am in the location "G"
    And I should see that there is a location "A" up from here

  @domain @telnet @websocket
  Scenario: Moving to far away from initial location
    When I move to north
    Then I should see that I am in the location "B"
    When I move to north
    Then I should see that I am in the location "Far away"

  @domain @telnet @websocket
  Scenario: Moving where there is no exit
    When I move to north
    Then I should see that I am in the location "B"
    When I move to east
    Then I should be told I cannot move there
    And I should see that there is a location "A" south from here

  @domain @telnet @websocket
  Scenario: Seeing another character leave
    Given there is the character "Hermione" in the location "A"
    And "Hermione" is connected to the game
    When I move to north
    Then I should see that I am in the location "B"
    And "Hermione" should see me leave to north

  @domain @telnet @websocket
  Scenario: Seeing another character arrive
    Given there is the character "Hermione" in the location "B"
    And "Hermione" is connected to the game
    When I move to north
    Then I should see that I am in the location "B"
    And "Hermione" should see me arrive from south
